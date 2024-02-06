<?php

namespace App\Console\Commands;

use Maveriks\WebApplication;

trait AddParametersTrait
{

    /**
     * Create a new queue command.
     *
     * @return void
     */
    public function __construct()
    {
        $this->signature .= '
            {--workspace=workflow : ProcessMaker Indicates the workspace to be processed.}
            {--processmakerPath=./ : ProcessMaker path.}
            ';

        $this->description .= ' (ProcessMaker has extended this command)';

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $workspace = $this->option('workspace');
        if (!empty($workspace)) {
            $webApplication = new WebApplication();
            $webApplication->setRootDir($this->option('processmakerPath'));
            $webApplication->loadEnvironment($workspace, false);
        }
        parent::handle();
    }
}
