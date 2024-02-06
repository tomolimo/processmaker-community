<?php

use Illuminate\Contracts\Console\Kernel as Kernel2;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Http\Kernel as Kernel4;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Foundation\Http\Kernel as Kernel3;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use ProcessMaker\Core\System;

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Application(
    realpath(__DIR__ . '/../')
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Kernel4::class,
    Kernel3::class
);

$app->singleton(
    Kernel2::class,
    App\Console\Kernel::class
);

$app->singleton(
    ExceptionHandler::class,
    Handler::class
);

$app->useStoragePath(System::getPathsInstalled()->pathData);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
