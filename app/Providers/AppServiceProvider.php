<?php

namespace App\Providers;

use App\Console\Commands\WorkCommand;
use App\Log\LogManager;
use Illuminate\Queue\Worker;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(WorkCommand::class, function ($app) {
            $isDownForMaintenance = function () {
                return $this->app->isDownForMaintenance();
            };
            return new WorkCommand(App::make(Worker::class, ['isDownForMaintenance' => $isDownForMaintenance]), $app['cache.store']);
        });

        $this->app->singleton('log', function ($app) {
            return new LogManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //is important for propel sql query builder
        setlocale(LC_NUMERIC, 'C');
    }
}
