<?php

namespace ProcessMaker\BusinessModel\Cases;

use Datetime;
use Exception;
use ProcessMaker\BusinessModel\Interfaces\CasesInterface;
use ProcessMaker\BusinessModel\Validator;
use ProcessMaker\Model\Delegation;
use ProcessMaker\Model\Task;
use ProcessMaker\Model\User;

class AbstractCases implements CasesInterface
{
    // Constants for validate values
    const REVIEW_STATUSES = ['READ', 'UNREAD']; //0 => READ, 1 => UNREAD
    const PARTICIPATED_STATUSES = ['STARTED', 'IN_PROGRESS', 'COMPLETED', 'SUPERVISING'];
    const RISK_STATUSES = ['ON_TIME', 'AT_RISK', 'OVERDUE'];
    const CASE_STATUSES = [1 => 'DRAFT', 2 => 'TO_DO', 3 => 'COMPLETED', 4 => 'CANCELED'];
    const ORDER_DIRECTIONS = ['DESC', 'ASC'];
    const CORRECT_CANCELED_STATUS = 'CANCELED';
    const INCORRECT_CANCELED_STATUS = 'CANCELLED';
    const PRIORITIES = [1 => 'VL', 2 => 'L', 3 => 'N', 4 => 'H', 5 => 'VH'];
    // Task Colors
    const TASK_COLORS = [1 => 'green', 2 => 'red', 3 => 'orange', 4 => 'blue', 5 => 'gray'];
    const TASK_STATUS = [1 => 'ON_TIME', 2 => 'OVERDUE', 3 => 'DRAFT', 4 => 'PAUSED', 5 => 'UNASSIGNED'];
    const COLOR_ON_TIME = 1; // green
    const COLOR_OVERDUE = 2; // red
    const COLOR_DRAFT = 3; // orange
    const COLOR_PAUSED = 4; // blue
    const COLOR_UNASSIGNED = 5; // gray
    // Status values
    const STATUS_DRAFT = 1;
    const STATUS_TODO = 2;
    const STATUS_COMPLETED = 3;
    const STATUS_CANCELED = 4;
    // Order by column allowed
    const ORDER_BY_COLUMN_ALLOWED = ['APP_NUMBER', 'DEL_TITLE', 'PRO_TITLE'];

    // Filter by category using the Id field
    private $categoryId = 0;

    // Filter by category from a process, know as "$category" in the old lists classes
    private $categoryUid = '';

    // Filter by process, know as "$process" in the old lists classes
    private $processUid = '';

    // Filter by process using the Id field
    private $processId = 0;

    // Filter by task using the Id field
    private $taskId = 0;

    // Filter by user, know as "$user" in the old lists classes
    private $userUid = '';

    // Filter by user using the Id field
    private $userId = 0;

    // Filter by user who completed using the Id field
    private $userCompleted = 0;

    // Filter by user who started using the Id field
    private $userStarted = 0;

    // Value to search, can be a text or an application number, know as "$search" in the old lists classes
    private $valueToSearch = '';

    // Filter cases depending if were read or not, know as "$filter" in the old lists classes
    private $reviewStatus = '';

    // Filter cases depending if the case was started or completed by the current user, know as "$filter" in the old lists classes
    private $participatedStatus = '';

    // Filter by risk status, know as "$filterStatus" in the old list "inbox" class
    private $riskStatus = '';

    // Filter by specific priority
    private $priority = 0;

    // Filter by specific priorities
    private $priorities = [];

    // Filter by case status, know as "$filterStatus" in the old "participated last" class
    private $caseStatus = '';

    // Filter by case statuses
    private $caseStatuses = [1, 2, 3, 4];

    // Filter by a specific case, know as "$caseLink" in the old lists classes
    private $caseUid = '';

    // Filter by a specific case using case number
    private $caseNumber = 0;

    // Filter by specific cases using the case numbers like [1,4,8]
    private $casesNumbers = [];

    // Filter by only one range of case number
    private $caseNumberFrom = 0;
    private $caseNumberTo = 0;

    // Filter more than one range of case number
    private $rangeCasesFromTo = [];

    // Filter by a specific cases like 1,3-5,8,10-15
    private $filterCases = '';

    // Filter by a specific case title
    private $caseTitle = '';

    // Filter by specific cases, know as "$appUidCheck" in the old lists classes
    private $casesUids = [];
    
    // Filter by Send By
    private $sendBy = '';

    // Filter range related to the start case date
    private $startCaseFrom = '';
    private $startCaseTo = '';

    // Filter range related to the finish case date
    private $finishCaseFrom = '';
    private $finishCaseTo = '';

    // Filter range related to the delegate date
    private $delegateFrom = '';
    private $delegateTo = '';

    // Filter range related to the finish date
    private $finishFrom = '';
    private $finishTo = '';

    // Filter range related to the due date
    private $dueFrom = '';
    private $dueTo = '';

    // Column by which the results will be sorted, know as "$sort" in the old lists classes
    private $orderByColumn = 'APP_NUMBER';

    // Sorts the data in descending or ascending order, know as "$dir" in the old lists classes
    private $orderDirection = 'DESC';

    // Results should be paged?
    private $paged = true;

    // Offset is used to identify the starting point to return rows from a result set, know as "$start" in the old lists classes
    private $offset = 0;

    // Number of rows to return
    private $limit = 15;

    /**
     * Set Category Uid value
     *
     * @param int $category
     */
    public function setCategoryId(int $category)
    {
        $this->categoryId = $category;
    }

    /**
     * Get Category Id value
     *
     * @return string
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Set Category Uid value
     *
     * @param string $categoryUid
     */
    public function setCategoryUid(string $categoryUid)
    {
        $this->categoryUid = $categoryUid;
    }

    /**
     * Get Category Uid value
     *
     * @return string
     */
    public function getCategoryUid()
    {
        return $this->categoryUid;
    }

    /**
     * Set Process Uid value
     *
     * @param string $processUid
     */
    public function setProcessUid(string $processUid)
    {
        $this->processUid = $processUid;
    }

    /**
     * Get Process Uid value
     *
     * @return string
     */
    public function getProcessUid()
    {
        return $this->processUid;
    }

    /**
     * Set Process Id value
     *
     * @param int $processId
     */
    public function setProcessId(int $processId)
    {
        $this->processId = $processId;
    }

    /**
     * Get Process Id value
     *
     * @return int
     */
    public function getProcessId()
    {
        return $this->processId;
    }

    /**
     * Set task Id value
     *
     * @param int $taskId
     */
    public function setTaskId(int $taskId)
    {
        $this->taskId = $taskId;
    }

    /**
     * Get task Id value
     *
     * @return int
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * Set User Uid value
     *
     * @param string $userUid
     */
    public function setUserUid(string $userUid)
    {
        $this->userUid = $userUid;
    }

    /**
     * Get User Uid value
     *
     * @return string
     */
    public function getUserUid()
    {
        return $this->userUid;
    }

    /**
     * Set User Id value
     *
     * @param int $userId
     */
    public function setUserId(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get User Id value
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set User Id value
     *
     * @param int $userId
     */
    public function setUserCompletedId(int $userId)
    {
        $this->userCompleted = $userId;
    }

    /**
     * Get User Id value
     *
     * @return int
     */
    public function getUserCompletedId()
    {
        return $this->userCompleted;
    }

    /**
     * Set User Id value
     *
     * @param int $userId
     */
    public function setUserStartedId(int $userId)
    {
        $this->userStarted = $userId;
    }

    /**
     * Get User Id value
     *
     * @return int
     */
    public function getUserStartedId()
    {
        return $this->userStarted;
    }

    /**
     * Set send by
     * 
     * @param type $sendBy
     */
    public function setSendBy(string $sendBy)
    {
        $this->sendBy = $sendBy;
    }

    /**
     * Get send by.
     * 
     * @return string
     */
    public function getSendBy()
    {
        return $this->sendBy;
    }

    /**
     * Set value to search
     *
     * @param string $valueToSearch
     */
    public function setValueToSearch(string $valueToSearch)
    {
        $this->valueToSearch = $valueToSearch;
    }

    /**
     * Get value to search
     *
     * @return string
     */
    public function getValueToSearch()
    {
        return $this->valueToSearch;
    }

    /**
     * Set review status
     *
     * @param string $status
     *
     * @throws Exception
     */
    public function setReviewStatus(string $status)
    {
        // Convert the value to upper case
        $status = strtoupper($status);

        // Validate the status value
        if (!empty($status)) {
            if (!in_array($status, self::REVIEW_STATUSES)) {
                throw new Exception("Inbox status '{$status}' is not valid.");
            }
        }

        $this->reviewStatus = $status;
    }

    /**
     * Get inbox status
     *
     * @return string
     */
    public function getReviewStatus()
    {
        return $this->reviewStatus;
    }

    /**
     * Set participated status
     *
     * @param string $participatedStatus
     *
     * @throws Exception
     */
    public function setParticipatedStatus(string $participatedStatus)
    {
        // Convert the value to upper case
        $participatedStatus = strtoupper($participatedStatus);

        // Validate the participated status
        if (!in_array($participatedStatus, self::PARTICIPATED_STATUSES)) {
            throw new Exception("Participated status '{$participatedStatus}' is not valid.");
        }

        $this->participatedStatus = $participatedStatus;
    }

    /**
     * Get participated status
     *
     * @return string
     */
    public function getParticipatedStatus()
    {
        return $this->participatedStatus;
    }

    /**
     * Set risk status
     *
     * @param string $riskStatus
     *
     * @throws Exception
     */
    public function setRiskStatus(string $riskStatus)
    {
        // Convert the value to upper case
        $riskStatus = strtoupper($riskStatus);

        // Validate the risk status
        if (!in_array($riskStatus, self::RISK_STATUSES)) {
            throw new Exception("Risk status '{$riskStatus}' is not valid.");
        }

        $this->riskStatus = $riskStatus;
    }

    /**
     * Get risk value
     *
     * @return string
     */
    public function getRiskStatus()
    {
        return $this->riskStatus;
    }

    /**
     * Set priority value
     *
     * @param string $priority
     *
     * @throws Exception
     */
    public function setPriority(string $priority)
    {
        // Validate the priority value
        if (!empty($priority)) {
            $priorityCode = array_search($priority, self::PRIORITIES);
            if (empty($priorityCode) && $priorityCode !== 0) {
                throw new Exception("Priority value {$priority} is not valid.");
            }
        } else {
            // List all priorities
            $priorityCode = 0;
        }

        $this->priority = $priorityCode;
    }

    /**
     * Get priority status
     *
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set priorities
     *
     * @param array $priorities
     *
     * @throws Exception
     */
    public function setPriorities(array $priorities)
    {
        $prioritiesCode = [];
        foreach ($priorities as $priority) {
            // Validate the priority value
            $priorityCode = array_search($priority, self::PRIORITIES);
            if (empty($priorityCode) && $priorityCode !== 0) {
                throw new Exception("Priority value {$priority} is not valid.");
            } else {
                array_push($prioritiesCode, $priorityCode);
            }
        }
        $this->priorities = $prioritiesCode;
    }

    /**
     * Get priorities
     *
     * @return array
     */
    public function getPriorities()
    {
        return $this->priorities;
    }

    /**
     * Set Case status
     *
     * @param string $status
     *
     * @throws Exception
     */
    public function setCaseStatus(string $status)
    {
        // Fix the canceled status, this is a legacy code error
        if ($status === self::INCORRECT_CANCELED_STATUS) {
            $status = self::CORRECT_CANCELED_STATUS;
        }
        $statusCode = 0;
        // Validate the status value
        if (!empty($status)) {
            $statusCode = array_search($status, self::CASE_STATUSES);
            if (empty($statusCode) && $statusCode !== 0) {
                throw new Exception("Case status '{$status}' is not valid.");
            }
        }
        $this->caseStatus = $statusCode;
    }

    /**
     * Get Case Status
     *
     * @return int
     */
    public function getCaseStatus()
    {
        return $this->caseStatus;
    }

    /**
     * Set Case statuses
     *
     * @param array $statuses
     *
     * @throws Exception
     */
    public function setCaseStatuses(array $statuses)
    {
        $statusCodes = [];
        foreach ($statuses as $status) {
            // Fix the canceled status, this is a legacy code error
            if ($status === self::INCORRECT_CANCELED_STATUS) {
                $status = self::CORRECT_CANCELED_STATUS;
            }
            // Validate the status value
            if (!empty($status)) {
                $statusCode = array_search($status, self::CASE_STATUSES);
                if (empty($statusCode) && $statusCode !== 0) {
                    throw new Exception("Case status '{$status}' is not valid.");
                } else {
                    array_push($statusCodes, $statusCode);
                }
            }
        }
        $this->caseStatuses = $statusCodes;
    }

    /**
     * Get Case Statuses
     *
     * @return array
     */
    public function getCaseStatuses()
    {
        return $this->caseStatuses;
    }

    /**
     * Set Case Uid
     *
     * @param string $caseUid
     */
    public function setCaseUid(string $caseUid)
    {
        $this->caseUid = $caseUid;
    }

    /**
     * Get Case Uid
     *
     * @return string
     */
    public function getCaseUid()
    {
        return $this->caseUid;
    }

    /**
     * Set Case Number
     *
     * @param int $caseNumber
     */
    public function setCaseNumber(int $caseNumber)
    {
        $this->caseNumber = $caseNumber;
    }

    /**
     * Get Case Number
     *
     * @return int
     */
    public function getCaseNumber()
    {
        return $this->caseNumber;
    }

    /**
     * Set range of case number from
     *
     * @param int $from
     */
    public function setCaseNumberFrom(int $from)
    {
        $this->caseNumberFrom = $from;
    }

    /**
     * Get from Case Number
     *
     * @return int
     */
    public function getCaseNumberFrom()
    {
        return $this->caseNumberFrom;
    }

    /**
     * Set range of case number to
     *
     * @param int $to
     */
    public function setCaseNumberTo(int $to)
    {
        $this->caseNumberTo = $to;
    }

    /**
     * Get to Case Number
     *
     * @return int
     */
    public function getCaseNumberTo()
    {
        return $this->caseNumberTo;
    }

    /**
     * Set more than one range of cases
     *
     * @param array $rangeCases
     */
    public function setRangeCasesFromTo(array $rangeCases)
    {
        $this->rangeCasesFromTo = $rangeCases;
    }

    /**
     * Get more than one range of cases
     *
     * @return array
     */
    public function getRangeCasesFromTo()
    {
        return $this->rangeCasesFromTo;
    }

    /**
     * Set filter of cases like '1,3-5,8,10-15'
     *
     * @param string $filterCases
     */
    public function setFilterCases(string $filterCases)
    {
        $this->filterCases = $filterCases;
        // Review the cases defined in the filter
        $rangeOfCases = explode(",", $filterCases);
        $specificCases = [];
        $rangeCases = [];
        foreach ($rangeOfCases as $cases) {
            if (is_numeric($cases)) {
                array_push($specificCases, $cases);
            } else {
                array_push($rangeCases, $cases);
            }
        }
        $this->setCasesNumbers($specificCases);
        $this->setRangeCasesFromTo($rangeCases);
    }

    /**
     * Get filter of cases
     *
     * @return string
     */
    public function getFilterCases()
    {
        return $this->filterCases;
    }

    /**
     * Set Case Title
     *
     * @param string $caseTitle
     */
    public function setCaseTitle(string $caseTitle)
    {
        $this->caseTitle = $caseTitle;
    }

    /**
     * Get Case Title
     *
     * @return string
     */
    public function getCaseTitle()
    {
        return $this->caseTitle;
    }

    /**
     * Set Cases Uids
     *
     * @param array $casesUid
     */
    public function setCasesUids(array $casesUid)
    {
        $this->casesUids = $casesUid;
    }

    /**
     * Get Cases Uids
     *
     * @return array
     */
    public function getCasesUids()
    {
        return $this->casesUids;
    }

    /**
     * Set Cases Numbers
     *
     * @param array $casesNumbers
     */
    public function setCasesNumbers(array $casesNumbers)
    {
        $this->casesNumbers = $casesNumbers;
    }

    /**
     * Get Cases Numbers
     *
     * @return array
     */
    public function getCasesNumbers()
    {
        return $this->casesNumbers;
    }

    /**
     * Set start case from
     *
     * @param string $from
     *
     * @throws Exception
     */
    public function setStartCaseFrom(string $from)
    {
        if (!Validator::isDate($from, 'Y-m-d')) {
            throw new Exception("Value '{$from}' is not a valid date.");
        }
        $this->startCaseFrom = $from;
    }

    /**
     * Get start case from
     *
     * @return string
     */
    public function getStartCaseFrom()
    {
        return $this->startCaseFrom;
    }

    /**
     * Set start case to
     *
     * @param string $to
     *
     * @throws Exception
     */
    public function setStartCaseTo(string $to)
    {
        if (!Validator::isDate($to, 'Y-m-d')) {
            throw new Exception("Value '{$to}' is not a valid date.");
        }
        $this->startCaseTo = $to;
    }

    /**
     * Get start case to
     *
     * @return string
     */
    public function getStartCaseTo()
    {
        return $this->startCaseTo;
    }

    /**
     * Set finish case from
     *
     * @param string $from
     *
     * @throws Exception
     */
    public function setFinishCaseFrom(string $from)
    {
        if (!Validator::isDate($from, 'Y-m-d')) {
            throw new Exception("Value '{$from}' is not a valid date.");
        }
        $this->finishCaseFrom = $from;
    }

    /**
     * Get start case from
     *
     * @return string
     */
    public function getFinishCaseFrom()
    {
        return $this->finishCaseFrom;
    }

    /**
     * Set start case to
     *
     * @param string $to
     *
     * @throws Exception
     */
    public function setFinishCaseTo(string $to)
    {
        if (!Validator::isDate($to, 'Y-m-d')) {
            throw new Exception("Value '{$to}' is not a valid date.");
        }
        $this->finishCaseTo = $to;
    }

    /**
     * Get start case to
     *
     * @return string
     */
    public function getFinishCaseTo()
    {
        return $this->finishCaseTo;
    }

    /**
     * Set Newest Than value
     *
     * @param string $delegateFrom
     *
     * @throws Exception
     */
    public function setDelegateFrom(string $delegateFrom)
    {
        if (!Validator::isDate($delegateFrom, 'Y-m-d')) {
            throw new Exception("Value '{$delegateFrom}' is not a valid date.");
        }
        $this->delegateFrom = $delegateFrom;
    }

    /**
     * Get Newest Than value
     *
     * @return string
     */
    public function getDelegateFrom()
    {
        return $this->delegateFrom;
    }

    /**
     * Set Oldest Than value
     *
     * @param string $delegateTo
     *
     * @throws Exception
     */
    public function setDelegateTo(string $delegateTo)
    {
        if (!Validator::isDate($delegateTo, 'Y-m-d')) {
            throw new Exception("Value '{$delegateTo}' is not a valid date.");
        }
        $this->delegateTo = $delegateTo;
    }

    /**
     * Get Oldest Than value
     *
     * @return string
     */
    public function getDelegateTo()
    {
        return $this->delegateTo;
    }

    /**
     * Set finish date value
     *
     * @param string $from
     *
     * @throws Exception
     */
    public function setFinishFrom(string $from)
    {
        if (!Validator::isDate($from, 'Y-m-d')) {
            throw new Exception("Value '{$from}' is not a valid date.");
        }
        $this->finishFrom = $from;
    }

    /**
     * Get finish date value
     *
     * @return string
     */
    public function getFinishFrom()
    {
        return $this->finishFrom;
    }

    /**
     * Set finish date value
     *
     * @param string $to
     *
     * @throws Exception
     */
    public function setFinishTo(string $to)
    {
        if (!Validator::isDate($to, 'Y-m-d')) {
            throw new Exception("Value '{$to}' is not a valid date.");
        }
        $this->finishTo = $to;
    }

    /**
     * Get finish date value
     *
     * @return string
     */
    public function getFinishTo()
    {
        return $this->finishTo;
    }

    /**
     * Set due date from
     *
     * @param string $dueFrom
     *
     * @throws Exception
     */
    public function setDueFrom(string $dueFrom)
    {
        if (!Validator::isDate($dueFrom, 'Y-m-d')) {
            throw new Exception("Value '{$dueFrom}' is not a valid date.");
        }
        $this->dueFrom = $dueFrom;
    }

    /**
     * Get due date from
     *
     * @return string
     */
    public function getDueFrom()
    {
        return $this->dueFrom;
    }

    /**
     * Set due date to
     *
     * @param string $dueTo
     *
     * @throws Exception
     */
    public function setDueTo(string $dueTo)
    {
        if (!Validator::isDate($dueTo, 'Y-m-d')) {
            throw new Exception("Value '{$dueTo}' is not a valid date.");
        }
        $this->dueTo = $dueTo;
    }

    /**
     * Get due date to
     *
     * @return string
     */
    public function getDueTo()
    {
        return $this->dueTo;
    }

    /**
     * Set order by column
     *
     * @param string $orderByColumn
     */
    public function setOrderByColumn(string $orderByColumn)
    {
        // Convert the value to upper case
        $orderByColumn = strtoupper($orderByColumn);

        // Validate the order by column
        if (!in_array($orderByColumn, self::ORDER_BY_COLUMN_ALLOWED)) {
            throw new Exception("Order by column '{$orderByColumn}' is not valid.");
        }

        $this->orderByColumn = $orderByColumn;
    }

    /**
     * Get order by column
     *
     * @return string
     */
    public function getOrderByColumn()
    {
        return $this->orderByColumn;
    }

    /**
     * Set order direction
     *
     * @param string $orderDirection
     *
     * @throws Exception
     */
    public function setOrderDirection(string $orderDirection)
    {
        // Convert the value to upper case
        $orderDirection = strtoupper($orderDirection);

        // Validate the order direction
        if (!in_array($orderDirection, self::ORDER_DIRECTIONS)) {
            throw new Exception("Order direction '{$orderDirection}' is not valid.");
        }

        $this->orderDirection = $orderDirection;
    }

    /**
     * Get order direction
     *
     * @return string
     */
    public function getOrderDirection()
    {
        return $this->orderDirection;
    }

    /**
     * Set if is paged
     *
     * @param bool $paged
     */
    public function setPaged(bool $paged)
    {
        $this->paged = (bool) $paged;
    }

    /**
     * Get if is paged
     *
     * @return bool
     */
    public function getPaged()
    {
        return $this->paged;
    }

    /**
     * Set offset value
     *
     * @param int $offset
     */
    public function setOffset(int $offset)
    {
        $this->offset = (int) $offset;
    }

    /**
     * Get offset value
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Set limit value
     *
     * @param int $limit
     */
    public function setLimit(int $limit)
    {
        $this->limit = (int) $limit;
    }

    /**
     * Get limit value
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Get task color according the due date
     *
     * @param string $dueDate
     * @param string $statusThread
     * @param string $dateToCompare
     *
     * @return int
     */
    public function getTaskColor(string $dueDate, string $statusThread = '', $dateToCompare = 'now')
    {
        $currentDate = new DateTime($dateToCompare);
        $dueDate = new DateTime($dueDate);
        if ($currentDate > $dueDate) {
            // Overdue: When the current date is mayor to the due date of the case
            $taskColor = self::COLOR_OVERDUE;
        } else {
            // OnTime
            $taskColor = self::COLOR_ON_TIME;
            if (get_class($this) === Draft::class || $statusThread === self::TASK_STATUS[3]) {
                $taskColor = self::COLOR_DRAFT;
            }
            if (get_class($this) === Paused::class || $statusThread === self::TASK_STATUS[4]) {
                $taskColor = self::COLOR_PAUSED;
            }
            if (get_class($this) === Unassigned::class || $statusThread === self::TASK_STATUS[5]) {
                $taskColor = self::COLOR_UNASSIGNED;
            }
        }

        return $taskColor;
    }

    /**
     * Get task color according the due date
     *
     * @param string $pendingJson
     * @param bool $onlyTask
     * @param string $statusThread
     * @param string $dateToCompare
     *
     * @return int
     */
    public function prepareTaskPending($pendingJson, $onlyTask = true, $statusThread = '', $dateToCompare = '')
    {
        $taskPending = json_decode($pendingJson, true);
        $result = [];
        $threadTasks = [];
        $threadUsers = [];
        $threadTitles = [];
        $i = 0;
        foreach ($taskPending as $thread) {
            foreach ($thread as $key => $row) {
                // Thread tasks
                if ($key === 'tas_id') {
                    $threadTasks[$i][$key] = $row;
                    $threadTasks[$i]['tas_title'] = (!empty($row)) ? Task::where('TAS_ID', $row)->first()->TAS_TITLE : '';
                }
                if ($key === 'due_date') {
                    $threadTasks[$i][$key] = $row;
                    // Get the end date for calculate the delay
                    $endDate = ($dateToCompare !== 'now') ? $endDate = $dateToCompare : date("Y-m-d H:i:s");
                    $threadTasks[$i]['delay'] = getDiffBetweenDates($row, $endDate);
                    // Get task color label
                    $threadTasks[$i]['tas_color'] = (!empty($row)) ? $this->getTaskColor($row, $statusThread, $dateToCompare) : '';
                    $threadTasks[$i]['tas_color_label'] = (!empty($row)) ? self::TASK_COLORS[$threadTasks[$i]['tas_color']] : '';
                    $threadTasks[$i]['tas_status'] = self::TASK_STATUS[$threadTasks[$i]['tas_color']];
                }
                // Review if require other information
                if ($onlyTask) {
                    // Thread tasks
                    if ($key === 'user_id') {
                        $threadTasks[$i][$key] = $row;
                        // Get the user tooltip information
                        $threadTasks[$i]['user_tooltip'] = User::getInformation($row);
                    }
                } else {
                    // Thread users
                    if ($key === 'user_id') {
                        $threadUsers[$i][$key] = $row;
                        // Get user information
                        $userInfo = User::getInformation($row);
                        $threadUsers[$i]['usr_username'] = !empty($userInfo) ? $userInfo['usr_username'] : '';
                        $threadUsers[$i]['usr_lastname'] = !empty($userInfo) ? $userInfo['usr_lastname'] : '';
                        $threadUsers[$i]['usr_firstname'] = !empty($userInfo) ? $userInfo['usr_firstname'] : '';
                        $threadUsers[$i]['user_tooltip'] = User::getInformation($row);
                    }
                    // Thread titles
                    if ($key === 'del_id') {
                        $threadTitles[$i][$key] = $row;
                        $threadTitles[$i]['thread_title'] = (!empty($row)) ? Delegation::where('DELEGATION_ID', $row)->first()->DEL_TITLE : '';
                    }
                }
            }
            $i++;
        }
        // Define the array responses
        $result['THREAD_TASKS'] = $threadTasks;
        $result['THREAD_USERS'] = $threadUsers;
        $result['THREAD_TITLES'] = $threadTitles;

        return $result;
    }

    /**
     * Get the thread information
     *
     * @param array $thread
     * @param bool $addUserInfo
     * @param bool $addThreadInfo
     *
     * @return array
     */
    public function threadInformation(array $thread, $addUserInfo = false, $addThreadInfo = true)
    {
        $status = '';
        $finishDate = 'now';
        $dateToCompare = date("Y-m-d H:i:s");
        // Define the task status
        if ($thread['TAS_ASSIGN_TYPE'] === 'SELF_SERVICE') {
            $status = 'UNASSIGNED';
        }
        if ($thread['APP_STATUS'] === 'DRAFT') {
            $status = 'DRAFT';
        }
        if (isset($thread['DEL_THREAD_STATUS']) && $thread['DEL_THREAD_STATUS'] === 'PAUSED') {
            $status = 'PAUSED';
        }
        if ($thread['APP_STATUS'] === 'COMPLETED') {
            $finishDate = !empty($thread['APP_FINISH_DATE']) ? $thread['APP_FINISH_DATE'] : date("Y-m-d H:i:s");
            $dateToCompare = $finishDate;
        }
        // Variables of results
        $threadTask = [];
        $threadUser = [];
        $threadTitle = [];
        // Define the thread information
        $threadTask['tas_uid'] = !empty($thread['TAS_UID']) ? $thread['TAS_UID'] : '';
        $threadTask['tas_title'] = $thread['TAS_TITLE'];
        $threadTask['user_id'] = $thread['USR_ID'];
        $threadTask['due_date'] = $thread['DEL_TASK_DUE_DATE'];
        $threadTask['delay'] = getDiffBetweenDates($thread['DEL_TASK_DUE_DATE'], $dateToCompare);
        $threadTask['tas_color'] = (!empty($thread['DEL_TASK_DUE_DATE'])) ? $this->getTaskColor($thread['DEL_TASK_DUE_DATE'], $status, $finishDate) : '';
        $threadTask['tas_color_label'] = (!empty($threadTask['tas_color'])) ? self::TASK_COLORS[$threadTask['tas_color']] : '';
        $threadTask['tas_status'] = self::TASK_STATUS[$threadTask['tas_color']];
        $threadTask['unassigned'] = ($status === 'UNASSIGNED' ? true : false);
        $userInfo = User::getInformation($thread['USR_ID']);
        $threadTask['user_tooltip'] = $userInfo;
        // Get user information
        if ($addUserInfo) {
            $threadUser['user_tooltip'] = $userInfo;
            $threadUser['user_id'] = $thread['USR_ID'];
            $threadUser['usr_username'] = !empty($userInfo['usr_username']) ? $userInfo['usr_username'] : '';
            $threadUser['usr_lastname'] = !empty($userInfo['usr_lastname']) ? $userInfo['usr_lastname'] : '';
            $threadUser['usr_firstname'] = !empty($userInfo['usr_firstname']) ? $userInfo['usr_firstname'] : '';
        }
        // Get thread titles
        if ($addThreadInfo) {
            $threadTitle['del_id'] = $thread['DELEGATION_ID'];
            $threadTitle['del_index'] = $thread['DEL_INDEX'];
            $threadTitle['thread_title'] = $thread['DEL_TITLE'];
        }
        // Define the array responses
        $result = [];
        $result['THREAD_TASK'] = $threadTask;
        $result['THREAD_USER'] = $threadUser;
        $result['THREAD_TITLE'] = $threadTitle;

        if (!$addUserInfo && !$addThreadInfo) {
            // Only will return the pending task info
            return $threadTask;
        } else {
            return $result;
        }
    }

    /**
     * Set all properties
     *
     * @param array $properties
     */
    public function setProperties(array $properties)
    {
        // Filter by category
        if (!empty($properties['category'])) {
            $this->setCategoryId($properties['category']);
        }
        // Filter by process
        if (!empty($properties['process'])) {
            $this->setProcessId($properties['process']);
        }
        // Filter by task
        if (!empty($properties['task'])) {
            $this->setTaskId($properties['task']);
        }
        // Filter by user
        if (!empty($properties['user'])) {
            $this->setUserId($properties['user']);
        }
        // Filter by one case number
        if (!empty($properties['caseNumber'])) {
            $this->setCaseNumber($properties['caseNumber']);
        }
        // Add a filter with specific cases or range of cases like '1, 3-5, 8, 10-15'
        if (!empty($properties['filterCases'])) {
            $this->setFilterCases($properties['filterCases']);
        }
        // Filter by case title
        if (!empty($properties['caseTitle'])) {
            $this->setCaseTitle($properties['caseTitle']);
        }
        // Filter by case uid
        if (!empty($properties['caseLink'])) {
            $this->setCaseUid($properties['caseLink']);
        }
        // Filter by array of case uids
        if (!empty($properties['appUidCheck'])) {
            $this->setCasesUids($properties['appUidCheck']);
        }
        // Sort column
        if (!empty($properties['sort'])) {
            $this->setOrderByColumn($properties['sort']);
        }
        // Direction column
        if (!empty($properties['dir'])) {
            $this->setOrderDirection($properties['dir']);
        }
        // Paged
        if (!empty($properties['paged'])) {
            $this->setPaged($properties['paged']);
        }
        // Start
        if (!empty($properties['start'])) {
            $this->setOffset($properties['start']);
        }
        // Limit
        if (!empty($properties['limit'])) {
            $this->setLimit($properties['limit']);
        }
        /** Apply filters related to INBOX */
        // Filter date related to delegate from
        if (get_class($this) === Inbox::class && !empty($properties['delegateFrom'])) {
            $this->setDelegateFrom($properties['delegateFrom']);
        }
        // Filter date related to delegate to
        if (get_class($this) === Inbox::class && !empty($properties['delegateTo'])) {
            $this->setDelegateTo($properties['delegateTo']);
        }
        // Filter by Send By
        if (get_class($this) === Inbox::class && !empty($properties['sendBy'])) {
            $this->setSendBy($properties['sendBy']);
        }
        // Filter by Review Status
        if (get_class($this) === Inbox::class && !empty($properties['reviewStatus'])) {
            $this->setReviewStatus($properties['reviewStatus']);
        }
        /** Apply filters related to PAUSED */
        // Filter date related to delegate from
        if (get_class($this) === Paused::class && !empty($properties['delegateFrom'])) {
            $this->setDelegateFrom($properties['delegateFrom']);
        }
        // Filter date related to delegate to
        if (get_class($this) === Paused::class && !empty($properties['delegateTo'])) {
            $this->setDelegateTo($properties['delegateTo']);
        }
        // Filter by Send By
        if (get_class($this) === Paused::class && !empty($properties['sendBy'])) {
            $this->setSendBy($properties['sendBy']);
        }
        // Filter by Review Status
        if (get_class($this) === Paused::class && !empty($properties['reviewStatus'])) {
            $this->setReviewStatus($properties['reviewStatus']);
        }
        /** Apply filters related to UNASSIGNED */
        // Filter date related to delegate from
        if (get_class($this) === Unassigned::class && !empty($properties['delegateFrom'])) {
            $this->setDelegateFrom($properties['delegateFrom']);
        }
        // Filter date related to delegate to
        if (get_class($this) === Unassigned::class && !empty($properties['delegateTo'])) {
            $this->setDelegateTo($properties['delegateTo']);
        }
        // Filter by Send By
        if (get_class($this) === Unassigned::class && !empty($properties['sendBy'])) {
            $this->setSendBy($properties['sendBy']);
        }
        // Filter by Review Status
        if (get_class($this) === Unassigned::class && !empty($properties['reviewStatus'])) {
            $this->setReviewStatus($properties['reviewStatus']);
        }

        /** Apply filters related to MY CASES */
        // My cases filter: started, in-progress, completed, supervising
        if (get_class($this) === Participated::class && !empty($properties['filter'])) {
            $this->setParticipatedStatus($properties['filter']);
        }
        // Filter by one case status
        if (get_class($this) === Participated::class && !empty($properties['caseStatus'])) {
            $this->setCaseStatus($properties['caseStatus']);
        }
        // Filter date related to started date from
        if ((get_class($this) === Participated::class || get_class($this) === Supervising::class) && !empty($properties['startCaseFrom'])) {
            $this->setStartCaseFrom($properties['startCaseFrom']);
        }
        // Filter date related to started date to
        if ((get_class($this) === Participated::class || get_class($this) === Supervising::class) && !empty($properties['startCaseTo'])) {
            $this->setStartCaseTo($properties['startCaseTo']);
        }
        // Filter date related to finish date from
        if ((get_class($this) === Participated::class || get_class($this) === Supervising::class) && !empty($properties['finishCaseFrom'])) {
            $this->setFinishCaseFrom($properties['finishCaseFrom']);
        }
        //  Filter date related to finish date to
        if ((get_class($this) === Participated::class || get_class($this) === Supervising::class) && !empty($properties['finishCaseTo'])) {
            $this->setFinishCaseTo($properties['finishCaseTo']);
        }
        /** Apply filters related to SEARCH */
        // Filter by category
        if (get_class($this) === Search::class && !empty($properties['category'])) {
            $this->setCategoryId($properties['category']);
        }
        // Filter by more than one case statuses like ['DRAFT', 'TO_DO']
        if (get_class($this) === Search::class && !empty($properties['caseStatuses'])) {
            $this->setCaseStatuses($properties['caseStatuses']);
        }
        // Filter date related to started date from
        if (get_class($this) === Search::class && !empty($properties['startCaseFrom'])) {
            $this->setStartCaseFrom($properties['startCaseFrom']);
        }
        // Filter date related to started date to
        if (get_class($this) === Search::class && !empty($properties['startCaseTo'])) {
            $this->setStartCaseTo($properties['startCaseTo']);
        }
        // Filter date related to finish date from
        if (get_class($this) === Search::class && !empty($properties['finishCaseFrom'])) {
            $this->setFinishCaseFrom($properties['finishCaseFrom']);
        }
        // Filter date related to finish date to
        if (get_class($this) === Search::class && !empty($properties['finishCaseTo'])) {
            $this->setFinishCaseTo($properties['finishCaseTo']);
        }
        // Filter date related to user who started
        if (get_class($this) === Search::class && !empty($properties['userCompleted'])) {
            $this->setUserCompletedId($properties['userCompleted']);
        }
        // Filter date related to user who completed
        if (get_class($this) === Search::class && !empty($properties['userStarted'])) {
            $this->setUserStartedId($properties['userStarted']);
        }
    }

    /**
     * Get the list data
     *
     * @throws Exception
     */
    public function getData()
    {
        throw new Exception("Method '" . __FUNCTION__ . "' should be implemented in the extended class '" . get_class($this) . "'.");
    }

    /**
     * Get the list counter
     *
     * @throws Exception
     */
    public function getCounter()
    {
        throw new Exception("Method '" . __FUNCTION__ . "' should be implemented in the extended class '" . get_class($this) . "'.");
    }

    /**
     * Get true if the user has at least one case
     *
     * @throws Exception
     */
    public function atLeastOne()
    {
        throw new Exception("Method '" . __FUNCTION__ . "' should be implemented in the extended class '" . get_class($this) . "'.");
    }

    /**
     * Get the list counter
     *
     * @throws Exception
     */
    public function getPagingCounters()
    {
        throw new Exception("Method '" . __FUNCTION__ . "' should be implemented in the extended class '" . get_class($this) . "'.");
    }

    /**
     * Count how many cases has each process
     *
     * @param int $category
     * @param bool $topTen
     * @param array $processes
     * 
     * @return array
     */
    public function getCountersByProcesses($category = null, $topTen = false, $processes = [])
    {
        $query = Delegation::selectRaw('count(APP_DELEGATION.DELEGATION_ID) as TOTAL, APP_DELEGATION.PRO_ID, PROCESS.PRO_TITLE')
            ->groupBy('APP_DELEGATION.PRO_UID');
        $listArray = explode("\\", get_class($this));
        $list = end($listArray);
        switch ($list) {
            case 'Inbox':
                $query->inboxMetrics();
                break;
            case 'Draft':
                $query->draftMetrics();
                break;
            case 'Paused':
                $query->pausedMetrics();
                break;
            case 'Unassigned':
                $query->selfServiceMetrics();
                break;
        }
        $query->joinProcess();
        if (!is_null($category)) {
            $query->categoryId($category);
        }
        if ($topTen) {
            $query->topTen('TOTAL', 'DESC');
        }
        if (!empty($processes)) {
            $query->processInList($processes);
        }
        return $query->get()->values()->toArray();
    }

    /**
     * Count how many cases has each process by range of dates
     * 
     * @param int $processId
     * @param string $dateFrom
     * @param string $dateTo
     * @param string $groupBy
     * 
     * @return array
     */
    public function getCountersByRange($processId = null, $dateFrom = null, $dateTo = null, $groupBy = 'day')
    {
        $rawQuery = 'count(APP_DELEGATION.DELEGATION_ID) as TOTAL, APP_DELEGATION.PRO_ID, PROCESS.PRO_TITLE, DATE(APP_DELEGATION.DEL_DELEGATE_DATE) as dateGroup';
        switch ($groupBy) {
            case 'month':
                $rawQuery = 'count(APP_DELEGATION.DELEGATION_ID) as TOTAL, APP_DELEGATION.PRO_ID, PROCESS.PRO_TITLE, EXTRACT(YEAR_MONTH From APP_DELEGATION.DEL_DELEGATE_DATE) as dateGroup';
                break;
            case 'year':
                $rawQuery = 'count(APP_DELEGATION.DELEGATION_ID) as TOTAL, APP_DELEGATION.PRO_ID, PROCESS.PRO_TITLE, YEAR(APP_DELEGATION.DEL_DELEGATE_DATE) as dateGroup';
                break;
        }
        $query = Delegation::selectRaw($rawQuery);
        $query->groupBy('dateGroup');
        $listArray = explode("\\", get_class($this));
        $list = end($listArray);
        switch ($list) {
            case 'Inbox':
                $query->inboxMetrics();
                break;
            case 'Draft':
                $query->draftMetrics();
                break;
            case 'Paused':
                $query->pausedMetrics();
                break;
            case 'Unassigned':
                $query->selfServiceMetrics();
                break;
        }
        $query->joinProcess();
        if (!is_null($processId)) {
            $query->processInList([$processId]);
        }
        if (!is_null($dateFrom)) {
            $query->where('APP_DELEGATION.DEL_DELEGATE_DATE', '>=', $dateFrom . ' 00:00:00');
        }
        if (!is_null($dateTo)) {
            $query->where('APP_DELEGATION.DEL_DELEGATE_DATE', '<=', $dateTo . ' 23:59:59');
        }
        return $query->get()->values()->toArray();
    }

    /**
     * Get cases risk by process
     * 
     * @param int $processId
     * @param string $dateFrom
     * @param string $dateTo
     * @param string $riskStatus
     * @param int $topCases
     * 
     * @return array
     */
    public function getCasesRisk($processId = '', $dateFrom = null, $dateTo = null, $riskStatus = 'ON_TIME', $topCases = null)
    {
        $date = new DateTime('now');
        $currentDate = $date->format('Y-m-d H:i:s');
        $query = Delegation::selectRaw('
            APP_DELEGATION.APP_NUMBER as number_case,
            APP_DELEGATION.DEL_DELEGATE_DATE as delegated,
            APP_DELEGATION.DEL_RISK_DATE as at_risk,
            APP_DELEGATION.DEL_TASK_DUE_DATE as due_date,
            APP_DELEGATION.APP_UID as app_uid,
            APP_DELEGATION.DEL_INDEX as del_index,
            APP_DELEGATION.TAS_UID as tas_uid
        ');
        $listArray = explode("\\", get_class($this));
        $list = end($listArray);
        switch ($list) {
            case 'Inbox':
                $query->inboxMetrics();
                break;
            case 'Draft':
                $query->draftMetrics();
                break;
            case 'Paused':
                $query->pausedMetrics();
                break;
            case 'Unassigned':
                $query->selfServiceMetrics();
                break;
        }
        $query->joinProcess();
        $query->processInList([$processId]);

        if (!is_null($dateFrom)) {
            $query->where('APP_DELEGATION.DEL_DELEGATE_DATE', '>=', $dateFrom);
        }
        if (!is_null($dateTo)) {
            $query->where('APP_DELEGATION.DEL_DELEGATE_DATE', '<=', $dateTo);
        }
        if (!is_null($topCases)) {
            $query->orderBy('APP_DELEGATION.DEL_DELEGATE_DATE', 'ASC')->limit($topCases);
        }
        $value = 'due_date';
        switch ($riskStatus) {
            case 'ON_TIME':
                $query->onTime($currentDate);
                $value = 'at_risk';
                break;
            case 'AT_RISK':
                $query->atRisk($currentDate);
                break;
            case 'OVERDUE':
                $query->overdue($currentDate);
                break;
        }
        $res = $query->get()->values()->toArray();
        foreach ($res as $key => $values) {
            $riskDate = new DateTime($values[$value]);
            $days = ['days' => $date->diff($riskDate)->days];
            $res[$key] = $days + $res[$key];
        }
        return $res;
    }
}
