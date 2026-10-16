<?php

use App\Jobs\FetchTaxonImageJob;
use App\Jobs\GenerateSpeciesBiographyJob;
use App\Jobs\QueueHealthProbe;
use App\Jobs\SyncAnimalsJob;
use App\Mail\AnimalInquiryMail;
use App\Models\Animal;
use App\Models\Inquiry;
use App\Models\Species;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redis;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;
use Laravel\Horizon\Contracts\SupervisorRepository;
use Laravel\Horizon\MasterSupervisor;
use Tests\TestCase;

uses(TestCase::class);

it('keeps all configured supervisor timeouts below their reservation intervals', function () {
    foreach (config('horizon.defaults') as $supervisor) {
        expect($supervisor['timeout'])->toBeLessThan(config('queue.connections.'.$supervisor['connection'].'.retry_after'));
    }
    expect(config('queue.connections.redis.retry_after'))->toBeGreaterThan((new SyncAnimalsJob)->timeout);
});

it('isolates new imports and mail from legacy default jobs', function () {
    $import = new SyncAnimalsJob;
    $mail = new AnimalInquiryMail(new Inquiry, new Animal);
    expect($import->connection)->toBe('redis-imports')
        ->and($import->queue)->toBe('imports')
        ->and($mail->connection)->toBe('redis-short')
        ->and($mail->queue)->toBe('mail');
});

it('routes both taxonomy workloads through the short reservation connection', function () {
    foreach ([new FetchTaxonImageJob(Species::class, 1), new GenerateSpeciesBiographyJob(Species::class, 1)] as $job) {
        expect($job->connection)->toBe('redis-short');
        $supervisor = config('horizon.defaults.'.($job instanceof FetchTaxonImageJob ? 'images' : 'bios'));
        expect($job->timeout)->toBeLessThan($supervisor['timeout']);
    }
});

it('marks an unsuccessful import command as a failed job', function () {
    Artisan::shouldReceive('call')->once()->with('animals:sync')->andReturn(1);
    (new SyncAnimalsJob)->handle();
})->throws(RuntimeException::class);

it('denies the Horizon dashboard to non-admin users', function () {
    $user = new User(['name' => 'Member', 'email' => 'member@example.test']);
    $user->is_admin = false;
    $this->actingAs($user)->get('/horizon')->assertForbidden();
});

it('requires authentication for the Horizon dashboard', function () {
    $this->get('/horizon')->assertRedirect('/login');
});

it('permits administrators to view Horizon', function () {
    $user = new User(['name' => 'Admin', 'email' => 'admin@example.test']);
    $user->is_admin = true;
    $this->actingAs($user)->get('/horizon')->assertOk();
});

it('fails verification with unsafe reservations before dispatching probes', function () {
    config(['queue.default' => 'redis', 'queue.connections.redis.retry_after' => 1, 'app.deployment_revision' => str_repeat('a', 40)]);
    Bus::fake();
    $this->artisan('queue:verify-horizon', ['--wait' => 0])->assertFailed();
    Bus::assertNothingDispatched();
});

it('rejects workers returning a stale release revision', function () {
    config(['queue.default' => 'redis', 'app.deployment_revision' => str_repeat('a', 40)]);
    $name = MasterSupervisor::basename().'-test';
    $masters = Mockery::mock(MasterSupervisorRepository::class);
    $masters->shouldReceive('all')->andReturn([(object) ['name' => $name, 'status' => 'running']]);
    $this->app->instance(MasterSupervisorRepository::class, $masters);
    $processes = [];
    foreach (config('horizon.defaults') as $options) {
        $processes[$options['connection'].':'.implode(',', $options['queue'])] = 1;
    }
    $supervisors = Mockery::mock(SupervisorRepository::class);
    $supervisors->shouldReceive('all')->andReturn([(object) ['master' => $name, 'status' => 'running', 'processes' => $processes]]);
    $this->app->instance(SupervisorRepository::class, $supervisors);
    Bus::fake();
    $redis = Mockery::mock();
    $redis->shouldReceive('get')->andReturn(str_repeat('b', 40));
    Redis::shouldReceive('connection')->with('default')->andReturn($redis);
    $this->artisan('queue:verify-horizon', ['--wait' => 0])->assertFailed();
    Bus::assertDispatched(QueueHealthProbe::class, 6);
});
