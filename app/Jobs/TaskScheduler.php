<?php

namespace App\Jobs;

class TaskScheduler extends QueuedClosure
{
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

}
