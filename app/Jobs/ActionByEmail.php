<?php

namespace App\Jobs;

class ActionByEmail extends QueuedClosure
{
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

}
