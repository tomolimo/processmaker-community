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

    /**
     * Get the display name for the queued job.
     *
     * @return string
     */
    public function displayName(): string
    {
        return get_class($this) . ' ' . parent::displayName();
    }
}
