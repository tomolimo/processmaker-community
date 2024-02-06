<?php

namespace ProcessMaker\Validation;

use ProcessMaker\Validation\Rule;

/**
 * Performs the validation process based on a list of validation rules.
 */
class Validator
{
    /**
     * List of instances of the class 'Rule'.
     * @var array 
     */
    private $rules = [];

    /**
     * Error message in the current validation rule.
     * @var string 
     */
    private $message = "";

    /**
     * Response status code.
     * @var int 
     */
    private $status = 0;

    /**
     * Current status of the validation, true if the validation has not been overcome.
     * @var boolean 
     */
    private $fails = false;

    /**
     * Call after the validation process.
     * @var Closure 
     */
    private $callback = null;

    /**
     * Get the message of the current validation if there was a failure.
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
     * Get the Closure function.
     * @return Closure
     */
    function getCallback()
    {
        return $this->callback;
    }

    /**
     * Add a validation rule.
     * The rules are verified in the order in which they have been added.
     * 
     * @param Rule $rule
     * @return Rule
     */
    public function addRule($rule = null)
    {
        if (!$rule instanceof Rule) {
            $rule = new Rule();
        }
        $this->rules[] = $rule;
        return $rule;
    }

    /**
     * Process all added validation rules.
     * @return Validator
     */
    public function validate()
    {
        $this->message = "";
        $this->status = 0;
        $this->fails = false;
        foreach ($this->rules as $rule) {
            $callback = $rule->getCallback();
            if (is_callable($callback)) {
                if ($callback($rule->getData())) {
                    $this->message = $rule->getMessage();
                    $this->status = $rule->getStatus();
                    $this->fails = true;
                    $getCallbackLog = $rule->getCallbackLog();
                    if (is_callable($getCallbackLog)) {
                        $getCallbackLog($rule);
                    }
                    break;
                }
            }
        }
        $callbackAfter = $this->getCallback();
        if (is_callable($callbackAfter)) {
            $callbackAfter($this);
        }
        return $this;
    }

    /**
     * Get the current status of the validation, the value is true if there was a 
     * failure and false if all the validation rules have been passed.
     * @return boolean
     */
    public function fails()
    {
        return $this->fails;
    }

    /**
     * The Closure function is called when the validation process is finished.
     * @param Closure $callback
     * @return Validator
     */
    public function after($callback)
    {
        if (is_callable($callback)) {
            $this->callback = $callback;
        }
        return $this;
    }
}
