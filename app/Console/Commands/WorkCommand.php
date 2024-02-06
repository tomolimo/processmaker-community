<?php

namespace App\Console\Commands;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Queue\Console\WorkCommand as BaseWorkCommand;
use Illuminate\Queue\Events\JobProcessing;
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
    public function __construct(Worker $worker, Cache $cache)
    {
        $this->signature .= '
            {--workspace=workflow : ProcessMaker Indicates the workspace to be processed.}
            {--processmakerPath=./ : ProcessMaker path.}
            ';

        $this->description .= ' (ProcessMaker has extended this command)';

        parent::__construct($worker, $cache);
    }

    /**
     * Listen for the queue events in order to update the console output.
     *
     * @return void
     */
    public function listenForEvents(): void
    {
        $this->laravel['events']->listen(JobProcessing::class, function ($event) {
            $this->loadAdditionalClassesAtRuntime();
        });
        parent::listenForEvents();
    }

    /**
     * This is required by artisan for dynamically access to plugin classes.
     */
    private function loadAdditionalClassesAtRuntime(): void
    {
        //load the main plugin classes because inside is the definition 'set_include_path()'
        if (!defined('PATH_PLUGINS')) {
            return;
        }
        $files = File::files(PATH_PLUGINS);
        foreach ($files as $file) {
            $isPhpFile = strtolower(File::extension($file)) === "php";
            if (!$isPhpFile) {
                continue;
            }
            require_once $file;
        }
        //load the classes of the plugins when is required dynamically.
        $closure = function ($className) {
            if (class_exists($className)) {
                return;
            }
            if (!defined('PATH_PLUGINS')) {
                return;
            }
            $searchFiles = function ($path) use (&$searchFiles, $className) {
                $directories = File::directories($path);
                foreach ($directories as $directory) {
                    $omittedDirectories = [
                        'bin', 'cache', 'colosa', 'config',
                        'data', 'documentation', 'fields',
                        'files', 'js', 'log', 'node_modules',
                        'public_html', 'resources', 'routes',
                        'templates', 'tests', 'translations',
                        'vendor', 'view', 'views'
                    ];
                    if (in_array(File::basename($directory), $omittedDirectories)) {
                        continue;
                    }
                    $searchFiles($directory);
                    $files = File::files($directory);
                    foreach ($files as $file) {
                        $isPhpFile = strtolower(File::extension($file)) === "php";
                        if (!$isPhpFile) {
                            continue;
                        }
                        $className = explode("\\", $className);
                        $className = array_pop($className);
                        $pattern = "/class[\n\s]+" . $className . "[\s\n]*\{/";
                        $status = preg_match($pattern, file_get_contents($file), $result);
                        if ($status === 1) {
                            require_once $file;
                            break;
                        }
                    }
                }
            };
            $searchFiles(PATH_PLUGINS);
        };
        spl_autoload_register($closure);
    }
}
