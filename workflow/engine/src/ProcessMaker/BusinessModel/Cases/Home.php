<?php

namespace ProcessMaker\BusinessModel\Cases;

use ProcessMaker\BusinessModel\Cases\Draft;
use ProcessMaker\BusinessModel\Cases\Inbox;
use ProcessMaker\BusinessModel\Cases\Paused;
use ProcessMaker\BusinessModel\Cases\Unassigned;
use ProcessMaker\Model\CaseList;
use ProcessMaker\Model\User;
use ProcessMaker\Util\DateTime;

class Home
{
    /**
     * This is the userId field.
     * @var string
     */
    private $userId = '';

    /**
     * Constructor of the class.
     * @param type $userId
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get the userId field.
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Get the draft cases.
     * 
     * @param int $caseNumber
     * @param int $category
     * @param int $process
     * @param int $task
     * @param int $limit
     * @param int $offset
     * @param string $caseTitle
     * @param string $filterCases
     * @param string $reviewStatus
     * @param string $sort
     * @param callable $callback
     * 
     * @return array
     */
    public function getDraft(
        int $caseNumber = 0,
        int $category = 0,
        int $process = 0,
        int $task = 0,
        int $limit = 15,
        int $offset = 0,
        string $caseTitle = '',
        string $filterCases = '',
        string $reviewStatus = '',
        string $sort = 'APP_NUMBER,DESC',
        callable $callback = null
    )
    {
        $list = new Draft();
        // Define the filters to apply
        $properties = [];
        $properties['caseNumber'] = $caseNumber;
        $properties['caseTitle'] = $caseTitle;
        $properties['filterCases'] = $filterCases;
        $properties['reviewStatus'] = $reviewStatus;
        $properties['category'] = $category;
        $properties['process'] = $process;
        $properties['task'] = $task;
        // Get the user that access to the API
        $usrUid = $this->getUserId();
        $properties['user'] = !empty($usrUid) ? User::getId($usrUid) : 0;
        $properties['start'] = $offset;
        $properties['limit'] = $limit;
        // Set the sort parameters
        $sort = explode(',', $sort);
        $properties['sort'] = $sort[0];
        $properties['dir'] = $sort[1];
        $list->setProperties($properties);
        $result = [];
        $result['data'] = DateTime::convertUtcToTimeZone($list->getData($callback));
        $result['total'] = $list->getPagingCounters();
        return $result;
    }

    /**
     * Get the inbox cases.
     * 
     * @param int $caseNumber
     * @param int $category
     * @param int $process
     * @param int $task
     * @param int $limit
     * @param int $offset
     * @param string $caseTitle
     * @param string $delegateFrom
     * @param string $delegateTo
     * @param string $filterCases
     * @param string $reviewStatus
     * @param string $sort
     * @param string $sendBy
     * @param callable $callback
     * 
     * @return array
     */
    public function getInbox(
        int $caseNumber = 0,
        int $category = 0,
        int $process = 0,
        int $task = 0,
        int $limit = 15,
        int $offset = 0,
        string $caseTitle = '',
        string $delegateFrom = '',
        string $delegateTo = '',
        string $filterCases = '',
        string $reviewStatus = '',
        string $sort = 'APP_NUMBER,DESC',
        string $sendBy = '',
        callable $callback = null
    )
    {
        $list = new Inbox();
        // Define the filters to apply
        $properties = [];
        $properties['caseNumber'] = $caseNumber;
        $properties['caseTitle'] = $caseTitle;
        $properties['delegateFrom'] = $delegateFrom;
        $properties['delegateTo'] = $delegateTo;
        $properties['filterCases'] = $filterCases;
        $properties['reviewStatus'] = $reviewStatus;
        $properties['category'] = $category;
        $properties['process'] = $process;
        $properties['task'] = $task;
        // Get the user that access to the API
        $usrUid = $this->getUserId();
        $properties['user'] = !empty($usrUid) ? User::getId($usrUid) : 0;
        $properties['start'] = $offset;
        $properties['limit'] = $limit;
        // Set the pagination parameters
        $sort = explode(',', $sort);
        $properties['sort'] = $sort[0];
        $properties['dir'] = $sort[1];
        $properties['sendBy'] = $sendBy;
        $list->setProperties($properties);
        $result = [];
        $result['data'] = DateTime::convertUtcToTimeZone($list->getData($callback));
        $result['total'] = $list->getPagingCounters();
        return $result;
    }

    /**
     * Get the unassigned cases.
     * 
     * @param int $caseNumber
     * @param int $category
     * @param int $process
     * @param int $task
     * @param int $limit
     * @param int $offset
     * @param string $caseTitle
     * @param string $delegateFrom
     * @param string $delegateTo
     * @param string $filterCases
     * @param string $reviewStatus
     * @param string $sort
     * @param string $sendBy
     * @param callable $callback
     * 
     * @return array
     */
    public function getUnassigned(
        int $caseNumber = 0,
        int $category = 0,
        int $process = 0,
        int $task = 0,
        int $limit = 15,
        int $offset = 0,
        string $caseTitle = '',
        string $delegateFrom = '',
        string $delegateTo = '',
        string $filterCases = '',
        string $reviewStatus = '',
        string $sort = 'APP_NUMBER,DESC',
        string $sendBy = '',
        callable $callback = null
    )
    {
        $list = new Unassigned();
        // Define the filters to apply
        $properties = [];
        $properties['caseNumber'] = $caseNumber;
        $properties['caseTitle'] = $caseTitle;
        $properties['delegateFrom'] = $delegateFrom;
        $properties['delegateTo'] = $delegateTo;
        $properties['filterCases'] = $filterCases;
        $properties['reviewStatus'] = $reviewStatus;
        $properties['category'] = $category;
        $properties['process'] = $process;
        $properties['task'] = $task;
        // Get the user that access to the API
        $usrUid = $this->getUserId();
        $properties['user'] = !empty($usrUid) ? User::getId($usrUid) : 0;
        $properties['start'] = $offset;
        $properties['limit'] = $limit;
        // Set the sort parameters
        $sort = explode(',', $sort);
        $properties['sort'] = $sort[0];
        $properties['dir'] = $sort[1];
        $properties['sendBy'] = $sendBy;
        // todo: some queries related to the unassigned are using the USR_UID
        $list->setUserUid($usrUid);
        $list->setProperties($properties);
        $result = [];
        $result['data'] = DateTime::convertUtcToTimeZone($list->getData($callback));
        $result['total'] = $list->getPagingCounters();
        return $result;
    }

    /**
     * Get the paused cases.
     * 
     * @param int $caseNumber
     * @param int $category
     * @param int $process
     * @param int $task
     * @param int $limit
     * @param int $offset
     * @param string $caseTitle
     * @param string $delegateFrom
     * @param string $delegateTo
     * @param string $filterCases
     * @param string $reviewStatus
     * @param string $sort
     * @param string $sendBy
     * @param callable $callback
     * 
     * @return array
     */
    public function getPaused(
        int $caseNumber = 0,
        int $category = 0,
        int $process = 0,
        int $task = 0,
        int $limit = 15,
        int $offset = 0,
        string $caseTitle = '',
        string $delegateFrom = '',
        string $delegateTo = '',
        string $filterCases = '',
        string $reviewStatus = '',
        string $sort = 'APP_NUMBER,DESC',
        string $sendBy = '',
        callable $callback = null
    )
    {
        $list = new Paused();
        // Define the filters to apply
        $properties = [];
        $properties['caseNumber'] = $caseNumber;
        $properties['caseTitle'] = $caseTitle;
        $properties['delegateFrom'] = $delegateFrom;
        $properties['delegateTo'] = $delegateTo;
        $properties['filterCases'] = $filterCases;
        $properties['reviewStatus'] = $reviewStatus;
        $properties['category'] = $category;
        $properties['process'] = $process;
        $properties['task'] = $task;
        // Get the user that access to the API
        $usrUid = $this->getUserId();
        $properties['user'] = !empty($usrUid) ? User::getId($usrUid) : 0;
        $properties['start'] = $offset;
        $properties['limit'] = $limit;
        // Set the sort parameters
        $sort = explode(',', $sort);
        $properties['sort'] = $sort[0];
        $properties['dir'] = $sort[1];
        $properties['sendBy'] = $sendBy;
        $list->setProperties($properties);
        $result = [];
        $result['data'] = DateTime::convertUtcToTimeZone($list->getData($callback));
        $result['total'] = $list->getPagingCounters();
        return $result;
    }

    /**
     * Build the columns and data from the custom list.
     * 
     * @param string $type
     * @param int $id
     * @param array $arguments
     * @param array $defaultColumns
     * @param array $customFilters
     */
    public function buildCustomCaseList(string $type, int $id, array &$arguments, array &$defaultColumns, array $customFilters = [])
    {
        $caseList = CaseList::where('CAL_TYPE', '=', $type)
            ->where('CAL_ID', '=', $id)
            ->join('ADDITIONAL_TABLES', 'ADDITIONAL_TABLES.ADD_TAB_UID', '=', 'CASE_LIST.ADD_TAB_UID')
            ->join('PROCESS', 'PROCESS.PRO_UID', '=', 'ADDITIONAL_TABLES.PRO_UID')
            ->first();
        if (!empty($caseList)) {
            $tableName = $caseList->ADD_TAB_NAME;
            $proUid = $caseList->PRO_UID;
            $proId = $caseList->PRO_ID;

            //this gets the configured columns
            $columns = json_decode($caseList->CAL_COLUMNS);
            $columns = CaseList::formattingColumns($type, $caseList->ADD_TAB_UID, $columns);

            //this gets the visible columns from the custom List and the fields from the table
            if (!empty($columns)) {
                $defaultColumns = [];
            }
            $fields = [];
            $types = [];
            foreach ($columns as $value) {
                if ($value['set'] === true) {
                    $defaultColumns[] = $value;
                    if ($value['source'] === $tableName) {
                        $fields[] = $value['field'];
                        $types[$value['field']] = $value['type'];
                    }
                }
            }

            //this modifies the query
            if (!empty($tableName)) {
                $arguments[] = function ($query) use ($tableName, $fields, $customFilters, $types, $proUid) {
                    //setting the related process
                    $query->where('PROCESS.PRO_UID', '=', $proUid);

                    //setting columns data from report table
                    $query->leftJoin($tableName, "{$tableName}.APP_UID", "=", "APP_DELEGATION.APP_UID");
                    foreach ($fields as $value) {
                        $query->addSelect($value);
                    }

                    //setting filters for custom case list
                    foreach ($customFilters as $key => $filter) {
                        if (in_array($key, $fields)) {
                            //special case for date range
                            if (isset($types[$key]) && ($types[$key] === "DATETIME" || $types[$key] === "DATE")) {
                                if (strpos($customFilters[$key], ",") !== false) {
                                    $explode = explode(",", $customFilters[$key]);
                                    $dateFrom = $explode[0];
                                    $dateTo = $explode[1];
                                    $query->whereBetween($key, [$dateFrom, $dateTo]);
                                    if (is_null($filter) || $filter === "") {
                                        $subquery->orWhereNull($key);
                                    }
                                    continue;
                                }
                            }
                            //normal filter
                            $subquery = $query->where($key, 'like', "%{$filter}%");
                            if (is_null($filter) || $filter === "") {
                                $subquery->orWhereNull($key);
                            }
                        }
                    }
                };
            }
            $arguments[2] = $proId;
        }
    }

    /**
     * Get the custom draft cases.
     * 
     * @param int $id
     * @param int $caseNumber
     * @param int $category
     * @param int $process
     * @param int $task
     * @param int $limit
     * @param int $offset
     * @param string $caseTitle
     * @param string $filterCases
     * @param string $reviewStatus
     * @param string $sort
     * @param array $customFilters
     * 
     * @return array
     */
    public function getCustomDraft(
        int $id,
        int $caseNumber = 0,
        int $category = 0,
        int $process = 0,
        int $task = 0,
        int $limit = 15,
        int $offset = 0,
        string $caseTitle = '',
        string $filterCases = '',
        string $reviewStatus = '',
        string $sort = 'APP_NUMBER,DESC',
        array $customFilters = []
    )
    {
        $arguments = [
            $caseNumber,
            $category,
            $process,
            $task,
            $limit,
            $offset,
            $caseTitle,
            $filterCases,
            $reviewStatus,
            $sort
        ];

        //clear duplicate indexes
        $keys = ['caseNumber', 'category', 'process', 'task', 'limit', 'offset', 'caseTitle', 'filterCases', 'reviewStatus', 'sort'];
        foreach ($keys as $value) {
            unset($customFilters[$value]);
        }

        $type = 'draft';
        $defaultColumns = CaseList::formattingColumns($type, '', []);
        $this->buildCustomCaseList($type, $id, $arguments, $defaultColumns, $customFilters);

        $result = $this->getDraft(...$arguments);
        $result['columns'] = $defaultColumns;
        return $result;
    }

    /**
     * Get the custom inbox cases.
     * 
     * @param int $id
     * @param int $caseNumber
     * @param int $category
     * @param int $process
     * @param int $task
     * @param int $limit
     * @param int $offset
     * @param string $caseTitle
     * @param string $delegateFrom
     * @param string $delegateTo
     * @param string $filterCases
     * @param string $reviewStatus
     * @param string $sort
     * @param string $sendBy
     * @param array $customFilters
     * 
     * @return array
     */
    public function getCustomInbox(
        int $id,
        int $caseNumber = 0,
        int $category = 0,
        int $process = 0,
        int $task = 0,
        int $limit = 15,
        int $offset = 0,
        string $caseTitle = '',
        string $delegateFrom = '',
        string $delegateTo = '',
        string $filterCases = '',
        string $reviewStatus = '',
        string $sort = 'APP_NUMBER,DESC',
        string $sendBy = '',
        array $customFilters = []
    )
    {
        $arguments = [
            $caseNumber,
            $category,
            $process,
            $task,
            $limit,
            $offset,
            $caseTitle,
            $delegateFrom,
            $delegateTo,
            $filterCases,
            $reviewStatus,
            $sort,
            $sendBy
        ];

        //clear duplicate indexes
        $keys = ['caseNumber', 'category', 'process', 'task', 'limit', 'offset', 'caseTitle', 'delegateFrom', 'delegateTo', 'filterCases', 'reviewStatus', 'sort', 'sendBy'];
        foreach ($keys as $value) {
            unset($customFilters[$value]);
        }

        $type = 'inbox';
        $defaultColumns = CaseList::formattingColumns($type, '', []);
        $this->buildCustomCaseList($type, $id, $arguments, $defaultColumns, $customFilters);

        $result = $this->getInbox(...$arguments);
        $result['columns'] = $defaultColumns;
        return $result;
    }

    /**
     * Get the custom unassigned cases.
     * 
     * @param int $id
     * @param int $caseNumber
     * @param int $category
     * @param int $process
     * @param int $task
     * @param int $limit
     * @param int $offset
     * @param string $caseTitle
     * @param string $delegateFrom
     * @param string $delegateTo
     * @param string $filterCases
     * @param string $reviewStatus
     * @param string $sort
     * @param string $sendBy
     * @param array $customFilters
     * 
     * @return array
     */
    public function getCustomUnassigned(
        int $id,
        int $caseNumber = 0,
        int $category = 0,
        int $process = 0,
        int $task = 0,
        int $limit = 15,
        int $offset = 0,
        string $caseTitle = '',
        string $delegateFrom = '',
        string $delegateTo = '',
        string $filterCases = '',
        string $reviewStatus = '',
        string $sort = 'APP_NUMBER,DESC',
        string $sendBy = '',
        array $customFilters = []
    )
    {
        $arguments = [
            $caseNumber,
            $category,
            $process,
            $task,
            $limit,
            $offset,
            $caseTitle,
            $delegateFrom,
            $delegateTo,
            $filterCases,
            $reviewStatus,
            $sort,
            $sendBy
        ];

        //clear duplicate indexes
        $keys = ['caseNumber', 'category', 'process', 'task', 'limit', 'offset', 'caseTitle', 'delegateFrom', 'delegateTo', 'filterCases', 'reviewStatus', 'sort', 'sendBy'];
        foreach ($keys as $value) {
            unset($customFilters[$value]);
        }

        $type = 'unassigned';
        $defaultColumns = CaseList::formattingColumns($type, '', []);
        $this->buildCustomCaseList($type, $id, $arguments, $defaultColumns, $customFilters);

        $result = $this->getUnassigned(...$arguments);
        $result['columns'] = $defaultColumns;
        return $result;
    }

    /**
     * Get the custom paused cases.
     * 
     * @param int $id
     * @param int $caseNumber
     * @param int $category
     * @param int $process
     * @param int $task
     * @param int $limit
     * @param int $offset
     * @param string $caseTitle
     * @param string $delegateFrom
     * @param string $delegateTo
     * @param string $filterCases
     * @param string $reviewStatus
     * @param string $sort
     * @param string $sendBy
     * @param array $customFilters
     * 
     * @return array
     */
    public function getCustomPaused(
        int $id,
        int $caseNumber = 0,
        int $category = 0,
        int $process = 0,
        int $task = 0,
        int $limit = 15,
        int $offset = 0,
        string $caseTitle = '',
        string $delegateFrom = '',
        string $delegateTo = '',
        string $filterCases = '',
        string $reviewStatus = '',
        string $sort = 'APP_NUMBER,DESC',
        string $sendBy = '',
        array $customFilters = []
    )
    {
        $arguments = [
            $caseNumber,
            $category,
            $process,
            $task,
            $limit,
            $offset,
            $caseTitle,
            $delegateFrom,
            $delegateTo,
            $filterCases,
            $reviewStatus,
            $sort,
            $sendBy
        ];

        //clear duplicate indexes
        $keys = ['caseNumber', 'category', 'process', 'task', 'limit', 'offset', 'caseTitle', 'delegateFrom', 'delegateTo', 'filterCases', 'reviewStatus', 'sort', 'sendBy'];
        foreach ($keys as $value) {
            unset($customFilters[$value]);
        }

        $type = 'paused';
        $defaultColumns = CaseList::formattingColumns($type, '', []);
        $this->buildCustomCaseList($type, $id, $arguments, $defaultColumns, $customFilters);

        $result = $this->getPaused(...$arguments);
        $result['columns'] = $defaultColumns;
        return $result;
    }
}
