<?php

namespace ProcessMaker\BusinessModel\Cases;

use G;
use ProcessMaker\Model\Application;
use ProcessMaker\Model\AppNotes;
use ProcessMaker\Model\Delegation;
use ProcessMaker\Model\Task;
use ProcessMaker\Model\ProcessCategory;
use ProcessMaker\Model\User;

class Participated extends AbstractCases
{
    // Columns to see in the cases list
    public $columnsView = [
        // Columns view in the cases list
        'APP_DELEGATION.APP_NUMBER', // Case #
        'APP_DELEGATION.DEL_TITLE', // Case Title
        'PROCESS.CATEGORY_ID', // Category
        'PROCESS.PRO_TITLE', // Process Name
        'TASK.TAS_TITLE', // Pending Task
        'TASK.TAS_ASSIGN_TYPE', // Task assign rule
        'APPLICATION.APP_STATUS', // Status
        'APPLICATION.APP_CREATE_DATE', // Start Date
        'APPLICATION.APP_FINISH_DATE', // Finish Date
        'APP_DELEGATION.DEL_TASK_DUE_DATE', // Due Date related to the colors
        'APP_DELEGATION.DEL_PREVIOUS', // Previous
        'USERS.USR_ID', // Current UserId
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
            $queryTask = Delegation::query()->select('APP_NUMBER');
            $queryTask->task($this->getTaskId());
            $queryTask->threadOpen();
            $results = $queryTask->get();
            $result = $results->values()->toArray();
            // Filter the cases related to the specific task
            $query->specificCases($result);
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
     * Get the data corresponding to Participated
     *
     * @return array
     */
    public function getData()
    {
        // Start the query for get the cases related to the user
        $query = Delegation::query()->select($this->getColumnsView());
        // Join with process
        $query->joinProcess();
        // Join with task
        $query->joinTask();
        // Join with users
        $query->joinUser();
        // Join with application
        $query->joinApplication();
        // Add filter
        $filter = $this->getParticipatedStatus();
        switch ($filter) {
            case 'STARTED':
                // Scope to Participated
                $query->participated($this->getUserId());
                // Scope that search for the STARTED by user: DRAFT, TO_DO, CANCELED AND COMPLETED
                $query->caseStarted();
                break;
            case 'IN_PROGRESS':
                // Scope to Participated
                $query->participated($this->getUserId());
                // Only cases in progress: TO_DO without DRAFT
                $query->caseTodo();
                // Group by AppNumber
                $query->groupBy('APP_NUMBER');
                break;
            case 'COMPLETED':
                // Scope to Participated User
                $query->participatedUser($this->getUserId());
                // Scope that search for the COMPLETED
                $query->caseCompleted();
                // Scope to set the last thread
                $query->lastThread();
                break;
        }
        /** Apply filters */
        $this->filters($query);
        /** Apply order and pagination */
        // The order by clause
        $query->orderBy($this->getOrderByColumn(), $this->getOrderDirection());
        // The limit by clause
        $query->offset($this->getOffset())->limit($this->getLimit());
        //Execute the query
        $results = $query->get();
        // Prepare the result
        $results->transform(function ($item, $key) use ($filter) {
            // Get the category
            $category = !empty($item['CATEGORY_ID']) ? ProcessCategory::getCategory($item['CATEGORY_ID']) : '';
            $item['CATEGORY'] = !empty($category) ? $category : G::LoadTranslation('ID_PROCESS_NONE_CATEGORY');
            // Apply the date format defined in environment
            $item['APP_CREATE_DATE_LABEL'] = !empty($item['APP_CREATE_DATE']) ? applyMaskDateEnvironment($item['APP_CREATE_DATE']): null;
            $item['APP_FINISH_DATE_LABEL'] = !empty($item['APP_FINISH_DATE']) ? applyMaskDateEnvironment($item['APP_FINISH_DATE']): null;
            // Calculate duration
            $startDate = (string)$item['APP_CREATE_DATE'];
            $endDate = !empty($item['APP_FINISH_DATE']) ? $item['APP_FINISH_DATE'] : date("Y-m-d H:i:s");
            $item['DURATION'] = getDiffBetweenDates($startDate, $endDate);
            // Get total case notes
            $item['CASE_NOTES_COUNT'] = AppNotes::total($item['APP_NUMBER']);
            // Define the thread information
            $thread = [];
            $thread['TAS_TITLE'] = $item['TAS_TITLE'];
            $thread['USR_ID'] = $item['USR_ID'];
            $thread['DEL_TASK_DUE_DATE'] = $item['DEL_TASK_DUE_DATE'];
            $thread['APP_FINISH_DATE'] = $item['APP_FINISH_DATE'];
            $thread['TAS_ASSIGN_TYPE'] = $item['TAS_ASSIGN_TYPE'];
            $thread['APP_STATUS'] = $item['APP_STATUS'];
            // Define data according to the filters
            switch ($filter) {
                case 'STARTED':
                case 'IN_PROGRESS':
                    switch ($item['APP_STATUS']) {
                        case 'TO_DO':
                            // Get the pending task
                            $taskPending = Delegation::getPendingThreads($item['APP_NUMBER'], false);
                            $result = [];
                            $result['THREAD_TASKS'] = [];
                            $result['THREAD_TITLES'] = [];
                            foreach ($taskPending as $thread) {
                                $thread['APP_STATUS'] = $item['APP_STATUS'];
                                // Get the thread information
                                $information = $this->threadInformation($thread);
                                $result['THREAD_TASKS'][] = $information['THREAD_TASK'];
                                $result['THREAD_TITLES'][] = $information['THREAD_TITLE'];
                            }
                            // Return THREAD_TASKS and THREAD_USERS in the same column
                            $item['PENDING'] = !empty($result['THREAD_TASKS']) ? $result['THREAD_TASKS'] : [];
                            // Return the THREAD_TITLES
                            $item['THREAD_TITLES'] = !empty($result['THREAD_TITLES']) ? $result['THREAD_TITLES'] : [];
                            break;
                        case 'COMPLETED':
                            // Get the last thread
                            $taskPending = Delegation::getLastThread($item['APP_NUMBER']);
                            // Get the head of array
                            $thread = head($taskPending);
                            // Define some values required for define the color status
                            $thread['APP_STATUS'] = $item['APP_STATUS'];
                            $thread['APP_FINISH_DATE'] = $item['APP_FINISH_DATE'];
                            // Get the thread information
                            $information = $this->threadInformation($thread);
                            $result = [];
                            $result[] = $information['THREAD_TASK'];
                            // Return THREAD_TASKS and THREAD_USERS in the same column
                            $item['PENDING'] = $result;
                            // Return the THREAD_TITLES
                            $result = [];
                            $result[] = $information['THREAD_TITLE'];
                            $item['THREAD_TITLES'] = $result;
                            break;
                        default: // Other status like DRAFT
                            // Get the last thread
                            $taskPending = Delegation::getLastThread($item['APP_NUMBER']);
                            // Get the head of array
                            $thread = head($taskPending);
                            // Define some values required for define the color status
                            $thread['APP_STATUS'] = $item['APP_STATUS'];
                            $thread['APP_FINISH_DATE'] = $item['APP_FINISH_DATE'];
                            // Get the thread information
                            $information = $this->threadInformation($thread);
                            $result = [];
                            $result[] = $information['THREAD_TASK'];
                            // Return THREAD_TASKS and THREAD_USERS in the same column
                            $item['PENDING'] = $result;
                            // Return the THREAD_TITLES
                            $result = [];
                            $result[] = $information['THREAD_TITLE'];
                            $item['THREAD_TITLES'] = $result;
                    }
                    break;
                case 'COMPLETED':
                    // Get the last thread
                    $taskPending = Delegation::getLastThread($item['APP_NUMBER']);
                    // Get the head of array
                    $thread = head($taskPending);
                    // Define some values required for define the color status
                    $thread['APP_STATUS'] = $item['APP_STATUS'];
                    $thread['APP_FINISH_DATE'] = $item['APP_FINISH_DATE'];
                    // Get the thread information
                    $information = $this->threadInformation($thread);
                    $result = [];
                    $result[] = $information['THREAD_TASK'];
                    // Return THREAD_TASKS and THREAD_USERS in the same column
                    $item['PENDING'] = $result;
                    // Return the THREAD_TITLES
                    $result = [];
                    $result[] = $information['THREAD_TITLE'];
                    $item['THREAD_TITLES'] = $result;
                    break;
            }
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
    }

    /**
     * Get the number of rows corresponding has Participation, does not apply filters
     *
     * @return int
     */

    public function getCounter()
    {
        // Get base query
        $query = Delegation::query()->select();
        // Join with application
        $query->joinApplication();
        // Get filter
        $filter = $this->getParticipatedStatus();
        switch ($filter) {
            case 'STARTED':
                // Scope that sets the queries for Participated
                $query->participated($this->getUserId());
                // Scope that search for the STARTED by user: DRAFT, TO_DO, CANCELED AND COMPLETED
                $query->caseStarted();
                break;
            case 'IN_PROGRESS':
                // Scope that sets the queries for Participated
                $query->participated($this->getUserId());
                // Only distinct APP_NUMBER
                $query->distinct();
                // Scope for only TO_DO cases
                $query->caseTodo();
                break;
            case 'COMPLETED':
                // Scope that sets the queries for Participated
                $query->participatedUser($this->getUserId());
                // Scope that search for the COMPLETED
                $query->caseCompleted();
                // Scope to set the last thread
                $query->lastThread();
                break;
        }
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
     * Count how many cases the user has Participation, needs to apply filters
     *
     * @return int
     */
    public function getPagingCounters()
    {
        // Get base query
        $query = Delegation::query()->select();
        // Join with application
        $query->joinApplication();
        // Scope that sets the queries for Participated
        $query->participated($this->getUserId());
        // Get filter
        $filter = $this->getParticipatedStatus();
        switch ($filter) {
            case 'STARTED':
                // Scope that search for the STARTED by user: DRAFT, TO_DO, CANCELED AND COMPLETED
                $query->caseStarted();
                break;
            case 'IN_PROGRESS':
                // Only distinct APP_NUMBER
                $query->distinct();
                // Scope for in progress: TO_DO without DRAFT
                $query->caseTodo();
                break;
            case 'COMPLETED':
                // Scope that search for the COMPLETED
                $query->caseCompleted();
                // Scope to set the last thread
                $query->lastThread();
                break;
        }
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
