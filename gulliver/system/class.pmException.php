<?php

use Illuminate\Support\Facades\Log;

/**
 * HttpProxyController
 */
class PMException extends Exception
{

    public function __construct($message, $code = 0, $previous = null)
    {
        parent::__construct($message, 1);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public static function registerErrorLog($error, $token)
    {
        $message = $error->getMessage();
        $context = [
            'token' => $token
        ];
        Log::channel(':ExceptionCron')->error($message, Bootstrap::context($context));
    }
}
