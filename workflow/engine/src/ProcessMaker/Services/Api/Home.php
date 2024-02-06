<?php

namespace ProcessMaker\Services\Api;

use Exception;
use G;
use Luracast\Restler\RestException;
use Menu;
use ProcessMaker\BusinessModel\Cases\CasesList;
use ProcessMaker\BusinessModel\Cases\Draft;
use ProcessMaker\BusinessModel\Cases\Filter;
use ProcessMaker\BusinessModel\Cases\Home as BMHome;
use ProcessMaker\BusinessModel\Cases\Inbox;
use ProcessMaker\BusinessModel\Cases\Participated;
use ProcessMaker\BusinessModel\Cases\Paused;
use ProcessMaker\BusinessModel\Cases\Search;
use ProcessMaker\BusinessModel\Cases\Supervising;
use ProcessMaker\BusinessModel\Cases\Unassigned;
use ProcessMaker\Model\CaseList;
use ProcessMaker\Model\Delegation;
use ProcessMaker\Model\Process;
use ProcessMaker\Model\ProcessCategory;
use ProcessMaker\Model\User;
use ProcessMaker\Model\UserConfig;
use ProcessMaker\Model\Task;
use ProcessMaker\Services\Api;
use ProcessMaker\Util\DateTime;
use RBAC;
use stdClass;

class Home extends Api
{
    /**
     * Constructor of the class
     * We will to define the $RBAC definition
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
     * Get the draft cases
     *
     * @url GET /draft
     *
     * @param int $caseNumber
     * @param int $category
     * @param int $process
     * @param int $task
     * @param int $limit
     * @param int $offset
     * @param string $caseTitle
     * @param string $filterCases
     * @param string $filterCases
     * @param string $sort
     *
     * @return array
     *
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doGetDraftCases(
        int $caseNumber = 0,
        int $category = 0,
        int $process = 0,
        int $task = 0,
        int $limit = 15,
        int $offset = 0,
        string $caseTitle = '',
        string $filterCases = '',
        string $reviewStatus = '',
        string $sort = 'APP_NUMBER,DESC'
    )
    {
        try {
            $bmHome = new BMHome($this->getUserId());
            return $bmHome->getDraft(
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
            );
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get the inbox cases
     *
     * @url GET /inbox
     * @url GET /todo [This is kept for compatibility should not be used 'todo', the reason is to only handle the same verb (inbox) for all 'normal case list' and 'custom case list']
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
     *
     * @return array
     *
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doGetTodoCases(
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
        string $sendBy = ''
    )
    {
        try {
            $bmHome = new BMHome($this->getUserId());
            return $bmHome->getInbox(
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
            );
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get the unassigned cases
     *
     * @url GET /unassigned
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
     *
     * @return array
     *
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doGetUnassignedCases(
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
        string $sendBy = ''
    )
    {
        try {
            $bmHome = new BMHome($this->getUserId());
            return $bmHome->getUnassigned(
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
            );
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get the paused cases
     *
     * @url GET /paused
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
     *
     * @return array
     *
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doGetPausedCases(
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
        string $sendBy = ''
    )
    {
        try {
            $bmHome = new BMHome($this->getUserId());
            return $bmHome->getPaused(
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
            );
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get the custom draft cases.
     * @url POST /draft/:id
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
     * @param array $request_data
     * @return array
     * @throws RestException
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doGetCustomDraftCases(
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
        array $request_data = []
    )
    {
        try {
            $bmHome = new BMHome($this->getUserId());
            return $bmHome->getCustomDraft(
                    $id,
                    $caseNumber,
                    $category,
                    $process,
                    $task,
                    $limit,
                    $offset,
                    $caseTitle,
                    $filterCases,
                    $reviewStatus,
                    $sort,
                    $request_data
            );
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get the custom inbox cases.
     * @url POST /inbox/:id
     * @param int $id
     * @param int $caseNumber
     * @param int $category
     * @param int $process
     * @param int $task
     * @param int $sendBy
     * @param int $limit
     * @param int $offset
     * @param string $caseTitle
     * @param string $delegateFrom
     * @param string $delegateTo
     * @param string $filterCases
     * @param string $reviewStatus
     * @param string $sort
     * @param array $request_data
     * @return array
     * @throws RestException
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doGetCustomInboxCases(
        int $id,
        int $caseNumber = 0,
        int $category = 0,
        int $process = 0,
        int $task = 0,
        int $sendBy = 0,
        int $limit = 15,
        int $offset = 0,
        string $caseTitle = '',
        string $delegateFrom = '',
        string $delegateTo = '',
        string $filterCases = '',
        string $reviewStatus = '',
        string $sort = 'APP_NUMBER,DESC',
        array $request_data = []
    )
    {
        try {
            $bmHome = new BMHome($this->getUserId());
            return $bmHome->getCustomInbox(
                    $id,
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
                    $sendBy,
                    $request_data
            );
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get the custom unassigned cases.
     * @url POST /unassigned/:id
     * @param int $id
     * @param int $caseNumber
     * @param int $category
     * @param int $process
     * @param int $task
     * @param int $sendBy
     * @param int $limit
     * @param int $offset
     * @param string $caseTitle
     * @param string $delegateFrom
     * @param string $delegateTo
     * @param string $filterCases
     * @param string $reviewStatus
     * @param string $sort
     * @param array $request_data
     * @return array
     * @throws RestException
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doGetCustomUnassignedCases(
        int $id,
        int $caseNumber = 0,
        int $category = 0,
        int $process = 0,
        int $task = 0,
        int $sendBy = 0,
        int $limit = 15,
        int $offset = 0,
        string $caseTitle = '',
        string $delegateFrom = '',
        string $delegateTo = '',
        string $filterCases = '',
        string $reviewStatus = '',
        string $sort = 'APP_NUMBER,DESC',
        array $request_data = []
    )
    {
        try {
            $bmHome = new BMHome($this->getUserId());
            return $bmHome->getCustomUnassigned(
                    $id,
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
                    $sendBy,
                    $request_data
            );
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get the custom paused cases.
     * @url POST /paused/:id
     * @param int $id
     * @param int $caseNumber
     * @param int $category
     * @param int $process
     * @param int $task
     * @param int $sendBy
     * @param int $limit
     * @param int $offset
     * @param string $caseTitle
     * @param string $delegateFrom
     * @param string $delegateTo
     * @param string $filterCases
     * @param string $reviewStatus
     * @param string $sort
     * @param array $request_data
     * @return array
     * @throws RestException
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doGetCustomPausedCases(
        int $id,
        int $caseNumber = 0,
        int $category = 0,
        int $process = 0,
        int $task = 0,
        int $sendBy = 0,
        int $limit = 15,
        int $offset = 0,
        string $caseTitle = '',
        string $delegateFrom = '',
        string $delegateTo = '',
        string $filterCases = '',
        string $reviewStatus = '',
        string $sort = 'APP_NUMBER,DESC',
        array $request_data = []
    )
    {
        try {
            $bmHome = new BMHome($this->getUserId());
            return $bmHome->getCustomPaused(
                    $id,
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
                    $sendBy,
                    $request_data
            );
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get the my cases
     *
     * @url GET /mycases
     *
     * @param int $caseNumber
     * @param int $category
     * @param int $process
     * @param int $task
     * @param int $limit
     * @param int $offset
     * @param string $caseTitle
     * @param string $filterCases
     * @param string $filter
     * @param string $caseStatus
     * @param string $startCaseFrom
     * @param string $startCaseTo
     * @param string $finishCaseFrom
     * @param string $finishCaseTo
     * @param string $sort
     *
     * @return array
     *
     * @throws Exception
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doGetMyCases(
        int $caseNumber = 0,
        int $category = 0,
        int $process = 0,
        int $task = 0,
        int $limit = 15,
        int $offset = 0,
        string $caseTitle = '',
        string $filterCases = '',
        string $filter = 'IN_PROGRESS',
        string $caseStatus = '',
        string $startCaseFrom = '',
        string $startCaseTo = '',
        string $finishCaseFrom = '',
        string $finishCaseTo = '',
        string $sort = 'APP_NUMBER,DESC'
    ) {
        // Define the filters to apply
        $properties = [];
        $properties['caseNumber'] = $caseNumber;
        $properties['caseTitle'] = $caseTitle;
        $properties['filterCases'] = $filterCases;
        $properties['category'] = $category;
        $properties['process'] = $process;
        $properties['task'] = $task;
        // Get the user that access to the API
        $usrUid = $this->getUserId();
        $properties['user'] = !empty($usrUid) ? User::getId($usrUid) : 0;
        $properties['filter'] = $filter;
        $properties['caseStatus'] = $caseStatus;
        $properties['startCaseFrom'] = $startCaseFrom;
        $properties['startCaseTo'] = $startCaseTo;
        $properties['finishCaseFrom'] = $finishCaseFrom;
        $properties['finishCaseTo'] = $finishCaseTo;
        $properties['start'] = $offset;
        $properties['limit'] = $limit;
        // Set the sort parameters
        $sort = explode(',', $sort);
        $properties['sort'] = $sort[0];
        $properties['dir'] = $sort[1];
        $result = [];
        try {
            if (!empty($filter)) {
                switch ($filter) {
                    case 'STARTED':
                    case 'IN_PROGRESS':
                    case 'COMPLETED':
                        $list = new Participated();
                        // todo: some queries related to the PROCESS_USER are using the USR_UID
                        $list->setUserUid($usrUid);
                        $list->setParticipatedStatus($filter);
                        $list->setProperties($properties);
                        $result['data'] = DateTime::convertUtcToTimeZone($list->getData());
                        $result['total'] = $list->getPagingCounters();
                        break;
                    case 'SUPERVISING':
                        // Scope that search for the SUPERVISING cases by specific user
                        $list = new Supervising();
                        // todo: some queries related to the PROCESS_USER are using the USR_UID
                        $list->setUserUid($usrUid);
                        $list->setProperties($properties);
                        $result['data'] = DateTime::convertUtcToTimeZone($list->getData());
                        $result['total'] = $list->getPagingCounters();
                        break;
                }
            }

            return $result;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get counters
     *
     * @url GET /counters
     * @url GET /mycases/counters
     *
     * @return array
     *
     * @throws Exception
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doGetCountMyCases()
    {
        try {
            // Initializing variables
            $participatedStatuses = ['STARTED', 'IN_PROGRESS', 'COMPLETED', 'SUPERVISING'];
            $participatedLabels = array_combine($participatedStatuses, ['ID_OPT_STARTED', 'ID_IN_PROGRESS', 'ID_COMPLETED', 'ID_SUPERVISING']);
            $counters = [];
            // Get the user that access to the API
            $usrUid = $this->getUserId();
            // Get counters
            foreach ($participatedStatuses as $participatedStatus) {
                // Initializing counter object
                $counter = new stdClass();
                $counter->id = $participatedStatus;
                $counter->title = G::LoadTranslation($participatedLabels[$participatedStatus]);

                // Get counter value according to the participated status
                switch ($participatedStatus) {
                    case 'STARTED':
                    case 'IN_PROGRESS':
                    case 'COMPLETED':
                        $participated = new Participated();
                        $participated->setParticipatedStatus($participatedStatus);
                        $usrId = !empty($usrUid) ? User::getId($usrUid) : 0;
                        $participated->setUserId($usrId);
                        // todo: some queries related to the PROCESS_USER are using the USR_UID
                        $participated->setUserUid($usrUid);
                        $counter->counter = $participated->getCounter();
                        break;
                    case 'SUPERVISING':
                        $supervising = new Supervising();
                        $usrId = !empty($usrUid) ? User::getId($usrUid) : 0;
                        $supervising->setUserId($usrId);
                        // todo: some queries related to the PROCESS_USER are using the USR_UID
                        $supervising->setUserUid($usrUid);
                        $counter->counter = $supervising->getCounter();
                        break;
                    default:
                        $counter->counter = 0;
                }
                // Add counter
                $counters[] = $counter;
            }

            // Return counters in the expected format
            return $counters;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get the search cases
     *
     * @url GET /search
     *
     * @param int $caseNumber
     * @param int $category
     * @param int $process
     * @param int $task
     * @param int $user
     * @param int $limit
     * @param int $offset
     * @param int $completedBy
     * @param int $startedBy
     * @param string $caseTitle
     * @param string $caseStatuses
     * @param string $filterCases
     * @param string $startCaseFrom
     * @param string $startCaseTo
     * @param string $finishCaseFrom
     * @param string $finishCaseTo
     * @param string $sort
     *
     * @return array
     *
     * @throws Exception
     *
     * @access protected
     * @class AccessControl {@permission PM_ALLCASES}
     */
    public function doGetSearchCases(
        int $caseNumber = 0,
        int $category = 0,
        int $process = 0,
        int $task = 0,
        int $user = 0,
        int $limit = 15,
        int $offset = 0,
        int $completedBy = 0,
        int $startedBy = 0,
        string $caseTitle = '',
        string $caseStatuses = '',
        string $filterCases = '',
        string $startCaseFrom = '',
        string $startCaseTo = '',
        string $finishCaseFrom = '',
        string $finishCaseTo = '',
        string $sort = 'APP_NUMBER,DESC'
    ) {
        try {
            $list = new Search();
            // Define the filters to apply
            $properties = [];
            $properties['caseNumber'] = $caseNumber;
            $properties['category'] = $category;
            $properties['caseTitle'] = $caseTitle;
            $properties['process'] = $process;
            $properties['task'] = $task;
            $properties['user'] = $user;
            $properties['userCompleted'] = $completedBy;
            $properties['userStarted'] = $startedBy;
            $properties['caseStatuses'] = explode(',', $caseStatuses);
            $properties['filterCases'] = $filterCases;
            $properties['startCaseFrom'] = $startCaseFrom;
            $properties['startCaseTo'] = $startCaseTo;
            $properties['finishCaseFrom'] = $finishCaseFrom;
            $properties['finishCaseTo'] = $finishCaseTo;
            $properties['start'] = $offset;
            $properties['limit'] = $limit;
            // Set the sort parameters
            $sort = explode(',', $sort);
            $properties['sort'] = $sort[0];
            $properties['dir'] = $sort[1];
            $list->setProperties($properties);
            $result = [];
            $result['data'] = DateTime::convertUtcToTimeZone($list->getData());
            // We will to enable always the pagination
            $result['total'] = $list->getCounter();
            return $result;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get home menu
     *
     * @url GET /menu
     *
     * @return array
     *
     * @access protected
     */
    public function getMenu()
    {
        global $RBAC;
        // Parse menu definition
        $menuInstance = new Menu();
        $menuInstance->load('home');

        // Initializing variables
        $optionsWithCounter = ['CASES_INBOX', 'CASES_DRAFT', 'CASES_PAUSED', 'CASES_SELFSERVICE'];
        $menuHome = [];

        // Build the Home menu
        for ($i = 0; $i < count($menuInstance->Options); $i++) {
            // Initializing option object
            $option = new stdClass();

            // Build the object according to the option menu type
            if ($menuInstance->Types[$i] === 'blockHeader') {
                $option->header = true;
                $option->title = $menuInstance->Labels[$i];
                $option->hiddenOnCollapse = true;
                $option->id = $menuInstance->Id[$i];
                if ( $option->id == "FOLDERS" && $RBAC->userCanAccess('TASK_METRICS_VIEW') == "1") {
                    $option->permission = true;   
                }
            } else {
                $option->href = $menuInstance->Options[$i];
                $option->id = $menuInstance->Id[$i];
                $option->title = $menuInstance->Labels[$i];
                $option->page = $menuInstance->Id[$i];
                $option->icon = $menuInstance->Icons[$i];
            }

            if ($menuInstance->Id[$i] === 'CASES_SEARCH') {
                // Get advanced search filters for the current user
                $filters = Filter::getByUser($this->getUserId());

                // Initializing
                $child = [];
                foreach ($filters as $filter) {
                    $childFilter = new stdClass();
                    $childFilter->id = $filter->id;
                    $childFilter->page = 'advanced-search';
                    $childFilter->href = "{$childFilter->page}/{$filter->id}";
                    $childFilter->title = $filter->name;
                    $childFilter->icon = 'fas fa-circle';
                    $childFilter->filters = $filter->filters;
                    $child[] = $childFilter;
                }

                // Adding filters to the "Advanced Search" option
                $option->child = $child;
            }
            if ($menuInstance->Id[$i] === 'ID_CASE_ARCHIVE_SEARCH') {
                $option->icon = "fas fa-archive";
            }
            //custom cases list
            if (in_array($menuInstance->Id[$i], $optionsWithCounter)) {
                $mapKeys = [
                    'CASES_INBOX' => 'inbox',
                    'CASES_DRAFT' => 'draft',
                    'CASES_SELFSERVICE' => 'unassigned',
                    'CASES_PAUSED' => 'paused'
                ];
                $option->customCasesList = [];
                $result = CaseList::getSetting($mapKeys[$menuInstance->Id[$i]], '', 0, 10, false);
                foreach ($result['data'] as $value) {
                    $option->customCasesList[] = [
                        "href" => "casesListExtJs?action=" . $mapKeys[$menuInstance->Id[$i]] . "&customList=" . $value['id'],
                        "id" => $value['id'],
                        "title" => $value['name'],
                        "description" => $value['description'],
                        "icon" => $value['iconList'],
                        "color" => $value['iconColor'],
                        "colorScreen" => $value['iconColorScreen'],
                        "page" => $mapKeys[$menuInstance->Id[$i]]
                    ];
                }
            }
            // Add option to the menu
            $menuHome[] = $option;
        }

        // Return menu
        return $menuHome;
    }

    /**
     * Get the search cases
     *
     * @url GET /:appNumber/pending-tasks
     *
     * @param int $appNumber
     *
     * @return array
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function getPendingTasks(int $appNumber)
    {
        // Get the pending task
        return Delegation::getPendingTask($appNumber);
    }

    /**
     * Get all processes, paged optionally, can be sent a text to filter results by "PRO_TITLE"
     *
     * @url GET /processes
     *
     * @param string $text
     * @param int $category
     * @param int $offset
     * @param int $limit
     * @param bool $paged
     *
     * @return array
     *
     * @throws Exception
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function getProcesses($text = null, $category = 0, int $offset = 0, int $limit = 15, $paged = true)
    {
        try {
            return Process::getProcessesForHome($text, $category, $offset, $limit, $paged);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get all users, paged optionally, can be sent a text to filter results by user information (first name, last name, username)
     *
     * @url GET /users
     *
     * @param string $text
     * @param int $offset
     * @param int $limit
     *
     * @return array
     *
     * @throws Exception
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function getUsers($text = null, int $offset = 0, int $limit = 15)
    {
        try {
            return User::getUsersForHome($text, $offset, $limit);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get the tasks counters for todo, draft, paused and unassigned
     *
     * @url GET /:task/counter
     *
     * @return array
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function getSpecificTaskCounter($task)
    {
        $result = [];
        $usrUid = $this->getUserId();
        $usrId = !empty($usrUid) ? User::getId($usrUid) : 0;
        switch ($task) {
            case 'inbox':
                $taskList = new Inbox();
                $text = G::LoadTranslation('ID_NUMBER_OF_CASES_INBOX');
                break;
            case 'draft':
                $taskList = new Draft();
                $text = G::LoadTranslation('ID_NUMBER_OF_CASES_DRAFT');
                break;
            case 'paused':
                $taskList = new Paused();
                $text = G::LoadTranslation('ID_NUMBER_OF_CASES_PAUSED');
                break;
            case 'unassigned':
                $taskList = new Unassigned();
                $text = G::LoadTranslation('ID_NUMBER_OF_CASES_UNASSIGNED');
                break;
            default:
              return [];
        }
        $taskList->setUserUid($usrUid);
        $taskList->setUserId($usrId);
        $count = $taskList->getCounter();
        $result = [];
        $result['label'] = $text . $count;
        $result['total'] = $count;
        return $result;
    }

    /**
     * Get task counters for inbox, draft, paused, and unassigned for custom case lists.
     * @url GET /:task/counter/caseList/:id
     * @param string $task
     * @param int $id
     * @return array
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function getCustomCaseListCounter(string $task, int $id)
    {
        try {
            $usrUid = $this->getUserId();
            $usrId = !empty($usrUid) ? User::getId($usrUid) : 0;
            switch ($task) {
                case 'inbox':
                    $taskList = new Inbox();
                    break;
                case 'draft':
                    $taskList = new Draft();
                    break;
                case 'paused':
                    $taskList = new Paused();
                    break;
                case 'unassigned':
                    $taskList = new Unassigned();
                    break;
                default:
                    return [];
            }
            $taskList->setUserUid($usrUid);
            $taskList->setUserId($usrId);
            $result = $taskList->getCustomListCount($id, $task);
            return $result;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get the tasks counters for all task list: todo, draft, paused and unassigned
     * 
     * @url GET /tasks/counter
     *
     * @return array
     * 
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function getTasksCounters()
    {
        $result = [];
        $usrUid = $this->getUserId();
        $usrId = !empty($usrUid) ? User::getId($usrUid) : 0;
        // For inbox
        $inbox = new Inbox();
        $inbox->setUserUid($usrUid);
        $inbox->setUserId($usrId);
        $result['todo'] = $inbox->getCounter();
        // For draft
        $draft = new Draft();
        $draft->setUserUid($usrUid);
        $draft->setUserId($usrId);
        $result['draft'] = $draft->getCounter();
        // For Paused
        $paused = new Paused();
        $paused->setUserUid($usrUid);
        $paused->setUserId($usrId);
        $result['paused'] = $paused->getCounter();
        // For Unassigned
        $unassigned = new Unassigned();
        $unassigned->setUserUid($usrUid);
        $unassigned->setUserId($usrId);
        $result['unassigned'] = $unassigned->getCounter();

        return $result;
    }

    /**
     * Get the tasks highlight for all task list
     *
     * @url GET /tasks/highlight
     *
     * @return array
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function getHighlight()
    {
        $usrUid = $this->getUserId();
        $casesList = new CasesList();
        $result = [];
        $result = $casesList->atLeastOne($usrUid);

        return $result;
    }

    /**
     * Get all tasks, paged optionally, can be sent a text to filter results by "TAS_TITLE"
     *
     * @url GET /tasks
     *
     * @param string $text
     * @param string $proId
     * @param int $offset
     * @param int $limit
     *
     * @return array
     *
     * @throws Exception
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function getTasks(string $text = null, string $proId = null, int $offset = 0, int $limit = 15)
    {
        try {
            return Task::getTasksForHome($text, $proId, $offset, $limit);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get the process debug status
     *
     * @url GET /process-debug-status
     *
     * @param string $processUid
     *
     * @return bool
     *
     * @throws Exception
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function getProcessDebugStatus(string $processUid)
    {
        try {
            // Get the process requested
            $process = Process::query()->select(['PRO_DEBUG'])->where('PRO_UID', '=', $processUid)->first();
            if (!is_null($process)) {
                return $process->PRO_DEBUG === 1;
            }
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
        // If not exists the requested process throw an 404 error
        if (is_null($process)) {
            throw new RestException(404, "Process with Uid '{$processUid}'.");
        }
    }

    /**
     * Get user setting.
     * @url GET /config/:id/:name
     * @param int $id
     * @param string $name
     * @return array
     * @throws RestException
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doGetConfig(int $id, string $name)
    {
        $setting = UserConfig::getSetting($id, $name);
        if (is_null($setting)) {
            $setting = [
                "status" => 404,
                "message" => G::LoadTranslation('ID_DOES_NOT_EXIST')
            ];
        }
        return $setting;
    }

    /**
     * Add user setting.
     * @url POST /config
     * @param int $id
     * @param string $name
     * @param array $setting
     * @return array
     * @throws RestException
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doPostConfig(int $id, string $name, array $setting)
    {
        try {
            return UserConfig::addSetting($id, $name, $setting);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, G::LoadTranslation('ID_EXIST'));
        }
    }

    /**
     * Update user setting.
     * @url PUT /config
     * @param int $id
     * @param string $name
     * @param array $setting
     * @return array
     * @throws RestException
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doPutConfig(int $id, string $name, array $setting)
    {
        $setting = UserConfig::editSetting($id, $name, $setting);
        if (is_null($setting)) {
            throw new RestException(Api::STAT_APP_EXCEPTION, G::LoadTranslation('ID_DOES_NOT_EXIST'));
        }
        return $setting;
    }

    /**
     * Delete user setting.
     * @url DELETE /config/:id/:name
     * @param int $id
     * @param string $name
     * @return array
     * @throws RestException
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doDeleteConfig(int $id, string $name)
    {
        $setting = UserConfig::deleteSetting($id, $name);
        if (is_null($setting)) {
            throw new RestException(Api::STAT_APP_EXCEPTION, G::LoadTranslation('ID_DOES_NOT_EXIST'));
        }
        return $setting;
    }

    /**
     * Get all process categories
     *
     * @url GET /categories
     *
     * @param string $name
     * @param int $limit
     * @param int $offset
     *
     * @return array
     *
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function getCategories($name = null, int $limit = 0, int $offset = 15)
    {
        try {
            return ProcessCategory::getProcessCategories($name, $offset, $limit);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }
}
