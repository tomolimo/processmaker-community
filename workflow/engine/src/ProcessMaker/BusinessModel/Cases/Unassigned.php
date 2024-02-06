<?php

namespace ProcessMaker\BusinessModel\Cases;

use G;
use ProcessMaker\Model\CaseList;
use ProcessMaker\Model\Delegation;
use ProcessMaker\Model\Task;
use ProcessMaker\Model\Process;
use ProcessMaker\Model\ProcessCategory;
use ProcessMaker\Model\User;

class Unassigned extends AbstractCases
{
    // Columns to see in the cases list
    public $columnsView = [
        // Columns view in the cases list
        'APP_DELEGATION.APP_NUMBER', // Case #
        'APP_DELEGATION.DEL_TITLE', // Case Title
        'PROCESS.CATEGORY_ID', // Category
        'PROCESS.PRO_TITLE', // Process
        'TASK.TAS_TITLE', // Task
        'USERS.USR_USERNAME', // Current UserName
        'USERS.USR_FIRSTNAME', // Current User FirstName
        'USERS.USR_LASTNAME', // Current User LastName
        'APP_DELEGATION.DEL_TASK_DUE_DATE', // Due Date
        'APP_DELEGATION.DEL_DELEGATE_DATE', // Delegate Date
        'APP_DELEGATION.DEL_INIT_DATE', // Init Date
        'APP_DELEGATION.DEL_PRIORITY', // Priority
        'APP_DELEGATION.DEL_PREVIOUS', // Previous
        // Additional column for other functionalities
        'APP_DELEGATION.APP_UID', // Case Uid for Open case
        'APP_DELEGATION.DEL_INDEX', // Del Index for Open case
        'APP_DELEGATION.PRO_UID', // Process Uid for Case notes
        'APP_DELEGATION.TAS_UID', // Task Uid for Case notes
    ];

    /**
     * Get the columns related to the cases list
     * @return array
     */
    public function getColumnsView()
    {
        return $this->columnsView;
    }

    /**
     * Scope filters
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filters($query)
    {
        // Specific case
        if ($this->getCaseNumber()) {
            $query->case($this->getCaseNumber());
        }
        // Filter only cases by specific cases like [1,3,5]
        if (!empty($this->getCasesNumbers()) && empty($this->getRangeCasesFromTo())) {
            $query->specificCases($this->getCasesNumbers());
        }
        // Filter only cases by range of cases like ['1-5', '10-15']
        if (!empty($this->getRangeCasesFromTo()) && empty($this->getCasesNumbers())) {
            $query->rangeOfCases($this->getRangeCasesFromTo());
        }
        // Filter cases mixed by range of cases and specific cases like '1,3-5,8'
        if (!empty($this->getCasesNumbers()) && !empty($this->getRangeCasesFromTo())) {
            $query->casesOrRangeOfCases($this->getCasesNumbers(), $this->getRangeCasesFromTo());
        }
        // Specific case title
        if ($this->getCaseTitle()) {
            $query->title($this->getCaseTitle());
        }
        // Specific category
        if ($this->getCategoryId()) {
            $query->categoryId($this->getCategoryId());
        }
        // Specific process
        if ($this->getProcessId()) {
            $query->processId($this->getProcessId());
        }
        // Specific task
        if ($this->getTaskId()) {
            $query->task($this->getTaskId());
        }
        // Specific case uid PMFCaseLink
        if (!empty($this->getCaseUid())) {
            $query->appUid($this->getCaseUid());
        }
        // Specific delegate date from
        if (!empty($this->getDelegateFrom())) {
            $query->delegateDateFrom($this->getDelegateFrom());
        }
        // Specific delegate date to
        if (!empty($this->getDelegateTo())) {
            $query->delegateDateTo($this->getDelegateTo());
        }
        // Specific usrId represented by sendBy
        if (!empty($this->getSendBy())) {
            $query->sendBy($this->getSendBy());
        }
        // Specific review status
        if (!empty($this->getReviewStatus())) {
            $query->readUnread($this->getReviewStatus());
        }

        return $query;
    }

    /**
     * Get data self-services cases by user
     * @param callable $callback
     * @return array
     */
    public function getData(callable $callback = null)
    {
        $query = Delegation::query()->select($this->getColumnsView());
        // Join with process
        $query->joinProcess();
        // Join with users
        $query->joinUser();
        // Join with application for add the initial scope for unassigned cases
        if (!empty($this->getUserUid())) {
            $query->selfService($this->getUserUid());
        }
        // Add join for application, for get the case title when the case status is TO_DO
        $query->joinApplication();
        $query->status(self::STATUS_TODO);
        /** Apply filters */
        $this->filters($query);
        /** Apply order and pagination */
        // Add any sort if needed
        if ($this->getOrderByColumn()) {
            $query->orderBy($this->getOrderByColumn(), $this->getOrderDirection());
        }
        // Add pagination to the query
        $query->offset($this->getOffset())->limit($this->getLimit());
        if (is_callable($callback)) {
            $callback($query);
        }
        // Get the data
        $results = $query->get();
        // Prepare the result
        $results->transform(function ($item, $key) {
            // Get the category
            $category = !empty($item['CATEGORY_ID']) ? ProcessCategory::getCategory($item['CATEGORY_ID']) : '';
            $item['CATEGORY'] = !empty($category) ? $category : G::LoadTranslation('ID_PROCESS_NONE_CATEGORY');
            // Get priority label
            $priorityLabel = self::PRIORITIES[$item['DEL_PRIORITY']];
            $item['DEL_PRIORITY_LABEL'] = G::LoadTranslation("ID_PRIORITY_{$priorityLabel}");
            // Get task color label
            $item['TAS_COLOR'] = $this->getTaskColor($item['DEL_TASK_DUE_DATE']);
            $item['TAS_COLOR_LABEL'] = self::TASK_COLORS[$item['TAS_COLOR']];
            // Get task status
            $item['TAS_STATUS'] = self::TASK_STATUS[$item['TAS_COLOR']];
            // Get delay
            $item['DELAY'] = getDiffBetweenDates($item['DEL_TASK_DUE_DATE'],  date("Y-m-d H:i:s"));
            // Apply the date format defined in environment
            $item['DEL_TASK_DUE_DATE_LABEL'] = applyMaskDateEnvironment($item['DEL_TASK_DUE_DATE']);
            $item['DEL_DELEGATE_DATE_LABEL'] = applyMaskDateEnvironment($item['DEL_DELEGATE_DATE']);
            // Get the send by related to the previous index
            $previousThread = Delegation::getThreadInfo($item['APP_NUMBER'], $item['DEL_PREVIOUS']);
            $userInfo = [];
            $dummyInfo = [];
            if (!empty($previousThread)) {
                // When the task has an user
                $userInfo = ($previousThread['USR_ID'] !== 0) ? User::getInformation($previousThread['USR_ID']) : [];
                // When the task does not have users refers to dummy task
                $taskInfo = ($previousThread['USR_ID'] === 0) ? Task::title($previousThread['TAS_ID']) : [];
                if (!empty($taskInfo)) {
                    $dummyInfo = [
                        'task_id' => $previousThread['TAS_ID'],
                        'name' => $taskInfo['title'],
                        'type' => $taskInfo['type']
                    ];
                }
            }
            $result = [];
            $result['del_previous'] = $item['DEL_PREVIOUS'];
            $result['key_name'] = !empty($userInfo) ? 'user_tooltip' : 'dummy_task';
            $result['user_tooltip'] = $userInfo;
            $result['dummy_task'] = $dummyInfo;
            $item['SEND_BY_INFO'] = $result;

            return $item;
        });

        return $results->values()->toArray();
    }

    /**
     * Count how many cases the user has in SELF_SERVICE, does not apply filters
     *
     * @return int
     */
    public function getCounter()
    {
        $query = Delegation::query()->select();
        // Add the initial scope for self-service cases
        $query->selfService($this->getUserUid());
        // Return the number of rows
        return $query->count(['APP_DELEGATION.APP_NUMBER']);
    }

    /**
     * Count if the user has at least one case in the list
     *
     * @return bool
     */
    public function atLeastOne()
    {
        $query = Delegation::query()->select(['APP_DELEGATION.APP_NUMBER']);
        // Add the initial scope for self-service cases
        $query->selfService($this->getUserUid());
        // Get only one case
        $query->limit(1);
        // Get result
        $items = $query->get();

        return $items->count() > 0;
    }

    /**
     * Count how many cases the user has in SELF_SERVICE, needs to apply filters
     *
     * @return int
     */
    public function getPagingCounters()
    {
        $query = Delegation::query()->select();
        // Add the initial scope for self-service cases
        $query->selfService($this->getUserUid());
        // Check if the category was defined
        if ($this->getCategoryId()) {
            // Join with process if the filter with category exist
            $query->joinProcess();
        }
        // Apply filters
        $this->filters($query);
        // Return the number of rows
        return $query->count(['APP_DELEGATION.APP_NUMBER']);
    }

    /**
     * Returns the total cases of the custom unassigned list.
     * @param int $id
     * @param string $type
     * @return array
     */
    public function getCustomListCount(int $id, string $type): array
    {
        $caseList = CaseList::getCaseList($id, $type);
        $query = Delegation::query()->select();
        $query->selfService($this->getUserUid());

        $name = '';
        $description = '';
        $tableName = '';
        if (!is_null($caseList)) {
            $name = $caseList->CAL_NAME;
            $description = $caseList->CAL_DESCRIPTION;
            $tableName = $caseList->ADD_TAB_NAME;
            $query->leftJoin($caseList->ADD_TAB_NAME, $caseList->ADD_TAB_NAME . '.APP_UID', '=', 'APP_DELEGATION.APP_UID');
            $process = Process::getIds($caseList->PRO_UID, 'PRO_UID');
            $proId = head($process)['PRO_ID'];
            $query->processId($proId);
        }
        $count = $query->count(['APP_DELEGATION.APP_NUMBER']);
        return [
            'label' => G::LoadTranslation('ID_NUMBER_OF_CASES_UNASSIGNED') . $count,
            'name' => $name,
            'description' => $description,
            'tableName' => $tableName,
            'total' => $count
        ];
    }

    /**
     * Count how many cases there are in SELF_SERVICE
     *
     * @return int
     */
    public function getCounterMetrics()
    {
        $query = Delegation::query()->select();
        $query->selfServiceMetrics();
        return $query->count(['APP_DELEGATION.APP_NUMBER']);
    }
}
