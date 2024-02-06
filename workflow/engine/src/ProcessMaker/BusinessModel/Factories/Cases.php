<?php

namespace ProcessMaker\BusinessModel\Factories;

use Exception;

class Cases
{
    const CLASSES_NAMESPACE = "ProcessMaker\\BusinessModel\\Cases\\";

    /**
     * Create an object an set the properties
     *
     * @param string $list
     * @param array $filters
     *
     * @return object
     *
     * @throws Exception
     */
    public static function create($list, array $filters)
    {
        // Prepare the list name
        $list = capitalize($list);

        // Build the class name
        $className = self::CLASSES_NAMESPACE . $list;

        // Validate if the class exists
        if (class_exists($className)) {
            $instance = new $className();
        } else {
            throw new Exception("Class '{$list}' not exists.");
        }

        // Set properties
        $instance->setProperties($filters);

        return $instance;
    }
}
