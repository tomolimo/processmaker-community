<?php

namespace ProcessMaker\PHPReflectionClass;

/**
 * Structure for the metadata of the class method.
 */
class MethodStructure
{
    /**
     * Params property.
     * @var array
     */
    public $params;

    /**
     * Information property.
     * @var array
     */
    public $info;

    /**
     * Constructor, runs when new object instance is created, sets name of the method.
     * @param string $name
     * @return void
     */
    public function __construct(string $name)
    {
        $this->info = [];
        $this->params = [];
        $this->setInfo("name", $name);
    }

    /**
     * Get value of a property by name.
     * @param string $name
     * @return mixed
     */
    public function getInfo(string $name)
    {
        if (array_key_exists($name, $this->info)) {
            return $this->info[$name];
        } else {
            return false;
        }
    }

    /**
     * Sets a property with supplied name to a supplied value.
     * @param string $name
     * @param string|array $value
     * @return void
     */
    public function setInfo($name, $value): void
    {
        $this->info[$name] = $value;
    }

    /**
     * Sets a parameter with supplied name and value.
     * @param string $name
     * @param string $value
     * @return void
     */
    public function setParam(string $name, string $value): void
    {
        $this->params[$name] = $value;
    }
}
