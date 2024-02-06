<?php

namespace App\Console\Commands;

use Illuminate\Queue\Console\ListFailedCommand as BaseListFailedCommand;

class ListFailedCommand extends BaseListFailedCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'queue:failed';

    /**
     * This contains the necessary code to add parameters.
     */
    use AddParametersTrait;
}
