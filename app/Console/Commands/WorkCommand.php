<?php

namespace App\Console\Commands;

use Illuminate\Queue\Console\WorkCommand as BaseWorkCommand;
use Illuminate\Queue\Worker;

class WorkCommand extends BaseWorkCommand
{

    use AddParametersTrait;

    /**
     * Create a new queue work command.
     *
     * @param \Illuminate\Queue\Worker $worker
     *
     * @return void
     */
    public function __construct(Worker $worker)
    {
        $this->signature .= '
            {--workspace=workflow : ProcessMaker Indicates the workspace to be processed.}
            {--processmakerPath=./ : ProcessMaker path.}
            ';

        $this->description .= ' (ProcessMaker has extended this command)';

        parent::__construct($worker);
    }
}
