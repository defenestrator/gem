<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Heartbeat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:heartbeat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (config('app.env') == "production" && config('app.url') == 'https://gemreptiles.com') {
            $blah = 'The gemreptiles.com application is not dead yet!';
            $this->comment($blah);
            Log::info($blah);
        } else {
            $quack = 'This command only executes on production. Where is your god now?';
            $this->comment($quack);
            Log::info($quack);
        }
        return 0;
    }
}
