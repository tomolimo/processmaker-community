<?php

namespace App\Providers;

use App\Console\Commands\WorkCommand;
use Illuminate\Queue\QueueServiceProvider;

class WorkCommandServiceProvider extends QueueServiceProvider
{

    /**
     * Overrides "register" method from Queue provider.
     * @return void
     */
    public function register()
    {
        parent::register();

        //Extend command "queue:work".
        $this->app->extend('command.queue.work', function ($command, $app) {
            return new WorkCommand($app['queue.worker']);
        });
    }
}
