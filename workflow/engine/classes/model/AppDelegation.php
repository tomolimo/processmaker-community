<?php

use Illuminate\Database\Eloquent\Builder;
use ProcessMaker\Model\Delegation;
use ProcessMaker\Plugins\PluginRegistry;
use ProcessMaker\Util\BatchProcessWithIndexes;

/**
 * Skeleton subclass for representing a row from the 'APP_DELEGATION' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements. This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package workflow.engine.classes.model
 */
class AppDelegation extends BaseAppDelegation
{
    /**
     * Get the risk value
     *
     * @return double
    */
    public function getRisk()
    {
        try {
            // This value needs to have a value like 0.x
            $systemConfiguration = Bootstrap::getSystemConfiguration();
            $risk = $systemConfiguration['at_risk_delegation_max_time'];

            return $risk;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get previous delegation (Valid Task)
     *
     * @param string $applicationUid        Unique id of Case
     * @param int    $delIndex              Delegation index
     * @param bool   $flagIncludeCurrentDel Include current delegation
     *
     * @return array Returns previous delegation, FALSE otherwise
     */
    public function getPreviousDelegationValidTask($applicationUid, $delIndex, $flagIncludeCurrentDel = false)
    {
        $arrayAppDelegationPrevious = false;
        $flagPrevious = true;

        do {
            $criteria = new Criteria('workflow');

            $criteria->addSelectColumn(AppDelegationPeer::TABLE_NAME . '.*');
            $criteria->addSelectColumn(TaskPeer::TAS_TYPE);

            $criteria->addJoin(AppDelegationPeer::TAS_UID, TaskPeer::TAS_UID, Criteria::INNER_JOIN);
            $criteria->add(AppDelegationPeer::APP_UID, $applicationUid, Criteria::EQUAL);
            $criteria->add(AppDelegationPeer::DEL_INDEX, $delIndex, Criteria::EQUAL);

            $rsCriteria = AppDelegationPeer::doSelectRS($criteria);
            $rsCriteria->setFetchmode(ResultSet::FETCHMODE_ASSOC);

            if ($rsCriteria->next()) {
                $record = $rsCriteria->getRow();

                if ($flagIncludeCurrentDel) {
                    if (preg_match('/^(?:' . 'SERVICE\-TASK|NORMAL|SCRIPT\-TASK|WEBENTRYEVENT|START\-MESSAGE\-EVENT|START\-TIMER\-EVENT' . ')$/', $record['TAS_TYPE'])) {
                        $arrayAppDelegationPrevious = $record;
                        $flagPrevious = false;
                    }
                }

                $delIndex = $record['DEL_PREVIOUS'];
            } else {
                $flagPrevious = false;
            }

            $flagIncludeCurrentDel = true;
        } while ($flagPrevious);

        //Return
        return $arrayAppDelegationPrevious;
    }

    /**
     * Create an application delegation
     *
     * @param string $proUid process Uid
     * @param string $appUid Application Uid
     * @param string $tasUid Task Uid
     * @param string $usrUid User Uid
     * @param int $priority delegation priority
     * @param bool $isSubprocess is a subprocess inside a process?
     *
     * @return int index of the application delegation.
     */
    public function createAppDelegation(
        $proUid,
        $appUid,
        $tasUid,
        $usrUid,
        $sAppThread,
        $priority = 3,
        $isSubprocess = false,
        $previous = -1,
        $nextTasParam = null,
        $flagControl = false,
        $flagControlMulInstance = false,
        $delPrevious = 0,
        $appNumber = 0,
        $taskId = 0,
        $userId = 0,
        $proId = 0
    ){
        if (! isset($proUid) || strlen($proUid) == 0) {
             throw new Exception('Column "PRO_UID" cannot be null.');
        }

        if (! isset($appUid) || strlen($appUid) == 0) {
            throw new Exception('Column "APP_UID" cannot be null.');
        }

        if (! isset($tasUid) || strlen($tasUid) == 0) {
            throw new Exception('Column "TAS_UID" cannot be null.');
        }

        if (! isset($usrUid)) {
            throw new Exception('Column "USR_UID" cannot be null.');
        }

        if (! isset($sAppThread) || strlen($sAppThread) == 0) {
            throw new Exception('Column "APP_THREAD" cannot be null.');
        }

        $this->delegation_id = null;
        // Get max DEL_INDEX
        $criteria = new Criteria("workflow");
        $criteria->add(AppDelegationPeer::APP_UID, $appUid);
        $criteria->add(AppDelegationPeer::DEL_LAST_INDEX, 1);
        $criteria->addDescendingOrderByColumn(AppDelegationPeer::DEL_INDEX);

        $criteriaIndex = clone $criteria;

        $rs = AppDelegationPeer::doSelectRS($criteriaIndex);
        $rs->setFetchmode(ResultSet::FETCHMODE_ASSOC);

        $delIndex = 1;
        $delPreviusUsrUid = $usrUid;
        $delPreviousFather = $previous;
        if ($rs->next()) {
            $row = $rs->getRow();

            $delIndex = (isset($row["DEL_INDEX"]))? $row["DEL_INDEX"] + 1 : 1;
            $delPreviusUsrUid = $row["USR_UID"];
            $delPreviousFather = $row["DEL_PREVIOUS"];
        } else {
            $criteriaDelIndex = new Criteria("workflow");

            $criteriaDelIndex->addSelectColumn(AppDelegationPeer::DEL_INDEX);
            $criteriaDelIndex->addSelectColumn(AppDelegationPeer::DEL_DELEGATE_DATE);
            $criteriaDelIndex->add(AppDelegationPeer::APP_UID, $appUid);
            $criteriaDelIndex->addDescendingOrderByColumn(AppDelegationPeer::DEL_DELEGATE_DATE);

            $rsCriteriaDelIndex = AppDelegationPeer::doSelectRS($criteriaDelIndex);
            $rsCriteriaDelIndex->setFetchmode(ResultSet::FETCHMODE_ASSOC);

            if ($rsCriteriaDelIndex->next()) {
                $row = $rsCriteriaDelIndex->getRow();

                $delIndex = (isset($row["DEL_INDEX"]))? $row["DEL_INDEX"] + 1 : 1;
            }
        }
        // Verify successors: parallel submit in the same time
        if ($flagControl) {
            $nextTaskUid = $tasUid;
            $index = $this->getAllTasksBeforeSecJoin($nextTaskUid, $appUid, $delPreviousFather);
            if ($this->createThread($index, $appUid)) {
                return 0;
            }
        }
        if ($flagControlMulInstance) {
            $nextTaskUid = $tasUid;
            $index = $this->getAllTheardMultipleInstance($delPreviousFather, $appUid);
            if ($this->createThread($index, $appUid, $usrUid)) {
                return 0;
            }
        }

        // Update set
        $criteriaUpdate = new Criteria('workflow');
        $criteriaUpdate->add(AppDelegationPeer::DEL_LAST_INDEX, 0);
        BasePeer::doUpdate($criteria, $criteriaUpdate, Propel::getConnection('workflow'));

        // Define the status of the thread, if is subprocess we need to CLOSED the thread
        $theadStatus = !$isSubprocess ? 'OPEN' : 'CLOSED';

        $this->setAppUid($appUid);
        $this->setProUid($proUid);
        $this->setTasUid($tasUid);
        $this->setDelIndex($delIndex);
        $this->setDelLastIndex(1);
        $this->setDelPrevious($previous == - 1 ? 0 : $previous);
        $this->setUsrUid($usrUid);
        $this->setDelType('NORMAL');
        $this->setDelPriority(($priority != '' ? $priority : '3'));
        $this->setDelThread($sAppThread);
        $this->setDelThreadStatus($theadStatus);
        $this->setDelThreadStatusId(Delegation::$thread_status[$theadStatus]);
        $this->setDelDelegateDate('now');
        $this->setAppNumber($appNumber);
        $this->setTasId($taskId);
        $this->setUsrId($userId);
        $this->setProId($proId);

        // The function return an array now.  By JHL
        $delTaskDueDate = $this->calculateDueDate($nextTasParam);
        $delRiskDate    = $this->calculateRiskDate($nextTasParam, $this->getRisk());
        $this->setDelTaskDueDate($delTaskDueDate);
        $this->setDelRiskDate($delRiskDate);

        if ((defined("DEBUG_CALENDAR_LOG")) && (DEBUG_CALENDAR_LOG)) {
            // Log of actions made by Calendar Engine
            $this->setDelData($delTaskDueDate);
        } else {
            $this->setDelData('');
        }

        // This condition assures that an internal delegation like a subprocess don't have an initial date set
        if ($delIndex == 1 && ! $isSubprocess) {
            // The first delegation, init date this should be now for draft applications, in other cases, should be null.
            $this->setDelInitDate('now');
        }

        if ($this->validate()) {
            try {
                $res = $this->save();
            } catch (PropelException $e) {
                return;
            }
        } else {
            // Something went wrong. We can now get the validationFailures and handle them.
            $msg = '';
            $validationFailuresArray = $this->getValidationFailures();
            foreach ($validationFailuresArray as $objValidationFailure) {
                $msg .= $objValidationFailure->getMessage() . "<br/>";
            }
            throw (new Exception('Failed Data validation. ' . $msg));
        }

        $delIndex = $this->getDelIndex();

        // Hook for the trigger PM_CREATE_NEW_DELEGATION
        if (defined('PM_CREATE_NEW_DELEGATION')) {
            $bpmn = new \ProcessMaker\Project\Bpmn();
            $flagActionsByEmail = true;

            $arrayAppDelegationPrevious = $this->getPreviousDelegationValidTask($appUid, $delIndex);

            $data = new stdclass();
            $data->TAS_UID = $tasUid;
            $data->APP_UID = $appUid;
            $data->DEL_INDEX = $delIndex;
            $data->USR_UID = $usrUid;
            $data->PREVIOUS_USR_UID = ($arrayAppDelegationPrevious !== false)? $arrayAppDelegationPrevious['USR_UID'] : $delPreviusUsrUid;

            if ($bpmn->exists($proUid)) {


            }

            if ($flagActionsByEmail) {
                $pluginRegistry = PluginRegistry::loadSingleton();
                $pluginRegistry->executeTriggers(PM_CREATE_NEW_DELEGATION, $data);
            }
        }

        return $delIndex;
    }

    /**
     * Load the Application Delegation row specified in [app_id] column value.
     *
     * @param string $appUid the uid of the application
     * @param integer $delIndex
     *
     * @return array $Fields the fields
     *
     * @throws Exception
     */

    public function Load ($appUid, $delIndex)
    {
        $con = Propel::getConnection(AppDelegationPeer::DATABASE_NAME);
        try {
            $oAppDel = AppDelegationPeer::retrieveByPk($appUid, $delIndex);
            if (is_object($oAppDel) && get_class($oAppDel) == 'AppDelegation') {
                $fields = $oAppDel->toArray(BasePeer::TYPE_FIELDNAME);
                $this->fromArray($fields, BasePeer::TYPE_FIELDNAME);

                return $fields;
            } else {
                throw (new Exception("The row '$appUid, $delIndex' in table AppDelegation doesn't exist!"));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    /**
     * Load the Application Delegation row specified in [app_id] column value.
     *
     * @param string $appUid the uid of the application
     * @param integer $index the index of the delegation
     *
     * @return array $Fields the fields
     */
    public function LoadParallel($appUid, $index = 0)
    {
        $cases = [];

        $c = new Criteria('workflow');
        $c->addSelectColumn(AppDelegationPeer::APP_UID);
        $c->addSelectColumn(AppDelegationPeer::DEL_INDEX);
        $c->addSelectColumn(AppDelegationPeer::PRO_UID);
        $c->addSelectColumn(AppDelegationPeer::TAS_UID);
        $c->addSelectColumn(AppDelegationPeer::USR_UID);
        $c->addSelectColumn(AppDelegationPeer::DEL_THREAD);
        $c->addSelectColumn(AppDelegationPeer::DEL_DELEGATE_DATE);
        $c->addSelectColumn(AppDelegationPeer::DEL_INIT_DATE);
        $c->addSelectColumn(AppDelegationPeer::DEL_TASK_DUE_DATE);
        $c->addSelectColumn(AppDelegationPeer::DEL_FINISH_DATE);
        $c->addSelectColumn(AppDelegationPeer::DEL_PREVIOUS);
        $c->add(AppDelegationPeer::DEL_THREAD_STATUS, 'OPEN');
        $c->add(AppDelegationPeer::APP_UID, $appUid);

        if ($index > 0) {
            $c->add(AppDelegationPeer::DEL_INDEX, $index);
        }

        $c->addDescendingOrderByColumn(AppDelegationPeer::DEL_INDEX);
        $rs = AppDelegationPeer::doSelectRS($c);
        $row = $rs->setFetchmode(ResultSet::FETCHMODE_ASSOC);

        $rs->next();
        $row = $rs->getRow();

        while (is_array($row)) {
            $cases[] = $row;
            $rs->next();
            $row = $rs->getRow();
        }

        return $cases;
    }

    /**
     * Update the application row
     *
     * @param array $aData
     * @return variant
     *
     */

    public function update($aData)
    {
        $con = Propel::getConnection(AppDelegationPeer::DATABASE_NAME);
        try {
            $con->begin();
            $oApp = AppDelegationPeer::retrieveByPK($aData['APP_UID'], $aData['DEL_INDEX']);
            if (is_object($oApp) && get_class($oApp) == 'AppDelegation') {
                $oApp->fromArray($aData, BasePeer::TYPE_FIELDNAME);
                if ($oApp->validate()) {
                    $res = $oApp->save();
                    $con->commit();
                    return $res;
                } else {
                    $msg = '';
                    foreach ($this->getValidationFailures() as $objValidationFailure) {
                        $msg .= $objValidationFailure->getMessage() . "<br/>";
                    }

                    throw (new PropelException('The row cannot be created!', new PropelException($msg)));
                }
            } else {
                $con->rollback();
                throw (new Exception("This AppDelegation row doesn't exist!"));
            }
        } catch (Exception $oError) {
            throw ($oError);
        }
    }

    public function remove($sApplicationUID, $iDelegationIndex)
    {
        $oConnection = Propel::getConnection(StepTriggerPeer::DATABASE_NAME);
        try {
            $oConnection->begin();
            $oApp = AppDelegationPeer::retrieveByPK($sApplicationUID, $iDelegationIndex);
            if (is_object($oApp) && get_class($oApp) == 'AppDelegation') {
                $result = $oApp->delete();
            }
            $oConnection->commit();
            return $result;
        } catch (Exception $e) {
            $oConnection->rollback();
            throw ($e);
        }
    }

    // TasTypeDay = 1  => working days
    // TasTypeDay = 2  => calendar days
    public function calculateDueDate($sNextTasParam)
    {
        //Get Task properties
        $task = TaskPeer::retrieveByPK($this->getTasUid());

        $aData = array();
        $aData['TAS_UID'] = $this->getTasUid();
        //Added to allow User defined Timing Control at Run time from Derivation screen
        if (isset($sNextTasParam['NEXT_TASK']['TAS_TRANSFER_HIDDEN_FLY']) && $sNextTasParam['NEXT_TASK']['TAS_TRANSFER_HIDDEN_FLY'] == 'true') {
            $aData['TAS_DURATION'] = $sNextTasParam['NEXT_TASK']['TAS_DURATION'];
            $aData['TAS_TIMEUNIT'] = $sNextTasParam['NEXT_TASK']['TAS_TIMEUNIT'];
            $aData['TAS_TYPE_DAY'] = $sNextTasParam['NEXT_TASK']['TAS_TYPE_DAY'];

            if (isset($sNextTasParam['NEXT_TASK']['TAS_CALENDAR']) && $sNextTasParam['NEXT_TASK']['TAS_CALENDAR'] != '') {
                $aCalendarUID = $sNextTasParam['NEXT_TASK']['TAS_CALENDAR'];
            } else {
                $aCalendarUID = '';
            }

            //Updating the task Table , so that user will see updated values in the assign screen in consequent cases
            $oTask = new Task();
            $oTask->update($aData);
        } else {
            if (is_null($task)) {
                return 0;
            }
            $aData['TAS_DURATION'] = $task->getTasDuration();
            $aData['TAS_TIMEUNIT'] = $task->getTasTimeUnit();
            $aData['TAS_TYPE_DAY'] = $task->getTasTypeDay();
            $aCalendarUID = '';
        }

        //Calendar - Use the dates class to calculate dates
        $calendar = new Calendar();

        $calendarData = $calendar->getCalendarData($aCalendarUID);

        if ($calendar->pmCalendarUid == "") {
            $calendar->getCalendar(null, $this->getProUid(), $this->getTasUid());

            $calendarData = $calendar->getCalendarData();
        }

        //Due date
        $initDate = $this->getDelDelegateDate();
        $timeZone = \ProcessMaker\Util\DateTime::convertUtcToTimeZone($initDate);
        $dueDate = $calendar->dashCalculateDate($timeZone, $aData["TAS_DURATION"], $aData["TAS_TIMEUNIT"], $calendarData);

        $dueDate = \ProcessMaker\Util\DateTime::convertDataToUtc($dueDate);
        return $dueDate;
    }

    /**
     * Calculate the risk date
     *
     * @param array $nextTask
     * @param double $risk
     *
     * @return string
    */
    public function calculateRiskDate($nextTask, $risk)
    {
        try {
            $data = [];
            if (isset($nextTask['NEXT_TASK']['TAS_TRANSFER_HIDDEN_FLY']) && $nextTask['NEXT_TASK']['TAS_TRANSFER_HIDDEN_FLY'] == 'true') {
                $data['TAS_DURATION'] = $nextTask['NEXT_TASK']['TAS_DURATION'];
                $data['TAS_TIMEUNIT'] = $nextTask['NEXT_TASK']['TAS_TIMEUNIT'];
            } else {
                $task = TaskPeer::retrieveByPK($this->getTasUid());
                $data['TAS_DURATION'] = $task->getTasDuration();
                $data['TAS_TIMEUNIT'] = $task->getTasTimeUnit();
            }

            $riskTime = $data['TAS_DURATION'] - ($data['TAS_DURATION'] * $risk);

            // Calendar - Use the dates class to calculate dates
            $calendar = new Calendar();
            $calendarData = [];
            if (empty($calendar->pmCalendarUid)) {
                $calendar->getCalendar(null, $this->getProUid(), $this->getTasUid());
                $calendarData = $calendar->getCalendarData();
            }

            // Risk date
            $riskDate = $calendar->dashCalculateDate(
                $this->getDelDelegateDate(),
                $riskTime,
                $data['TAS_TIMEUNIT'],
                $calendarData
            );

            return $riskDate;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Usually this function is called when routing in the flow, so by default cron = 0
     * @param int $cron
     * @return void
     */
    public function calculateDuration($cron = 0): void
    {
        $this->writeFileIfCalledFromCronForCalculateDuration($cron);
        $this->patchDataWithValuesForCalculateDuration();

        $builder = $this->getAppDelegationTask();
        $count = $builder->count();
        $now = new DateTime();

        $batch = new BatchProcessWithIndexes($count);
        $batch->process(function ($start, $limit) use ($builder, $now) {
            $results = $builder
                ->offset($start)
                ->limit($limit)
                ->get();
            foreach ($results as $object) {
                $appDelegationTask = $object->toArray();
                $this->updateAppDelegationWithCalendar($appDelegationTask, $now);
            }
        });
    }

    /**
     * Get APP_DELEGATION and TASK tables where 'started' and 'finished' are 0.
     * @return iterable
     */
    private function getAppDelegationTask(): Builder
    {
        $columns = [
            'APP_DELEGATION.APP_UID',
            'APP_DELEGATION.DEL_INDEX',
            'APP_DELEGATION.USR_UID',
            'APP_DELEGATION.PRO_UID',
            'APP_DELEGATION.TAS_UID',
            'APP_DELEGATION.DEL_DELEGATE_DATE',
            'APP_DELEGATION.DEL_INIT_DATE',
            'APP_DELEGATION.DEL_TASK_DUE_DATE',
            'APP_DELEGATION.DEL_FINISH_DATE',
            'APP_DELEGATION.DEL_DURATION',
            'APP_DELEGATION.DEL_QUEUE_DURATION',
            'APP_DELEGATION.DEL_DELAY_DURATION',
            'APP_DELEGATION.DEL_STARTED',
            'APP_DELEGATION.DEL_FINISHED',
            'APP_DELEGATION.DEL_DELAYED',
            'TASK.TAS_DURATION',
            'TASK.TAS_TIMEUNIT',
            'TASK.TAS_TYPE_DAY'
        ];
        $builder = Delegation::query()
            ->select($columns)
            ->leftjoin('TASK', function ($join) {
                $join->on('APP_DELEGATION.TAS_UID', '=', 'TASK.TAS_UID');
            })
            ->where(function ($query) {
                $query->where('APP_DELEGATION.DEL_STARTED', '=', 0)
                ->orWhere('APP_DELEGATION.DEL_FINISHED', '=', 0);
            })
            ->orderBy('DELEGATION_ID', 'asc');
        return $builder;
    }

    /**
     * Update the APP_DELEGATION table with the calculated calendar results.
     * @param array $appDelegationTask
     * @param DateTime $date
     * @return void
     */
    private function updateAppDelegationWithCalendar(array $appDelegationTask, DateTime $date): void
    {
        $calendar = new Calendar();
        $calendar->getCalendar($appDelegationTask['USR_UID'], $appDelegationTask['PRO_UID'], $appDelegationTask['TAS_UID']);
        $calData = $calendar->getCalendarData();
        $calculatedValues = $this->getValuesToStoreForCalculateDuration($appDelegationTask, $calendar, $calData, $date);

        Delegation::select()
            ->where('APP_UID', '=', $appDelegationTask['APP_UID'])
            ->where('DEL_INDEX', '=', $appDelegationTask['DEL_INDEX'])
            ->update([
                'DEL_STARTED' => $calculatedValues['isStarted'],
                'DEL_FINISHED' => $calculatedValues['isFinished'],
                'DEL_DELAYED' => $calculatedValues['isDelayed'],
                'DEL_QUEUE_DURATION' => $calculatedValues['queueTime'],
                'DEL_DELAY_DURATION' => $calculatedValues['delayTime'],
                'DEL_DURATION' => $calculatedValues['durationTime'],
                'APP_OVERDUE_PERCENTAGE' => $calculatedValues['percentDelay']
        ]);
    }

    public function getValuesToStoreForCalculateDuration($row, $calendar, $calData, $nowDate)
    {
        $rowValues = $this->completeRowDataForCalculateDuration($row, $nowDate);
        return array(
            'isStarted'    => $this->createDateFromString($row['DEL_INIT_DATE']) != null ? 1 : 0,
            'isFinished'   => $this->createDateFromString($row['DEL_FINISH_DATE']) != null ? 1: 0,
            'isDelayed'    => $this->calculateDelayTime($calendar, $calData, $rowValues) > 0 ? 1 : 0,
            'queueTime'    => $this->calculateQueueTime($calendar, $calData, $rowValues),
            'delayTime'    => $this->calculateDelayTime($calendar, $calData, $rowValues),
            'durationTime' => $this->calculateNetProcessingTime($calendar, $calData, $rowValues),
            'percentDelay' => $this->calculateOverduePercentage($calendar, $calData, $rowValues)
        );
    }

    private function calculateOverduePercentage($calendar, $calData, $rowValues)
    {
        if ($rowValues['fTaskDuration'] == 0) {
            return 0;
        }
        //TODO 8 daily/hours must be extracted from calendar
        $taskTime = ($rowValues['cTaskDurationUnit'] == 'DAYS')
                    ? $rowValues['fTaskDuration'] * 8 / 24
                    : $rowValues['fTaskDuration'] / 24;

        return $this->calculateDelayTime($calendar, $calData, $rowValues)  * 100/ $taskTime;
    }

    //time in days from init or delegate date to finish or today's date
    private function calculateNetProcessingTime($calendar, $calData, $rowValues)
    {
        $initDateForCalc = $this->selectDate($rowValues['dInitDate'], $rowValues['dDelegateDate'], 'max');
        $endDateForCalc = $this->selectDate($rowValues['dFinishDate'], $rowValues['dNow'], 'min');
        return $calendar->dashCalculateDurationWithCalendar(
                            $initDateForCalc->format('Y-m-d H:i:s'),
                            $endDateForCalc->format('Y-m-d H:i:s'),
                            $calData
        )/(24*60*60);
    }

    //time in days from delegate date to init date
    private function calculateQueueTime($calendar, $calData, $rowValues)
    {
        $initDateForCalc = $rowValues['dDelegateDate'];
        $endDateForCalc = $this->selectDate($rowValues['dInitDate'], $rowValues['dNow'], 'min');
        return $calendar->dashCalculateDurationWithCalendar(
                            $initDateForCalc->format('Y-m-d H:i:s'),
                            $endDateForCalc->format('Y-m-d H:i:s'),
                            $calData
        )/(24*60*60);
    }

    //time in days from due date to finish or today date
    private function calculateDelayTime($calendar, $calData, $rowValues)
    {
        $initDateForCalc = $this->selectDate($rowValues['dDueDate'], $rowValues['dDelegateDate'], 'max');
        $endDateForCalc = $this->selectDate($rowValues['dFinishDate'], $rowValues['dNow'], 'min');
        return $calendar->dashCalculateDurationWithCalendar(
                            $initDateForCalc->format('Y-m-d H:i:s'),
                            $endDateForCalc->format('Y-m-d H:i:s'),
                            $calData
        )/(24*60*60);
    }

    //to avoid aplying many times the same conversions and functions the row data
    //is used to create dates as DateTime objects and other fields are stracted also,
    //so the array returned will work as a "context" object for the rest of the functions.
    private function completeRowDataForCalculateDuration($row, $nowDate)
    {
        return array(
            'dDelegateDate'       => $this->createDateFromString($row['DEL_DELEGATE_DATE']),
            'dInitDate'           => $this->createDateFromString($row['DEL_INIT_DATE']),
            'dDueDate'            => $this->createDateFromString($row['DEL_TASK_DUE_DATE']),
            'dFinishDate'         => $this->createDateFromString($row['DEL_FINISH_DATE']),
            'fTaskDuration'       => $row['TAS_DURATION'] * 1.0,
            'cTaskDurationUnit'   => $row['TAS_TIMEUNIT'],
            'dNow'                => $nowDate,
            'row'                 => $row
        );
    }

    //by default min function returns de null value if one of the params is null
    //to avoid that behaviour this function was created so the function returns the first
    //not null date or if both are not null the mix/max date
    //NOTE date1 and date2 are DateTime objects.
    private function selectDate($date1, $date2, $compareFunction)
    {
        if ($date1 == null) {
            return $date2;
        }

        if ($date2 == null) {
            return $date1;
        }

        return $compareFunction($date1, $date2);
    }

    //Creates a DateTime object from a string. If the string is null or empty a null object is returned
    private function createDateFromString($stringDate)
    {
        if ($stringDate == null || $stringDate == '') {
            return null;
        }
        return new DateTime($stringDate);
    }

    private function writeFileIfCalledFromCronForCalculateDuration($cron)
    {
        if ($cron == 1) {
            $arrayCron = unserialize(trim(@file_get_contents(PATH_DATA . "cron")));
            $arrayCron["processcTimeStart"] = time();
            @file_put_contents(PATH_DATA . "cron", serialize($arrayCron));
        }
    }

    private function patchDataWithValuesForCalculateDuration()
    {
        //patch  rows with initdate = null and finish_date
        $c = new Criteria();
        $c->clearSelectColumns();
        $c->addSelectColumn(AppDelegationPeer::APP_UID);
        $c->addSelectColumn(AppDelegationPeer::DEL_INDEX);
        $c->addSelectColumn(AppDelegationPeer::DEL_DELEGATE_DATE);
        $c->addSelectColumn(AppDelegationPeer::DEL_FINISH_DATE);
        $c->add(AppDelegationPeer::DEL_INIT_DATE, null, Criteria::ISNULL);
        $c->add(AppDelegationPeer::DEL_FINISH_DATE, null, Criteria::ISNOTNULL);
        //$c->add(AppDelegationPeer::DEL_INDEX, 1);


        $rs = AppDelegationPeer::doSelectRS($c);
        $rs->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $rs->next();
        $row = $rs->getRow();

        while (is_array($row)) {
            $oAppDel = AppDelegationPeer::retrieveByPk($row['APP_UID'], $row['DEL_INDEX']);
            if (isset($row['DEL_FINISH_DATE'])) {
                $oAppDel->setDelInitDate($row['DEL_FINISH_DATE']);
            } else {
                $oAppDel->setDelInitDate($row['DEL_INIT_DATE']);
            }
            $oAppDel->save();

            $rs->next();
            $row = $rs->getRow();
        }
    }



    public function getLastDeleration($APP_UID)
    {
        $c = new Criteria('workflow');
        $c->addSelectColumn(AppDelegationPeer::APP_UID);
        $c->addSelectColumn(AppDelegationPeer::DEL_INDEX);
        $c->addSelectColumn(AppDelegationPeer::DEL_DELEGATE_DATE);
        $c->addSelectColumn(AppDelegationPeer::DEL_INIT_DATE);
        $c->addSelectColumn(AppDelegationPeer::DEL_TASK_DUE_DATE);
        $c->addSelectColumn(AppDelegationPeer::DEL_FINISH_DATE);
        $c->addSelectColumn(AppDelegationPeer::DEL_DURATION);
        $c->addSelectColumn(AppDelegationPeer::DEL_QUEUE_DURATION);
        $c->addSelectColumn(AppDelegationPeer::DEL_DELAY_DURATION);
        $c->addSelectColumn(AppDelegationPeer::DEL_STARTED);
        $c->addSelectColumn(AppDelegationPeer::DEL_FINISHED);
        $c->addSelectColumn(AppDelegationPeer::DEL_DELAYED);
        $c->addSelectColumn(AppDelegationPeer::USR_UID);

        $c->add(AppDelegationPeer::APP_UID, $APP_UID);
        $c->addDescendingOrderByColumn(AppDelegationPeer::DEL_INDEX);
        $rs = AppDelegationPeer::doSelectRS($c);
        $rs->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $rs->next();
        return $rs->getRow();
    }

    public static function getCurrentIndex($appUid)
    {
        $oCriteria = new Criteria();
        $oCriteria->addSelectColumn(AppDelegationPeer::DEL_INDEX);
        $oCriteria->add(AppDelegationPeer::APP_UID, $appUid);
        $oCriteria->addDescendingOrderByColumn(AppDelegationPeer::DEL_INDEX);
        $oRuleSet = AppDelegationPeer::doSelectRS($oCriteria);
        $oRuleSet->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $oRuleSet->next();
        $data = $oRuleSet->getRow();
        return (int)$data['DEL_INDEX'];
    }

    public static function getCurrentTask($appUid)
    {
        $oCriteria = new Criteria();
        $oCriteria->addSelectColumn(AppDelegationPeer::TAS_UID);
        $oCriteria->add(AppDelegationPeer::APP_UID, $appUid);
        $oCriteria->addDescendingOrderByColumn(AppDelegationPeer::DEL_INDEX);
        $oRuleSet = AppDelegationPeer::doSelectRS($oCriteria);
        $oRuleSet->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $oRuleSet->next();
        $data = $oRuleSet->getRow();
        return $data['TAS_UID'];
    }

    /**
     * This function get the current user related to the specific case and index
     *
     * @param string $appUid, Uid related to the case
     * @param integer $index, Index to review
     *
     * @return array
    */
    public static function getCurrentUsers($appUid, $index)
    {
        $oCriteria = new Criteria();
        $oCriteria->addSelectColumn(AppDelegationPeer::USR_UID);
        $oCriteria->add(AppDelegationPeer::APP_UID, $appUid);
        $oCriteria->add(AppDelegationPeer::DEL_THREAD_STATUS, 'OPEN');
        $oCriteria->add(AppDelegationPeer::DEL_INDEX, $index);
        $oRuleSet = AppDelegationPeer::doSelectRS($oCriteria);
        $oRuleSet->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $oRuleSet->next();
        $data = $oRuleSet->getRow();
        return $data;
    }

    /**
    * Verify if the current case is already routed.
    *
    * @param string $appUid the uid of the application
     *
    * @return array $Fields the fields
    */

    public function alreadyRouted($appUid, $sDelIndex)
    {
        $c = new Criteria("workflow");
        $c->clearSelectColumns();
        $c->addSelectColumn(AppDelegationPeer::APP_UID);
        $c->add(AppDelegationPeer::APP_UID, $appUid);
        $c->add(AppDelegationPeer::DEL_INDEX, $sDelIndex);
        $c->add(AppDelegationPeer::DEL_FINISH_DATE, null, Criteria::ISNOTNULL);
        $result = AppDelegationPeer::doSelectRS($c);
        $result->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        if ($result->next()) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Get all task before Join Threads
    *
    * @param string $nextTaskUid
    * @param string $sAppUid
    * @return array $index
    */
    public static function getAllTasksBeforeSecJoin($nextTaskUid, $sAppUid, $sDelPrevious, $threadStatus = '')
    {
        $criteriaR = new Criteria('workflow');
        $criteriaR->addSelectColumn(AppDelegationPeer::DEL_INDEX);
        $criteriaR->addSelectColumn(AppDelegationPeer::DEL_PREVIOUS);
        $criteriaR->addJoin(RoutePeer::TAS_UID, AppDelegationPeer::TAS_UID, Criteria::LEFT_JOIN);
        $criteriaR->add(RoutePeer::ROU_NEXT_TASK, $nextTaskUid, Criteria::EQUAL);
        $criteriaR->add(RoutePeer::ROU_TYPE, 'SEC-JOIN', Criteria::EQUAL);
        $criteriaR->add(AppDelegationPeer::APP_UID, $sAppUid, Criteria::EQUAL);
        $criteriaR->add(AppDelegationPeer::DEL_PREVIOUS, $sDelPrevious, Criteria::EQUAL);
        if (!empty($threadStatus)) {
            $criteriaR->add(AppDelegationPeer::DEL_THREAD_STATUS, $threadStatus, Criteria::EQUAL);
        }
        $rsCriteriaR = RoutePeer::doSelectRS($criteriaR);
        $rsCriteriaR->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $index = array();
        $c = 0;
        while ($rsCriteriaR->next()) {
            $row = $rsCriteriaR->getRow();
            $index[$c++] = $row['DEL_INDEX'];
        }
        return $index;
    }

    /**
    * Verify if we need to create a new Thread
    *
    * @param array $index
    * @param string $sAppUid
    * @param string $sUsrUid
    * @return boolean $res
    */
    public static function createThread($index, $sAppUid, $sUsrUid = '')
    {
        $criteriaDel = new Criteria("workflow");
        $criteriaDel->addSelectColumn(AppDelegationPeer::DEL_INDEX);
        $criteriaDel->addSelectColumn(AppDelegationPeer::DEL_PREVIOUS);
        $criteriaDel->add(AppDelegationPeer::APP_UID, $sAppUid);
        $criteriaDel->add(AppDelegationPeer::DEL_PREVIOUS, $index, Criteria::IN);
        if ($sUsrUid !== '') {
            $criteriaDel->add(AppDelegationPeer::USR_UID, $sUsrUid);
        }
        $criteriaDel = AppDelegationPeer::doSelectRS($criteriaDel);
        $criteriaDel->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $res = $criteriaDel->next();
        return $res;
    }

    /**
    * Get all Threads for Multiple Instance
    *
    * @param string $sPrevious
    * @param string $sAppUid
    * @return array $index
    */
    public static function getAllTheardMultipleInstance($sPrevious, $sAppUid)
    {
        $criteriaR = new Criteria('workflow');
        $criteriaR->addSelectColumn(AppDelegationPeer::DEL_INDEX);
        $criteriaR->add(AppDelegationPeer::APP_UID, $sAppUid, Criteria::EQUAL);
        $criteriaR->add(AppDelegationPeer::DEL_PREVIOUS, $sPrevious, Criteria::EQUAL);
        $rsCriteriaR = AppDelegationPeer::doSelectRS($criteriaR);
        $rsCriteriaR->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $index = array();
        $c = 0;
        while ($rsCriteriaR->next()) {
            $row = $rsCriteriaR->getRow();
            $index[$c++] = $row['DEL_INDEX'];
        }
        return $index;
    }

    /**
     * This function get the columns by Id indexing
     *
     * @param string $appUid
     * @param integer $delIndex
     *
     * @return array|null
     * @throws Exception
     */
    public function getColumnIds($appUid, $delIndex)
    {
        try {
            $columnsId = [];
            if ($delIndex > 0) {
                $row = AppDelegationPeer::retrieveByPK($appUid, $delIndex);
                if (!is_null($row)) {
                    $fields = $row->toArray(BasePeer::TYPE_FIELDNAME);
                    $this->fromArray($fields, BasePeer::TYPE_FIELDNAME);
                    $columnsId['APP_NUMBER'] = $fields['APP_NUMBER'];
                    $columnsId['USR_ID'] = $fields['USR_ID'];
                    $columnsId['TAS_ID'] = $fields['TAS_ID'];
                    $columnsId['PRO_ID'] = $fields['PRO_ID'];
                    return $columnsId;
                } else {
                    throw (new Exception("The row '" . $appUid . "' , '" . $delIndex . "' in table APP_DELEGATION doesn't exist!"));
                }
            }

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get the user assigned in the index
     *
     * @param string $appUid
     * @param string $delIndex
     *
     * @return string|null
    */
    public function getUserAssignedInThread($appUid, $delIndex)
    {
        $currentUserUid = null;

        $result = $this->Load($appUid, $delIndex);
        if (isset($result["USR_UID"])) {
            $currentUserUid = $result["USR_UID"];
        }

        return $currentUserUid;
    }

    /**
     * Get column PRO_ID related to the APP_NUMBER
     *
     * @param integer $appNumber
     *
     * @return integer
    */
    public function getProcessId($appNumber)
    {
        $proId = 0;
        $criteria = new Criteria("workflow");
        $criteria->add(AppDelegationPeer::APP_NUMBER, $appNumber);
        $dataset = AppDelegationPeer::doSelectOne($criteria);
        if (!is_null($dataset)) {
            $proId = $dataset->getProId();
        }

        return $proId;
    }
    /**
     * Get the last index by a specific status
     *
     * @param integer $appNumber
     * @param string $status
     *
     * @return integer
     */
    public static function getLastIndexByStatus($appNumber, $status = 'OPEN')
    {
        $delIndex = 0;
        $criteria = new Criteria();
        $criteria->add(AppDelegationPeer::APP_NUMBER, $appNumber);
        $criteria->add(AppDelegationPeer::DEL_THREAD_STATUS, $status);
        $criteria->addDescendingOrderByColumn(AppDelegationPeer::DEL_INDEX);
        $dataset = AppDelegationPeer::doSelectOne($criteria);
        if (!is_null($dataset)) {
            $delIndex = $dataset->getDelIndex();
        }

        return $delIndex;
    }

    /**
     * Get the last index assigned to the user by a specific status
     *
     * @param integer $appNumber
     * @param integer $usrId
     * @param string $status
     *
     * @return integer
    */
    public static function getLastIndexByUserAndStatus($appNumber, $usrId, $status = 'OPEN')
    {
        $delIndex = 0;
        $criteria = new Criteria();
        $criteria->add(AppDelegationPeer::APP_NUMBER, $appNumber);
        $criteria->add(AppDelegationPeer::USR_ID, $usrId);
        $criteria->add(AppDelegationPeer::DEL_THREAD_STATUS, $status);
        $criteria->addDescendingOrderByColumn(AppDelegationPeer::DEL_INDEX);
        $dataset = AppDelegationPeer::doSelectOne($criteria);
        if (!is_null($dataset)) {
            $delIndex = $dataset->getDelIndex();
        }

        return $delIndex;
    }

    /**
     * Update the priority in AppDelegation table, using the defined variable in task
     *
     * @param integer $delIndex
     * @param string $tasUid
     * @param string $appUid
     * @param array $fieldAppData
     *
     * @return void
     *
     * @see Cases->update()
     *
    */
    public function updatePriority($delIndex, $tasUid, $appUid, $fieldAppData)
    {
        if (!empty($delIndex) && !empty($tasUid)) {
            //Optimized code to avoid load task content row.
            $criteria = new Criteria();
            $criteria->clearSelectColumns();
            $criteria->addSelectColumn(TaskPeer::TAS_PRIORITY_VARIABLE);
            $criteria->add(TaskPeer::TAS_UID, $tasUid);
            $rs = TaskPeer::doSelectRS($criteria);
            $rs->setFetchmode(ResultSet::FETCHMODE_ASSOC);
            $rs->next();
            $row = $rs->getRow();
            $tasPriority = substr($row['TAS_PRIORITY_VARIABLE'], 2);
            //End optimized code.

            $x = $fieldAppData;
            if (!empty($x[$tasPriority])) {
                $array = [];
                $array['APP_UID'] = $appUid;
                $array['DEL_INDEX'] = $delIndex;
                $array['TAS_UID'] = $tasUid;
                $array['DEL_PRIORITY'] = (isset($x[$tasPriority]) ?
                    ($x[$tasPriority] >= 1 && $x[$tasPriority] <= 5 ? $x[$tasPriority] : '3') : '3');
                $this->update($array);
            }
        }
    }
}
