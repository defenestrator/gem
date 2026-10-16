<?php

namespace App\Console\Commands;

use App\Jobs\QueueHealthProbe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;
use Laravel\Horizon\Contracts\SupervisorRepository;
use Laravel\Horizon\MasterSupervisor;

class VerifyHorizon extends Command
{
    protected $signature = 'queue:verify-horizon {--restart : Terminate existing masters and require replacement processes} {--wait=3750 : Maximum seconds for graceful restart and probe processing}';

    protected $description = 'Verify Horizon ownership and actual processing on every configured queue';

    public function handle(MasterSupervisorRepository $masters, SupervisorRepository $supervisors): int
    {
        if (config('queue.default') !== 'redis') {
            $this->error('Production queue connection must be redis before Horizon verification.');

            return self::FAILURE;
        }

        $revision = (string) config('app.deployment_revision');
        if (! preg_match('/^[a-f0-9]{40}$/', $revision)) {
            $this->error('Cache a full GEM_DEPLOY_REVISION commit hash before verification.');

            return self::FAILURE;
        }

        $expected = [];
        $plan = config('horizon.environments.'.app()->environment(), config('horizon.environments.*', []));
        foreach ($plan as $name => $overrides) {
            $options = array_replace(config('horizon.defaults.'.$name, []), $overrides);
            $connection = $options['connection'];
            if ($options['timeout'] >= config('queue.connections.'.$connection.'.retry_after')) {
                $this->error("Unsafe timeout for {$name}.");

                return self::FAILURE;
            }
            foreach ($options['queue'] as $queue) {
                $expected[$connection.':'.$queue] = [$connection, $queue];
            }
        }
        if (! $expected) {
            $this->error('No Horizon supervisors configured for this environment.');

            return self::FAILURE;
        }

        $local = fn () => collect($masters->all())->filter(fn ($master) => str_starts_with($master->name, MasterSupervisor::basename().'-'));
        $old = $this->option('restart') ? $local() : collect();
        if ($this->option('restart')) {
            if ($old->isEmpty()) {
                $this->error('No local Horizon master to restart. Start the managed daemon first.');

                return self::FAILURE;
            }
            $this->call('horizon:terminate');
        }

        $deadline = microtime(true) + max(0, (int) $this->option('wait'));
        $probes = [];
        do {
            $active = $local()->filter(fn ($master) => $master->status === 'running' && ! $old->pluck('name')->contains($master->name));
            $oldAlive = $old->contains(fn ($master) => posix_kill((int) $master->pid, 0));
            $coverage = [];
            foreach ($supervisors->all() as $supervisor) {
                if ($supervisor->status !== 'running' || ! $active->pluck('name')->contains($supervisor->master)) {
                    continue;
                }
                foreach ($supervisor->processes as $queues => $count) {
                    [$connection, $queueList] = explode(':', $queues, 2);
                    foreach (explode(',', $queueList) as $queue) {
                        if ($count > 0) {
                            $coverage[$connection.':'.$queue] = true;
                        }
                    }
                }
            }
            if (! $oldAlive && ! array_diff_key($expected, $coverage)) {
                if (! $probes) {
                    foreach ($expected as [$connection, $queue]) {
                        $key = 'queue-health:'.Str::uuid();
                        Bus::dispatch((new QueueHealthProbe($key))->onConnection($connection)->onQueue($queue));
                        $probes[] = $key;
                    }
                }
                if (collect($probes)->every(fn ($key) => Redis::connection('default')->get($key) === $revision)) {
                    Redis::connection('default')->del(...$probes);
                    $this->info('Horizon verified: running master, configured workers, and every queue processed the expected release revision.');

                    return self::SUCCESS;
                }
            }
            if (microtime(true) >= $deadline) {
                break;
            }
            usleep(500000);
        } while (true);

        $this->error('Horizon verification timed out. Inspect managed daemon logs, worker coverage, and in-flight jobs; do not clear queues.');

        return self::FAILURE;
    }
}
