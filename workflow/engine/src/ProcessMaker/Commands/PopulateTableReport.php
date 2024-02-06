<?php

namespace ProcessMaker\Commands;

use ProcessMaker\Core\ProcOpen;

class PopulateTableReport extends ProcOpen
{
    private $workspace;
    private $sql;
    private $isRbac;

    /**
     * Initializes the command parameters.
     * @param string $workspace
     * @param string $sql
     * @param boolean $isRbac
     */
    public function __construct($workspace, $sql, $isRbac = false)
    {
        $this->workspace = $workspace;
        $this->sql = $sql;
        $this->isRbac = $isRbac;
        $this->setCwd(PATH_TRUNK);
        parent::__construct($this->buildCommand());
    }

    /**
     * Returns the command to execute.
     * @return string
     */
    public function buildCommand()
    {
        $command = PHP_BINDIR . "/php "
                . "./processmaker "
                . "'populate-table' "
                . "'{$this->workspace}' "
                . base64_encode($this->sql) . " "
                . ($this->isRbac ? "'1'" : "'0'");
        return $command;
    }
}
