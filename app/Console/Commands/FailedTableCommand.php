<?php

namespace App\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Queue\Console\FailedTableCommand as BaseFailedTableCommand;
use Illuminate\Support\Composer;

class FailedTableCommand extends BaseFailedTableCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'queue:failed-table';

    /**
     * This contains the necessary code to add parameters.
     */
    use AddParametersTrait;

    /**
     * Create a new queue failed-table command.
     *
     * @return void
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        $this->signature .= '
            {--workspace=workflow : ProcessMaker Indicates the workspace to be processed.}
            {--processmakerPath=./ : ProcessMaker path.}
            ';

        $this->description .= ' (ProcessMaker has extended this command)';

        parent::__construct($files, $composer);
    }
}
