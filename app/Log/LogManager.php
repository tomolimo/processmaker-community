<?php

namespace App\Log;

use Illuminate\Log\LogManager as BaseLogManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ProcessMaker\Core\System;

class LogManager extends BaseLogManager
{

    /**
     * Get the log connection configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function configurationFor($name)
    {
        //default
        if (!Str::contains($name, ':')) {
            return parent::configurationFor($name);
        }

        //extend channel
        $parse = explode(':', $name, 2);
        if (empty($parse[0])) {
            $parse[0] = config('logging.default');
        }
        $config = parent::configurationFor($parse[0]);
        if (!empty($parse[1])) {
            $config['name'] = $parse[1];
        }

        //extends
        if (!defined('PATH_DATA') || !defined('PATH_SEP')) {
            return $config;
        }
        $sys = System::getSystemConfiguration();

        //level
        if (!empty($sys['logging_level'])) {
            $levels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];
            $level = strtolower($sys['logging_level']);
            if (in_array($level, $levels)) {
                $config['level'] = $level;
            }
        }

        //path
        $basePath = PATH_DATA . 'sites' . PATH_SEP . config('system.workspace') . PATH_SEP . 'log' . PATH_SEP;
        $config['path'] = $basePath . File::basename($config['path']);
        if (!empty($sys['logs_location'])) {
            $config['path'] = $sys['logs_location'];
        }

        //days
        if (!empty($sys['logs_max_files'])) {
            $value = intval($sys['logs_max_files']);
            if ($value >= 0) {
                $config['days'] = $value;
            }
        }

        return $config;
    }
}
