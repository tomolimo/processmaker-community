<?php

namespace App\Console\Commands;

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
}
