<?php

namespace ProcessMaker\BusinessModel\Cases;

use G;
use ProcessMaker\Model\Application;
use ProcessMaker\Model\AppNotes;
use ProcessMaker\Model\Delegation;
use ProcessMaker\Model\Process;
use ProcessMaker\Model\ProcessCategory;

class Search extends AbstractCases
{
    // Columns to see in the cases list
    public $columnsView = [
        // Columns view in the cases list
        'APPLICATION.APP_NUMBER', // Case #
        'APPLICATION.APP_TITLE AS DEL_TITLE', // Case Title
        'PROCESS.CATEGORY_ID', // Category
        'PROCESS.PRO_TITLE', // Process
        'APPLICATION.APP_STATUS',  // Status
        'APPLICATION.APP_CREATE_DATE',  // Case create date
        'APPLICATION.APP_FINISH_DATE',  // Case finish date
        // Additional column for other functionalities
        'APPLICATION.APP_UID', // Case Uid for Open case
        'APPLICATION.PRO_UID', // Process Uid for Case notes
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
        // Filter case by case number
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
            $result = Delegation::casesThreadTitle($this->getCaseTitle(), $this->getOffset(), $this->getLimit());
            // Add the filter
            $query->specificCases($result);
        }
        // Filter by category
        if ($this->getCategoryId()) {
            // This filter require a join with the process table
            $query->category($this->getCategoryId());
        }
        // Filter by process
        if ($this->getProcessId()) {
            $result = Process::query()->select(['PRO_UID'])
                ->where('PRO_ID', '=', $this->getProcessId())->get()->toArray();
            $result = head($result);
            $query->proUid($result['PRO_UID']);
        }
        // Filter by user
        if ($this->getUserId()) {
            // Join with delegation
            $query->joinDelegation();
            // Add the filter
            $query->userId($this->getUserId());
            // Get only the open threads related to the user or the last index
            $query->where(function ($query) {
                $query->where('APP_DELEGATION.DEL_THREAD_STATUS', 'OPEN');
                $query->orWhere(function ($query) {
                    $query->where('APP_DELEGATION.DEL_LAST_INDEX', '1');
                });
            });
        }
        // Filter by user who started
        if ($this->getUserStartedId()) {
            // Get the case numbers related to this filter
            $result = Delegation::casesStartedBy($this->getUserStartedId());
            $query->specificCases($result);
        }
        // Filter by user who completed
        if ($this->getUserCompletedId()) {
            // Get the case numbers related to this filter
            $result = Delegation::casesCompletedBy($this->getUserCompletedId());
            $query->specificCases($result);
        }
        // Filter by task
        if ($this->getTaskId()) {
            if (!$this->getUserId()) {
                // Join with delegation if was not defined before
                $query->joinDelegation();
            }
            // Add the filter
            $query->task($this->getTaskId());
            // Get only the open threads related to the task or the last index
            $query->where(function ($query) {
                $query->where('APP_DELEGATION.DEL_THREAD_STATUS', 'OPEN');
                $query->orWhere(function ($query) {
                    $query->where('APP_DELEGATION.DEL_LAST_INDEX', '1');
                });
            });
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
        // Filter related to the case status like ['DRAFT', 'TO_DO']
        if (!empty($this->getCaseStatuses())) {
            $query->statusIds($this->getCaseStatuses());
        }

        return $query;
    }

    /**
     * Get the data corresponding to advanced search
     *
     * @return array
     */
    public function getData()
    {
        $query = Application::query()->select($this->getColumnsView());
        // Join with process
        $query->joinProcess();
        /** Apply filters */
        $this->filters($query);
        /** Exclude the web entries does not submitted */
        $query->positiveCases($query);
        /** Apply order and pagination */
        // The order by clause
        $query->orderBy($this->getOrderByColumn(), $this->getOrderDirection());
        // The limit by clause
        $query->offset($this->getOffset())->limit($this->getLimit());
        //Execute the query
        $results = $query->get();
        // Prepare the result
        $results->transform(function ($item, $key) {
            // Get the category
            $category = !empty($item['CATEGORY_ID']) ? ProcessCategory::getCategory($item['CATEGORY_ID']) : '';
            $item['CATEGORY'] = !empty($category) ? $category : G::LoadTranslation('ID_PROCESS_NONE_CATEGORY');
            // Apply the date format defined in environment
            $item['APP_CREATE_DATE_LABEL'] = !empty($item['APP_CREATE_DATE']) ? applyMaskDateEnvironment($item['APP_CREATE_DATE']): null;
            $item['APP_FINISH_DATE_LABEL'] = !empty($item['APP_FINISH_DATE']) ? applyMaskDateEnvironment($item['APP_FINISH_DATE']): null;
            // Calculate duration
            $startDate = (string)$item['APP_CREATE_DATE'];
            $endDate = !empty($item['APP_FINISH_DATE']) ? $item['APP_FINISH_DATE'] : date("Y-m-d H:i:s");
            $dateToCompare = !empty($item['APP_FINISH_DATE']) ? $item['APP_FINISH_DATE'] : 'now';
            $item['DURATION'] = getDiffBetweenDates($startDate, $endDate);
            // Get total case notes
            $item['CASE_NOTES_COUNT'] = AppNotes::total($item['APP_NUMBER']);
            // Get the detail related to the open thread
            $taskPending = [];
            $status = $item['APP_STATUS'];
            switch ($status) {
                case 'DRAFT':
                case 'TO_DO':
                    $taskPending = Delegation::getPendingThreads($item['APP_NUMBER'], false);
                    break;
                case 'COMPLETED':
                case 'CANCELLED':
                    $taskPending = Delegation::getLastThread($item['APP_NUMBER']);
                    break;
            }
            $i = 0;
            $result = [];
            foreach ($taskPending as $thread) {
                $thread['APP_STATUS'] = $item['APP_STATUS'];
                $information = $this->threadInformation($thread, true);
                $result['THREAD_TASKS'][$i] = $information['THREAD_TASK'];
                $result['THREAD_USERS'][$i] = $information['THREAD_USER'];
                $result['THREAD_TITLES'][$i] = $information['THREAD_TITLE'];
                $i++;
                // Del Index for Open case
                $item['DEL_INDEX'] = $information['THREAD_TITLE']['del_index'];
                // Task Uid for Case notes
                $item['TAS_UID'] = $information['THREAD_TASK']['tas_uid'];
            }

            $item['THREAD_TASKS'] = !empty($result['THREAD_TASKS']) ? $result['THREAD_TASKS'] : [];
            $item['THREAD_USERS'] = !empty($result['THREAD_USERS']) ? $result['THREAD_USERS'] : [];
            $item['THREAD_TITLES'] = !empty($result['THREAD_TITLES']) ? $result['THREAD_TITLES'] : [];

            return $item;
        });

        return $results->values()->toArray();
    }

    /**
     * Count how many cases the user has in the advanced search, does not apply filters
     *
     * @return int
     */
    public function getCounter()
    {
        // The search does not have a counters
        return 0;
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
     * Get the number of rows corresponding to the advanced search, needs to apply filters
     *
     * @return int
     */
    public function getPagingCounters()
    {
        // The search always will enable the pagination
        return 0;
    }
}