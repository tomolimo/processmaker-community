<?php

namespace App\Console\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Queue\Console\TableCommand as BaseTableCommand;
use Illuminate\Support\Composer;

class TableCommand extends BaseTableCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'queue:table';

    /**
     * This contains the necessary code to add parameters.
     */
    use AddParametersTrait;

    /**
     * Create a new queue table command.
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
