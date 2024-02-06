<?php

namespace App\Console\Commands;

use Illuminate\Queue\Console\FlushFailedCommand as BaseFlushFailedCommand;

class FlushFailedCommand extends BaseFlushFailedCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'queue:flush';

    /**
     * This contains the necessary code to add parameters.
     */
    use AddParametersTrait;
}
