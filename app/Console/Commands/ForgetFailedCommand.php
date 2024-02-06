<?php

namespace App\Console\Commands;

use Illuminate\Queue\Console\ForgetFailedCommand as BaseForgetFailedCommand;

class ForgetFailedCommand extends BaseForgetFailedCommand
{

    /**
     * This contains the necessary code to add parameters.
     */
    use AddParametersTrait;
}
