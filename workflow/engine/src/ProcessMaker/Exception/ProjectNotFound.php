<?php
namespace ProcessMaker\Exception;

use ProcessMaker\Project;

class ProjectNotFound extends \RuntimeException
{
    const EXCEPTION_CODE = 400;

    public function __construct(Project\Handler $obj, $uid, $message = "", \Exception $previous = null) {
        $message = empty($message) ? 'Project ' . $uid . ', does not exist.' : $message;

        parent::__construct($message, self::EXCEPTION_CODE, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}