<?php

use Illuminate\Cache\CacheServiceProvider;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\View\ViewServiceProvider;

return [
    'name' => env('APP_NAME', 'ProcessMaker'),
    'url' => env('APP_URL', 'http://localhost'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'cache_lifetime' => env('APP_CACHE_LIFETIME', 60),
    'key' => env('APP_KEY', 'base64:rU28h/tElUn/eiLY0qC24jJq1rakvAFRoRl1DWxj/kM='),
    'cipher' => 'AES-256-CBC',
    'timezone' => 'UTC',
    'providers' => [
        FilesystemServiceProvider::class,
        CacheServiceProvider::class,
        ViewServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Laravel\Tinker\TinkerServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
    ],

    'aliases' => [
        'Crypt' => Illuminate\Support\Facades\Crypt::class
    ],

];
