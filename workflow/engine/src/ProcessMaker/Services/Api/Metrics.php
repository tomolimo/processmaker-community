<?php

namespace ProcessMaker\Services\Api;

use Exception;
use Luracast\Restler\RestException;
use ProcessMaker\BusinessModel\Cases\Draft;
use ProcessMaker\BusinessModel\Cases\Inbox;
use ProcessMaker\BusinessModel\Cases\Paused;
use ProcessMaker\BusinessModel\Cases\Unassigned;
use ProcessMaker\Services\Api;
use RBAC;

class Metrics extends Api
{
    /**
     * Constructor of the class
     * Defines the $RBAC definition
     */
    public function __construct()
    {
        global $RBAC;
        if (!isset($RBAC)) {
            $RBAC = RBAC::getSingleton(PATH_DATA, session_id());
            $RBAC->sSystem = 'PROCESSMAKER';
            $RBAC->initRBAC();
            $RBAC->loadUserRolePermission($RBAC->sSystem, $this->getUserId());
        }
    }

    /**
     * Get total cases per process
     * 
     * @url GET /total-cases-by-process
     * 
     * @param string $caseList
     * @param int $category
     * @param bool $topTen
     * @param array $processes
     * 
     * @return array
     * 
     * @throws RestException
     * 
     * @class AccessControl {@permission TASK_METRICS_VIEW}
     */
    public function getProcessTotalCases($caseList, $category = null, $topTen = false, $processes = [])
    {
        try {
            switch ($caseList) {
                case 'inbox':
                    $list = new Inbox();
                    break;
                case 'draft':
                    $list = new Draft();
                    break;
                case 'paused':
                    $list = new Paused();
                    break;
                case 'unassigned':
                    $list = new Unassigned();
                    break;
            }
            $result = $list->getCountersByProcesses($category, $topTen, $processes);
            return $result;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get total cases by range
     * 
     * @url GET /process-total-cases
     * 
     * @param string $caseList
     * @param int $processId
     * @param string $dateFrom
     * @param string $dateTo
     * @param string $groupBy
     * 
     * @return array
     * 
     * @throws RestException
     * 
     * @class AccessControl {@permission TASK_METRICS_VIEW}
     */
    public function getTotalCasesByRange($caseList, $processId = null, $dateFrom = null, $dateTo = null, $groupBy = 'day')
    {
        try {
            switch ($caseList) {
                case 'inbox':
                    $list = new Inbox();
                    break;
                case 'draft':
                    $list = new Draft();
                    break;
                case 'paused':
                    $list = new Paused();
                    break;
                case 'unassigned':
                    $list = new Unassigned();
                    break;
            }
            $result = $list->getCountersByRange($processId, $dateFrom, $dateTo, $groupBy);
            return $result;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get total of cases per list
     * 
     * @url GET /list-total-cases
     * 
     * @return array
     * 
     * @throws RestException
     * 
     * @class AccessControl {@permission TASK_METRICS_VIEW}
     */
    public function getCountersList()
    {
        try {
            $listInbox = new Inbox();
            $listDraft = new Draft();
            $listPaused = new Paused();
            $listUnassigned = new Unassigned();

            $casesInbox = $listInbox->getCounterMetrics();
            $casesDraft = $listDraft->getCounterMetrics();
            $casesPaused = $listPaused->getCounterMetrics();
            $casesUnassigned = $listUnassigned->getCounterMetrics();

            $result = [
                ['List Name' => 'Inbox', 'Total' => $casesInbox, 'Color' => 'green'],
                ['List Name' => 'Draft', 'Total' => $casesDraft, 'Color' => 'yellow'],
                ['List Name' => 'Paused', 'Total' => $casesPaused, 'Color' => 'blue'],
                ['List Name' => 'Unassigned', 'Total' => $casesUnassigned, 'Color' => 'gray']
            ];

            return $result;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get total cases risk
     * 
     * @url GET /cases-risk
     * 
     * @param string $caseList
     * @param int $process
     * @param string $dateFrom
     * @param string $dateTo
     * @param string $riskStatus
     * @param int $topCases
     * 
     * @return array
     * 
     * @throws RestException
     * 
     * @class AccessControl {@permission TASK_METRICS_VIEW}
     */
    public function getCasesRiskByProcess($caseList = 'inbox', $process = '', $dateFrom = null, $dateTo = null, $riskStatus = 'ON_TIME', $topCases = null)
    {
        try {
            switch ($caseList) {
                case 'inbox':
                    $list = new Inbox();
                    break;
                case 'draft':
                    $list = new Draft();
                    break;
                case 'paused':
                    $list = new Paused();
                    break;
                case 'unassigned':
                    $list = new Unassigned();
                    break;
            }
            $result = $list->getCasesRisk($process, $dateFrom, $dateTo, $riskStatus, $topCases);
            return $result;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }
}
