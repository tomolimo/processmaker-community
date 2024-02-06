<?php

namespace ProcessMaker\Upgrade;

use ProcessMaker\Core\RunProcess;

/**
 * Extended class to manage the processes that executes a queries in the upgrade process
 */
class RunProcessUpgradeQuery extends RunProcess
{
    // Class constants
    const SUCCESS = 'success';
    const CMD = PHP_BINARY . ' processmaker upgrade-query %s %s %s';
    const RBAC = '1';
    const NO_RBAC = '0';

    // Class properties
    private $workspace;
    private $sql;
    private $isRbac;

    /**
     * Class constructor
     *
     * @param string $workspace
     * @param string $sql
     * @param bool $isRbac
     */
    public function __construct($workspace, $sql, $isRbac = false)
    {
        // Set properties values
        $this->workspace = $workspace;
        $this->sql = $sql;
        $this->isRbac = $isRbac;

        // Build the command and send to the parent class
        parent::__construct($this->buildCommand());
    }

    /**
     * Override the parent method in order to compare the raw response with the SUCCESS value
     *
     * @return string
     */
    public function parseAnswer()
    {
        return $this->getRawAnswer() === self::SUCCESS ? parent::TERMINATED : parent::ERROR;
    }

    /**
     * Build the command to execute a query for the upgrade process
     *
     * @return string
     */
    private function buildCommand()
    {
        return sprintf(self::CMD, $this->workspace, base64_encode($this->sql),
            ($this->isRbac ? self::RBAC : self::NO_RBAC));
    }
}