<?php

namespace ProcessMaker\BusinessModel\Cases;

use G;
use ProcessMaker\Model\AppNotes;
use ProcessMaker\Model\Delegation;
use ProcessMaker\Model\ProcessUser;
use ProcessMaker\Model\ProcessCategory;
use ProcessMaker\Model\User;

class Supervising extends AbstractCases
{
    // Columns to see in the cases list
    public $columnsView = [
        // Columns view in the cases list
        'APP_DELEGATION.APP_NUMBER', // Case #
        'APP_DELEGATION.DEL_TITLE', // Case Title
        'PROCESS.CATEGORY_ID', // Category
        'PROCESS.PRO_TITLE', // Process Name
        'TASK.TAS_TITLE', // Pending Task
        'APPLICATION.APP_STATUS', // Status
        'APPLICATION.APP_CREATE_DATE', // Start Date
        'APPLICATION.APP_FINISH_DATE', // Finish Date
        'APP_DELEGATION.DEL_TASK_DUE_DATE', // Due Date related to the colors
        'APP_DELEGATION.DEL_PREVIOUS', // Previous
        'USERS.USR_ID',  // Current UserId
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
        if (!empty($this->getCaseTitle())) {
            // Get the result
            $result = Delegation::casesThreadTitle($this->getCaseTitle());
            // Add the filter
            $query->specificCases($result);
        }
        // Specific category
        if ($this->getCategoryId()) {
            $query->categoryId($this->getCategoryId());
        }
        // Scope to search for an specific process
        if ($this->getProcessId()) {
            $query->processId($this->getProcessId());
        }
        // Specific task
        if ($this->getTaskId()) {
            $query->task($this->getTaskId());
        }
        // Specific status
        if ($this->getCaseStatus()) {
            $query->status($this->getCaseStatus());
        }
        // Specific start case date from
        if (!empty($this->getStartCaseFrom())) {
            $query->startDateFrom($this->getStartCaseFrom());
        }
        // Specific by start case date to
        if (!empty($this->getStartCaseTo())) {
            $query->startDateTo($this->getStartCaseTo());
        }
        // Specific finish case date from
        if (!empty($this->getFinishCaseFrom())) {
            $query->finishCaseFrom($this->getFinishCaseFrom());
        }
        // Filter by finish case date to
        if (!empty($this->getFinishCaseTo())) {
            $query->finishCaseTo($this->getFinishCaseTo());
        }
        // Specific case uid PMFCaseLink
        if (!empty($this->getCaseUid())) {
            $query->appUid($this->getCaseUid());
        }

        return $query;
    }

    /**
     * Gets the data for the Cases list Review
     * 
     * @return array
     */
    public function getData()
    {
        // Get the list of processes of the supervisor
        $processes = ProcessUser::getProcessesOfSupervisor($this->getUserUid());
        // We will prepare the queries if the user is supervisor
        if (!empty($processes)) {
            // Start the query for get the cases related to the user
            $query = Delegation::query()->select($this->getColumnsView());
            // Join with application
            $query->joinApplication();
            // Join with process
            $query->joinProcess();
            // Join with task
            $query->joinTask();
            // Join with users
            $query->joinUser();
            // Only cases in TO_DO
            $query->caseTodo();
            // Scope the specific array of processes supervising
            $query->processInList($processes);
            // Get only the last thread
            $query->lastThread();
            /** Apply filters */
            $this->filters($query);
            /** Apply order and pagination */
            //The order by clause
            $query->orderBy($this->getOrderByColumn(), $this->getOrderDirection());
            //The limit clause
            $query->offset($this->getOffset())->limit($this->getLimit());
            //Execute the query
            $results = $query->get();
            // Prepare the result
            $results->transform(function ($item, $key) {
                // Get the category
                $category = !empty($item['CATEGORY_ID']) ? ProcessCategory::getCategory($item['CATEGORY_ID']) : '';
                $item['CATEGORY'] = !empty($category) ? $category : G::LoadTranslation('ID_PROCESS_NONE_CATEGORY');
                // Get task color label
                $item['TAS_COLOR'] = $this->getTaskColor($item['DEL_TASK_DUE_DATE']);
                $item['TAS_COLOR_LABEL'] = self::TASK_COLORS[$item['TAS_COLOR']];
                // Apply the date format defined in environment
                $item['APP_CREATE_DATE_LABEL'] = !empty($item['APP_CREATE_DATE']) ? applyMaskDateEnvironment($item['APP_CREATE_DATE']): null;
                $item['APP_FINISH_DATE_LABEL'] = !empty($item['APP_FINISH_DATE']) ? applyMaskDateEnvironment($item['APP_FINISH_DATE']): null;
                // Calculate duration
                $startDate = (string)$item['APP_CREATE_DATE'];
                $endDate = !empty($item['APP_FINISH_DATE']) ? $item['APP_FINISH_DATE'] : date("Y-m-d H:i:s");
                $item['DURATION'] = getDiffBetweenDates($startDate, $endDate);
                // Get total case notes
                $item['CASE_NOTES_COUNT'] = AppNotes::total($item['APP_NUMBER']);
                // Get the detail related to the open thread
                $taskPending = Delegation::getPendingThreads($item['APP_NUMBER']);
                $result = [];
                $result['THREAD_TASKS'] = [];
                $result['THREAD_TITLES'] = [];
                foreach ($taskPending as $thread) {
                    $thread['APP_STATUS'] = $item['APP_STATUS'];
                    $information = $this->threadInformation($thread);
                    $result['THREAD_TASKS'][] = $information['THREAD_TASK'];
                    $result['THREAD_TITLES'][] = $information['THREAD_TITLE'];
                }
                $item['PENDING'] = $result['THREAD_TASKS'];
                $item['THREAD_TITLES'] = $result['THREAD_TITLES'];
                // Get send by related to the previous index
                $previousThread = Delegation::getThreadInfo($item['APP_NUMBER'], $item['DEL_PREVIOUS']);
                $userInfo = !empty($previousThread) ? User::getInformation($previousThread['USR_ID']) : [];
                $result = [];
                $result['del_previous'] = $item['DEL_PREVIOUS'];
                $result['user_tooltip'] = $userInfo;
                $item['SEND_BY_INFO'] = $result;

                return $item;
            });

            return $results->values()->toArray();
        } else {
            return [];
        }
    }

    /**
     * Count how many cases the user has in Supervising, does not apply filters
     * 
     * @return int
     */
    public function getCounter()
    {
        // Get base query
        $query = Delegation::query()->select();
        // Join with application
        $query->joinApplication();
        // Only cases in to_do
        $query->caseTodo();
        // Only open threads
        $query->threadOpen();
        // For parallel threads the distinct by APP_NUMBER is important
        $query->distinct();
        // Get the list of processes of the supervisor
        $processes = ProcessUser::getProcessesOfSupervisor($this->getUserUid());
        // Scope the specific array of processes supervising
        $query->processInList($processes);
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
        // This class does not require this value
        return false;
    }

    /**
     * Count how many cases the user has in Supervising, needs to apply filters
     *
     * @return int
     */
    public function getPagingCounters()
    {
        // Get base query
        $query = Delegation::query()->select();
        // Join with application
        $query->joinApplication();
        // Only cases in to_do
        $query->caseTodo();
        // Only open threads
        $query->threadOpen();
        // For parallel threads the distinct by APP_NUMBER is important
        $query->distinct();
        // Get the list of processes of the supervisor
        $processes = ProcessUser::getProcessesOfSupervisor($this->getUserUid());
        // Scope the specific array of processes supervising
        $query->processInList($processes);
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
}
