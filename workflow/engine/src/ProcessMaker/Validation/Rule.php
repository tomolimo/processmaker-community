<?php

namespace ProcessMaker\Validation;

/**
 * It contains a validation rule defined by the Closure function that must 
 * return a boolean value, true if it has failed, and false if it has passed the 
 * validation rule.
 */
class Rule
{
    /**
     * Validation data defined by value and field.
     * @var object 
     */
    private $data = null;

    /**
     * Validation rule.
     * @var Closure 
     */
    private $callback = null;

    /**
     * Help to register when the rule is not met.
     * @var Closure 
     */
    private $callbackLog = null;

    /**
     * Return message in case the rule is not met.
     * @var string 
     */
    private $message = "";

    /**
     * Response status code.
     * @var int 
     */
    private $status = 0;

    /**
     * Obtain validation data composed of field and value.
     * @return object
     */
    function getData()
    {
        return $this->data;
    }

    /**
     * Get the Closure function.
     * @return Closure
     */
    function getCallback()
    {
        return $this->callback;
    }

    /**
     * Gets the Closure function that applies the validation rule.
     * @return Closure
     */
    function getCallbackLog()
    {
        return $this->callbackLog;
    }

    /**
     * Get the message to be saved in the log if the rule is not fulfilled.
     * @return string
     */
    function getMessage()
    {
        return $this->message;
    }

    /**
     * Get status code.
     * @return int
     */
    function getStatus()
    {
        return $this->status;
    }

    /**
     * Registers the data and the Closure function that contains the validation 
     * rule.
     * @param array $data
     * @param Closure $callback
     * @return Rule
     */
    public function validate($data, $callback = null)
    {
        $this->data = (object) $data;
        if (is_callable($callback)) {
            $this->callback = $callback;
        }
        return $this;
    }

    /**
     * Registers the customized message in case the validation rule is not met.
     * @param string $message
     * @return Rule
     */
    public function message($message = "")
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Set status code.
     * @param int $status
     * @return $this
     */
    function status($status = 0)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Registers the Closure function in case the validation rule is not met.
     * @param Closure $callback
     * @return Rule
     */
    public function log($callback = null)
    {
        if (is_callable($callback)) {
            $this->callbackLog = $callback;
        }
        return $this;
    }
}
