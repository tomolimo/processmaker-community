<?php

namespace ProcessMaker\BusinessModel\Factories;

use Closure;
use Exception;

class Jobs
{
    const CLASS_NAMESPACE = "App\\Jobs\\";

    /**
     * Gets the full name of the class, if the class does not exist, an exception is thrown.
     * @param string $name
     * @return string
     * @throws Exception
     */
    public static function getClassName($name)
    {
        $className = self::CLASS_NAMESPACE . $name;

        if (!class_exists($className)) {
            throw new Exception("{$className} not exists.");
        }

        return $className;
    }

    /**
     * This gets an instance of some Job defined in App\Jobs and dispatch this job.
     * @param string $name
     * @param Closure $closure
     * @return object
     */
    public static function create($name, Closure $closure)
    {
        $jobName = self::getClassName($name);

        $instance = $jobName::dispatch($closure);

        return $instance;
    }
}
