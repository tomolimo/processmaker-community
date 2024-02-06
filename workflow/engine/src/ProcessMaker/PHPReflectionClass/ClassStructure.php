<?php

namespace ProcessMaker\PHPReflectionClass;

use ReflectionClass;

/**
 * Structure for the metadata of the class.
 */
class ClassStructure
{
    /**
     * Array of methods.
     * @var array
     */
    public $methods;

    /**
     * Array of properties.
     * @var array
     */
    public $properties;

    /**
     * Array of informations.
     * @var array
     */
    public $info;

    /**
     * Constructor of the class, require the path of source code.
     * @param string $filename
     * @return void
     */
    public function __construct(string $filename = "")
    {
        $this->methods = [];
        $this->properties = [];
        $this->info = [];
        if ($filename != "") {
            $this->parseFromFile($filename);
        }
    }

    /**
     * Remove a property by name.
     * @param string $name
     * @return boolean
     */
    public function deleteInfo(string $name): bool
    {
        if (array_key_exists($name, $this->info)) {
            unset($this->info[$name]);
            return true;
        }
        return false;
    }

    /**
     * Get a property value by name.
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
     * Sets a property by name.
     * @param string $name
     * @param string|array $value
     * @return void
     */
    public function setInfo($name, $value): void
    {
        $this->info[$name] = $value;
    }

    /**
     * Adds a method to the class definition.
     * @param type $method
     * @return bool
     */
    public function setMethod($method): bool
    {
        if (is_object($method) && (new ReflectionClass($method))->getShortName() == "MethodStructure") {
            $this->methods[$method->getInfo("name")] = $method;
            return true;
        }
        return false;
    }

    /**
     * Parses a source code, require a filename.
     * @param string $filename
     * @return bool
     */
    public function parseFromFile(string $filename): bool
    {
        if (file_exists($filename) && is_readable($filename)) {
            $arrContents = file($filename);
            $parsing = false;
            $parsingBlocks = [];
            $tempBlock = [];
            foreach ($arrContents as $line) {
                if (trim($line) == "/**") {
                    $parsing = true;
                    $blockstart = true;
                } elseif ($parsing && trim($line) == "*/") {
                    $parsing = false;
                    $parsingBlocks[] = $tempBlock;
                    $tempBlock = [];
                } else {
                    if ($parsing) {
                        if ($blockstart) {
                            $tempBlock[] = $line;
                            $blockstart = false;
                        } else {
                            $tempBlock[] = $line;
                        }
                    }
                }
            }
            foreach ($parsingBlocks as $blockLines) {
                $block = [];
                foreach ($blockLines as $line) {
                    $str = strstr($line, "@");
                    $str = substr($str, 1);
                    if ($str !== false) {
                        $separatorPos = (strpos($str, " ") && strpos($str, "\t")) ? min(strpos($str, " "), strpos($str, "\t")) : (strpos($str, " ") ? strpos($str, " ") : (strpos($str, "\t") ? strpos($str, "\t") : strlen($str)));
                        $name = trim(substr($str, 0, $separatorPos));
                        $value = trim(substr($str, $separatorPos));
                    } else {
                        $name = "description";
                        $value = trim($line);
                    }
                    if ($name == "param" || $name == "description")
                        $block[$name][] = $value;
                    else
                        $block[$name] = $value;
                }
                if (array_key_exists("method", $block)) {
                    $tempMethod = new MethodStructure($block["method"]);
                    unset($block["method"]);
                    if (isset($block["param"]) && is_array($block["param"])) {
                        foreach ($block["param"] as $param) {
                            $tempMethod->setParam($param, "");
                        }
                    }
                    unset($block["param"]);
                    foreach ($block as $name => $value) {
                        $tempMethod->setInfo($name, $value);
                    }
                    $this->setMethod($tempMethod);
                } elseif (array_key_exists("class", $block)) {
                    $this->setInfo("name", $block["class"]);
                    unset($block["class"]);
                    foreach ($block as $name => $value) {
                        $this->setInfo($name, $value);
                    }
                }
            }
            return true;
        }
        return false;
    }
}
