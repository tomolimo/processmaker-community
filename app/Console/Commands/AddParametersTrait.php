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
            if (is_file(PATH_DATA_SITE . PATH_SEP . '.server_info')) {
                global $SERVER_INFO;
                $SERVER_INFO = file_get_contents(PATH_DATA_SITE . PATH_SEP . '.server_info');
                $SERVER_INFO = unserialize($SERVER_INFO);

                if (!defined('SERVER_NAME')) {
                    define('SERVER_NAME', $SERVER_INFO['SERVER_NAME']);
                }
                if (!defined('SERVER_PORT')) {
                    define('SERVER_PORT', $SERVER_INFO['SERVER_PORT']);
                }

                if (!defined('REQUEST_SCHEME')) {
                    if ((isset($SERVER_INFO['HTTPS']) && $SERVER_INFO['HTTPS'] == 'on') ||(isset($SERVER_INFO['HTTP_X_FORWARDED_PROTO']) && $SERVER_INFO['HTTP_X_FORWARDED_PROTO'] == 'https')) {
                        define('REQUEST_SCHEME', 'https');
                    } else {
                        define('REQUEST_SCHEME', $SERVER_INFO['REQUEST_SCHEME']);
                    }
                }
            }
        }
        parent::handle();
    }
}
