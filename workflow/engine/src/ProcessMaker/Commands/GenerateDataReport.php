<?php

namespace ProcessMaker\Commands;

use ProcessMaker\Core\ProcOpen;

class GenerateDataReport extends ProcOpen
{
    private $workspace;
    private $tableName;
    private $type;
    private $processUid;
    private $gridKey;
    private $addTabUid;
    private $className;
    private $pathWorkspace;
    private $start;
    private $limit;

    /**
     * Initializes the command parameters.
     * @param string $workspace
     * @param string $tableName
     * @param string $type
     * @param string $processUid
     * @param string $gridKey
     * @param string $addTabUid
     * @param string $className
     * @param string $pathWorkspace
     * @param integer $start
     * @param integer $limit
     */
    public function __construct(
            $workspace, 
            $tableName, 
            $type = 'NORMAL', 
            $processUid = '', 
            $gridKey = '', 
            $addTabUid = '', 
            $className = '', 
            $pathWorkspace, 
            $start = 0, 
            $limit = 10)
    {
        $this->workspace = $workspace;
        $this->tableName = $tableName;
        $this->type = $type;
        $this->processUid = $processUid;
        $this->gridKey = $gridKey;
        $this->addTabUid = $addTabUid;
        $this->className = $className;
        $this->pathWorkspace = $pathWorkspace;
        $this->start = $start;
        $this->limit = $limit;
        $this->setCwd(PATH_TRUNK);
        parent::__construct($this->buildCommand());
    }

    /**
     * Returns the command to execute.
     * @return string
     */
    private function buildCommand(): string
    {
        $command = PHP_BINDIR . "/php "
                . "./processmaker "
                . "'generate-data-report' "
                . "'{$this->workspace}' "
                . "'tableName={$this->tableName}' "
                . "'type={$this->type}' "
                . "'process={$this->processUid}' "
                . "'gridKey={$this->gridKey}' "
                . "'additionalTable={$this->addTabUid}' "
                . "'className={$this->className}' "
                . "'pathWorkspace={$this->pathWorkspace}' "
                . "'start={$this->start}' "
                . "'limit={$this->limit}' ";
        return $command;
    }
}
