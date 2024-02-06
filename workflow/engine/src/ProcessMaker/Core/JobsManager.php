<?php

namespace ProcessMaker\Core;

use Exception;
use Illuminate\Support\Facades\Log;
use ProcessMaker\BusinessModel\Factories\Jobs;
use ProcessMaker\Core\System;
use Propel;

class JobsManager
{
    /**
     * Single object instance to be used in the entire environment.
     * 
     * @var object 
     */
    private static $jobsManager = null;

    /**
     * Delayed Dispatching: To delay the execution of a queued job. 
     * The time is 
     * in minutes.
     * @var int
     */
    private $delay;

    /**
     * Specifying Max Job Attempts: specifying the maximum number of times a job 
     * may be attempted. 
     * Number of times by default is 10, It is defined in env.ini file.
     * @var int
     */
    private $tries;

    /**
     * Job Expiration: Specifies how many seconds the queue connection should wait 
     * before retrying a job that is being processed. 
     * Time is in seconds.
     * @var int
     */
    private $retryAfter;

    /**
     * This is a list of the values that are saved from the current session.
     * @var array 
     */
    private $sessionValues = [
        '__SYSTEM_UTC_TIME_ZONE__',
        'USER_LOGGED',
        'USR_USERNAME',
        'APPLICATION',
        'INDEX',
        'PROCESS',
        'TASK',
    ];

    /**
     * Get delay property.
     * @return int
     */
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * Get tries property.
     * @return int
     */
    public function getTries()
    {
        return $this->tries;
    }

    /**
     * Get retryAfter property.
     * @return int
     */
    public function getRetryAfter()
    {
        return $this->retryAfter;
    }

    /**
     * It obtains a single object to be used as a record of the whole environment.
     * 
     * @return object
     */
    public static function getSingleton()
    {
        if (self::$jobsManager === null) {
            self::$jobsManager = new JobsManager();
        }
        return self::$jobsManager;
    }

    /**
     * This initialize environment configuration values.
     * @return JobsManager
     */
    public function init()
    {
        $envs = System::getSystemConfiguration('', '', config("system.workspace"));
        $this->delay = $envs['delay'];
        $this->tries = $envs['tries'];
        $this->retryAfter = $envs['retry_after'];

        config(['queue.connections.database.retry_after' => $this->retryAfter]);
        return $this;
    }

    /**
     * This obtains a status of the current values that are running. The status 
     * of the values will be used by the Job at a future time when the job is 
     * launched.
     * @return array
     */
    private function getDataSnapshot()
    {
        $constants = get_defined_constants(true);
        $session = $this->getSessionValues();
        return [
            'errorReporting' => ini_get('error_reporting'),
            'configuration' => Propel::getConfiguration(),
            'constants' => $constants['user'],
            'session' => $session,
            'server' => $_SERVER,
        ];
    }

    /**
     * This sets the status of the values when the job is launched. Accepts the 
     * result of the execution of the getDataSnapshot() method.
     * @param array $environment
     */
    private function recoverDataSnapshot($environment)
    {
        $this->prepareEnvironment($environment);

        $_SESSION = $environment['session'];
        $_SERVER = $environment['server'];
        Propel::initConfiguration($environment['configuration']);
        foreach ($environment['constants'] as $key => $value) {
            if (!defined($key)) {
                define($key, $value);
            }
        }
    }

    /**
     * This allows you to configure the PHP environment policies. The parameter 
     * must contain the correct indices.
     * @param array $environment
     */
    private function prepareEnvironment($environment)
    {
        ini_set('error_reporting', $environment['errorReporting']);
    }

    /**
     * This gets the values defined in the $this->sessionValues property from 
     * the current $_SESSION.
     * @return array
     */
    private function getSessionValues()
    {
        $result = [];
        foreach ($this->sessionValues as $key) {
            if (array_key_exists($key, $_SESSION)) {
                $result[$key] = $_SESSION[$key];
            }
        }
        return $result;
    }

    /**
     * Dispatch a job to its appropriate handler.
     * @param string $name
     * @param Closure $callback
     * @return object
     */
    public function dispatch($name, $callback)
    {
        $environment = $this->getDataSnapshot();

        $instance = Jobs::create($name, function() use ($callback, $environment) {
                    try {
                        $this->recoverDataSnapshot($environment);
                        $callback($environment);
                    } catch (Exception $e) {
                        Log::error($e->getMessage() . ": " . $e->getTraceAsString());
                    }
                });
        $instance->delay($this->delay);

        return $instance;
    }

    /**
     * This gets the value of the option specified in the second parameter from an 
     * array that represents the arguments.
     * If the option is not found, it returns false.
     * @param array $arguments
     * @param string $option
     * @return string|boolean
     */
    public function getOptionValueFromArguments($arguments, $option, $allocationSeparator = "=")
    {
        $option = $option . $allocationSeparator;
        $result = preg_grep("/{$option}/", $arguments);
        if (empty($result)) {
            return false;
        }
        $string = array_pop($result);
        $value = str_replace($option, "", $string);
        return trim($value);
    }
}
