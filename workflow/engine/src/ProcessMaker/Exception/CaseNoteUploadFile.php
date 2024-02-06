<?php

namespace ProcessMaker\Exception;

use Exception;
use Throwable;

class CaseNoteUploadFile extends Exception
{

    /**
     * Constructor method.
     * @param string $message
     * @param int $code
     * @param Throwable $previous
     */
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
