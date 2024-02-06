<?php

namespace App\Providers;

use App\Helpers\Workspace;
use App\Log\LogManager;
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
        App::bind('workspace', function() {
            return new Workspace();
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
