<?php

namespace App\Foundation;

use Illuminate\Foundation\Application as BaseApplication;

class Application extends BaseApplication
{

    protected function registerBaseServiceProviders(): void
    {
        parent::registerBaseServiceProviders();
    }
}
