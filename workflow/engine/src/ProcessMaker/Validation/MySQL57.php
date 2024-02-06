<?php

namespace ProcessMaker\Validation;

use ProcessMaker\Model\Dynaform;
use ProcessMaker\Model\Process;
use ProcessMaker\Model\ProcessVariables;
use ProcessMaker\Model\Triggers;

class MySQL57
{
    const REGEX = '/(?i)(select|\$).*?UNION.*?(select|\$).*?/ms';

    /**
     * Checks the queries inside triggers that could have possible incompatibilities with MySQL 5.7
     *
     * @see workflow/engine/bin/tasks/cliWorkspaces.php->check_queries_incompatibilities()
     * @param array $processes
     * @return array
     */
    public function checkIncompatibilityTriggers($processes)
    {
        $result = [];

        foreach ($processes as $process) {
            $triggerQuery = Triggers::query()->select();
            //Call the scope method to filter by process
            $triggerQuery->process($process['PRO_UID']);
            $triggers = $triggerQuery->get()->values()->toArray();
            foreach ($triggers as $trigger) {
                $resultIncompatibility = $this->analyzeQuery($trigger['TRI_WEBBOT']);
                if ($resultIncompatibility) {
                    $aux = array_merge($process, $trigger);
                    array_push($result, $aux);
                }
            }
        }

        return $result;
    }

    /**
     * Checks the queries inside dynaforms that could have possible incompatibilities with MySQL 5.7
     *
     * @see workflow/engine/bin/tasks/cliWorkspaces.php->check_queries_incompatibilities()
     * @param array $processes
     * @return array
     */
    public function checkIncompatibilityDynaforms($processes)
    {
        $result = [];

        foreach ($processes as $process) {
            $dynaformQuery = Dynaform::query()->select();
            //Call the scope method to filter by process
            $dynaformQuery->process($process['PRO_UID']);
            $dynaforms = $dynaformQuery->get()->values()->toArray();
            foreach ($dynaforms as $dynaform) {
                $resultIncompatibility = $this->analyzeQuery($dynaform['DYN_CONTENT']);
                if ($resultIncompatibility) {
                    $aux = array_merge($process, $dynaform);
                    array_push($result, $aux);
                }
            }
        }

        return $result;
    }

    /**
     * Checks the queries inside variables that could have possible incompatibilities with MySQL 5.7
     *
     * @see workflow/engine/bin/tasks/cliWorkspaces.php->check_queries_incompatibilities()
     * @param array $processes
     * @return array
     */
    public function checkIncompatibilityVariables($processes)
    {
        $result = [];

        foreach ($processes as $process) {
            $variablesQuery = ProcessVariables::query()->select();
            //Call the scope method to filter by process
            $variablesQuery->process($process['PRO_UID']);
            $variables = $variablesQuery->get()->values()->toArray();
            foreach ($variables as $variable) {
                $resultIncompatibility = $this->analyzeQuery($variable['VAR_SQL']);
                if ($resultIncompatibility) {
                    $aux = array_merge($process, $variable);
                    array_push($result, $aux);
                }
            }
        }

        return $result;
    }

    /**
     * Analyze the query using the regular expression
     *
     * @param string $query
     * @return bool
     */
    public function analyzeQuery($query)
    {
        preg_match_all($this::REGEX, $query, $matches, PREG_SET_ORDER, 0);

        return !empty($matches);
    }
}