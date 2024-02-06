<?php

namespace App\Console\Commands;

use Illuminate\Queue\Console\RetryCommand as BaseRetryCommand;

class RetryCommand extends BaseRetryCommand
{

    /**
     * This contains the necessary code to add parameters.
     */
    use AddParametersTrait;
}
