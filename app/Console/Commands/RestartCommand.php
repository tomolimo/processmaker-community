<?php

namespace App\Console\Commands;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Queue\Console\RestartCommand as BaseRestartCommand;

class RestartCommand extends BaseRestartCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'queue:restart';

    /**
     * This contains the necessary code to add parameters.
     */
    use AddParametersTrait;

    /**
     * Create a new queue restart command.
     * 
     * @param Cache $cache
     * @return void
     */
    public function __construct(Cache $cache)
    {
        parent::__construct($cache);
    }
}
