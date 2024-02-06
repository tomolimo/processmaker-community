<?php

namespace App\Jobs;

use Closure;
use Illuminate\Queue\CallQueuedClosure;
use Illuminate\Queue\SerializableClosure;

abstract class QueuedClosure extends CallQueuedClosure
{

    /**
     * Create a new job instance.
     *
     * @param \Illuminate\Queue\SerializableClosure $closure
     */
    public function __construct(Closure $closure)
    {
        parent::__construct(new SerializableClosure($closure));
    }
}
