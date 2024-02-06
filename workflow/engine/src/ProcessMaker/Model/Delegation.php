<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Cases;
use DateTime;
use G;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use ProcessMaker\BusinessModel\Cases\AbstractCases;
use ProcessMaker\Core\System;
use ProcessMaker\Model\Task;

class Delegation extends Model
{
    use HasFactory;

    // Class constants
    const PRIORITIES_MAP = [1 => 'VL', 2 => 'L', 3 => 'N', 4 => 'H', 5 => 'VH'];

    protected $table = "APP_DELEGATION";

    // We don't have our standard timestamp columns
    public $timestamps = false;

    // Static properties to preserve values
    public static $usrUid = '';
    public static $groups = [];
    // Status name and status id
    public static $thread_status = ['CLOSED' => 0, 'OPEN' => 1, 'PAUSED' => 3];

    /**
     * Returns the application this delegation belongs to
     */
    public function application()
    {
        return $this->belongsTo(Application::class, 'APP_UID', 'APP_UID');
    }

    /**
     * Returns the user this delegation belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'USR_ID', 'USR_ID');
    }

    /**
     * Return the process task this belongs to
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'TAS_ID', 'TAS_ID');
    }

    /**
     * Return the process this delegation belongs to
     */
    public function process()
    {
        return $this->belongsTo(Process::class, 'PRO_ID', 'PRO_ID');
    }

    /**
     * Scope a query to only include specific priority
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $priority
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePriority($query, int $priority)
    {
        return $query->where('DEL_PRIORITY', $priority);
    }

    /**
     * Scope a query to only include specific priorities
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array $priorities
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePriorities($query, array $priorities)
    {
        return $query->whereIn('DEL_PRIORITY', $priorities);
    }

    /**
     * Scope a query to only include open threads
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeThreadOpen($query)
    {
        return $query->where('APP_DELEGATION.DEL_THREAD_STATUS', '=', 'OPEN');
    }

    /**
     * Scope a query to only include open threads
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeThreadIdOpen($query)
    {
        return $query->where('APP_DELEGATION.DEL_THREAD_STATUS_ID', 1);
    }

    /**
     * Scope a query to only include pause threads
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeThreadPause($query)
    {
        return $query->where('APP_DELEGATION.DEL_THREAD_STATUS_ID', '=', 3);
    }

    /**
     * Scope a query to only include open and pause threads
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpenAndPause($query)
    {
        $query->where(function ($query) {
            $query->threadOpen();
            $query->orWhere(function ($query) {
                $query->threadPause();
            });
        });
        return $query;
    }

    /**
     * Scope to use when the case is IN_PROGRESS like DRAFT or TO_DO
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array $ids
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCasesInProgress($query, array $ids)
    {
        $query->threadOpen()->statusIds($ids);

        return $query;
    }

    /**
     * Scope to use when the case is DONE like COMPLETED or CANCELED
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array $ids
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCasesDone($query, array $ids)
    {
        return $query->lastThread()->statusIds($ids);
    }

    /**
     * Scope a query to only include a specific index
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $index
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIndex($query, int $index)
    {
        return $query->where('DEL_INDEX', '=', $index);
    }

    /**
     * Scope a query to get the started by me
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCaseStarted($query)
    {
        return $query->where('DEL_INDEX', '=', 1);
    }

    /**
     * Scope a query to get the to_do cases
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCaseTodo($query)
    {
        return $query->where('APPLICATION.APP_STATUS_ID', Application::STATUS_TODO);
    }

    /**
     * Scope a query to get the completed by me
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCaseCompleted($query)
    {
        return $query->where('APPLICATION.APP_STATUS_ID', Application::STATUS_COMPLETED);
    }

    /**
     * Scope a query to get the canceled by me
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCaseCanceled($query)
    {
        return $query->where('APPLICATION.APP_STATUS_ID', Application::STATUS_CANCELED);
    }

    /**
     * Scope a query to get specific status
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param int $statusId
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, int $statusId)
    {
        return $query->where('APPLICATION.APP_STATUS_ID', $statusId);
    }

    /**
     * Scope a more status
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $statuses
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatusIds($query, array $statuses)
    {
        return $query->whereIn('APPLICATION.APP_STATUS_ID', $statuses);
    }

    /**
     * Scope a query to only include a specific start date
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $from
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStartDateFrom($query, string $from)
    {
        return $query->where('APPLICATION.APP_CREATE_DATE', '>=', $from);
    }

    /**
     * Scope a query to only include a specific start date
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $to
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStartDateTo($query, string $to)
    {
        return $query->where('APPLICATION.APP_CREATE_DATE', '<=', $to);
    }

    /**
     * Scope a query to only include a specific finish date
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $from
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFinishCaseFrom($query, string $from)
    {
        return $query->where('APPLICATION.APP_FINISH_DATE', '>=', $from);
    }

    /**
     * Scope a query to only include a specific finish date
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $to
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFinishCaseTo($query, string $to)
    {
        return $query->where('APPLICATION.APP_FINISH_DATE', '<=', $to);
    }

    /**
     * Scope a query to only include unread thread
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReadUnread($query, $status)
    {
        if ($status === 'READ') {
            // READ
            return $query->whereNotNull('DEL_INIT_DATE');
        } else {
            // UNREAD
            return $query->whereNull('DEL_INIT_DATE');
        }
    }

    /**
     * Scope a query to only include a specific delegate date
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $from
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDelegateDateFrom($query, string $from)
    {
        return $query->where('DEL_DELEGATE_DATE', '>=', $from);
    }

    /**
     * Scope a query to only include a specific delegate date
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $to
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDelegateDateTo($query, string $to)
    {
        return $query->where('DEL_DELEGATE_DATE', '<=', $to);
    }

    /**
     * Scope a query to only include a specific finish date
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $from
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFinishDateFrom($query, $from)
    {
        return $query->where('DEL_FINISH_DATE', '>=', $from);
    }

    /**
     * Scope a query to only include a specific finish date
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $to
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFinishDateTo($query, $to)
    {
        return $query->where('DEL_FINISH_DATE', '<=', $to);
    }

    /**
     * Scope a query to only include a specific due date
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $from
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDueFrom($query, $from)
    {
        return $query->where('DEL_TASK_DUE_DATE', '>=', $from);
    }

    /**
     * Scope a query to only include a specific due date
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $to
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDueTo($query, $to)
    {
        return $query->where('DEL_TASK_DUE_DATE', '<=', $to);
    }

    /**
     * Scope a query to get only the date on time
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $now
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnTime($query, $now)
    {
        return $query->where('DEL_RISK_DATE', '>', $now);
    }

    /**
     * Scope a query to get only the date at risk
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $now
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAtRisk($query, $now)
    {
        return $query->where('DEL_RISK_DATE', '<=', $now)->where('DEL_TASK_DUE_DATE', '>=', $now);
    }

    /**
     * Scope a query to get only the date overdue
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $now
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOverdue($query, $now)
    {
        return $query->where('DEL_TASK_DUE_DATE', '<', $now);
    }

    /**
     * Scope a query to only include a specific case
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $appNumber
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCase($query, $appNumber)
    {
        return $query->where('APP_DELEGATION.APP_NUMBER', '=', $appNumber);
    }

    /**
     * Scope a query to only include a specific case title
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $search
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTitle($query, string $search)
    {
        $config = System::getSystemConfiguration();
        if ((int)$config['disable_advanced_search_case_title_fulltext'] === 0) {
            // Cleaning "fulltext" operators in order to avoid unexpected results
            $search = str_replace(
                ['-', '+', '<', '>', '(', ')', '~', '*', '"'],
                ['', '', '', '', '', '', '', '', ''],
                $search
            );

            // Build the "fulltext" expression
            $search = '+"' . preg_replace('/\s+/', '" +"', addslashes($search)) . '"';
            // Searching using "fulltext" index
            $query->whereRaw("MATCH(APP_DELEGATION.DEL_TITLE) AGAINST('{$search}' IN BOOLEAN MODE)");
        } else {
            // Searching using "like" operator
            $query->where('APP_DELEGATION.DEL_TITLE', 'LIKE', "%{$search}%");
        }

        return $query;
    }

    /**
     * Scope a query to only include specific cases
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $cases
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSpecificCases($query, array $cases)
    {
        return $query->whereIn('APP_DELEGATION.APP_NUMBER', $cases);
    }

    /**
     * Scope a query to only include cases from a range
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $from
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCasesFrom($query, int $from)
    {
        return $query->where('APP_DELEGATION.APP_NUMBER', '>=', $from);
    }

    /**
     * Scope a query to only include cases from a range
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $to
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCasesTo($query, int $to)
    {
        return $query->where('APP_DELEGATION.APP_NUMBER', '<=', $to);
    }

    /**
     * Scope for query to get the positive cases for avoid the web entry
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePositiveCases($query)
    {
        return $query->where('APP_DELEGATION.APP_NUMBER', '>', 0);
    }

    /**
     * Scope more than one range of cases
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array $rangeCases
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRangeOfCases($query, array $rangeCases)
    {
        $query->where(function ($query) use ($rangeCases) {
            foreach ($rangeCases as $fromTo) {
                $fromTo = explode("-", $fromTo);
                if (count($fromTo) === 2) {
                    $from = $fromTo[0];
                    $to = $fromTo[1];
                    if ($to > $from) {
                        $query->orWhere(function ($query) use ($from, $to) {
                            $query->casesFrom($from)->casesTo($to);
                        });
                    }
                }
            }
        });
    }

    /**
     * Scope more than one range of cases
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array $cases
     * @param  array $rangeCases
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCasesOrRangeOfCases($query, array $cases, array $rangeCases)
    {
        $query->where(function ($query) use ($cases, $rangeCases) {
            // Get the cases related to the task self service
            $query->specificCases($cases);
            foreach ($rangeCases as $fromTo) {
                $fromTo = explode("-", $fromTo);
                if (count($fromTo) === 2) {
                    $from = $fromTo[0];
                    $to = $fromTo[1];
                    if ($to > $from) {
                        $query->orWhere(function ($query) use ($from, $to) {
                            $query->casesFrom($from)->casesTo($to);
                        });
                    }
                }
            }
        });
    }

    /**
     * Scope a query to get the delegations from a case by APP_UID
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $appUid
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAppUid($query, $appUid)
    {
        return $query->where('APP_DELEGATION.APP_UID', '=', $appUid);
    }

    /**
     * Scope a query to get the last thread
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLastThread($query)
    {
        return $query->where('APP_DELEGATION.DEL_LAST_INDEX', '=', 1);
    }

    /**
     * Scope a query to only include threads without user
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNoUserInThread($query)
    {
        return $query->where('APP_DELEGATION.USR_ID', '=', 0);
    }

    /**
     * Scope a query to only include specific tasks
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array $tasks
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTasksIn($query, array $tasks)
    {
        return $query->whereIn('APP_DELEGATION.TAS_ID', $tasks);
    }

    /**
     * Scope a query to only include specific cases by APP_UID
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $cases
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSpecificCasesByUid($query, array $cases)
    {
        return $query->whereIn('APP_DELEGATION.APP_UID', $cases);
    }

    /**
     * Scope a query to only include specific user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUserId($query, int $user)
    {
        return $query->where('APP_DELEGATION.USR_ID', '=', $user);
    }

    /**
     * Scope a query to only include threads without user
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutUserId($query)
    {
        return $query->where('APP_DELEGATION.USR_ID', '=', 0);
    }

    /**
     * Scope a query to only include specific process
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $process
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcessId($query, $process)
    {
        return $query->where('APP_DELEGATION.PRO_ID', $process);
    }

    /**
     * Scope a query to only include a specific task
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $task
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTask($query, int $task)
    {
        return $query->where('APP_DELEGATION.TAS_ID', '=', $task);
    }

    /**
     * Scope a query to only include specific tasks
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array $tasks
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSpecificTasks($query, array $tasks)
    {
        return $query->whereIn('APP_DELEGATION.TAS_ID', $tasks);
    }

    /**
     * Scope a join with task and include a specific task assign type:
     * BALANCED|MANUAL|EVALUATE|REPORT_TO|SELF_SERVICE|STATIC_MI|CANCEL_MI|MULTIPLE_INSTANCE|MULTIPLE_INSTANCE_VALUE_BASED
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $taskType
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTaskAssignType($query, $taskType = 'SELF_SERVICE')
    {
        $query->leftJoin('TASK', function ($join) use ($taskType) {
            $join->on('APP_DELEGATION.TAS_ID', '=', 'TASK.TAS_ID')
                ->where('TASK.TAS_ASSIGN_TYPE', '=', $taskType);
        });

        return $query;
    }

    /**
     * Scope a join with task and exclude a specific task assign type:
     * NORMAL|ADHOC|SUBPROCESS|HIDDEN|GATEWAYTOGATEWAY|WEBENTRYEVENT|END-MESSAGE-EVENT|START-MESSAGE-EVENT|
     * INTERMEDIATE-THROW-MESSAGE-EVENT|INTERMEDIATE-CATCH-MESSAGE-EVENT|SCRIPT-TASK|START-TIMER-EVENT|
     * INTERMEDIATE-CATCH-TIMER-EVENT|END-EMAIL-EVENT|INTERMEDIATE-THROW-EMAIL-EVENT|SERVICE-TASK
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $taskTypes
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExcludeTaskTypes($query, array $taskTypes)
    {
        $query->leftJoin('TASK', function ($join) use ($taskTypes) {
            $join->on('APP_DELEGATION.TAS_ID', '=', 'TASK.TAS_ID')
                ->whereNotIn('TASK.TAS_TYPE', $taskTypes);
        });
    }

    /**
     * Scope a join with task and include a specific task assign type:
     * NORMAL|ADHOC|SUBPROCESS|HIDDEN|GATEWAYTOGATEWAY|WEBENTRYEVENT|END-MESSAGE-EVENT|START-MESSAGE-EVENT|
     * INTERMEDIATE-THROW-MESSAGE-EVENT|INTERMEDIATE-CATCH-MESSAGE-EVENT|SCRIPT-TASK|START-TIMER-EVENT|
     * INTERMEDIATE-CATCH-TIMER-EVENT|END-EMAIL-EVENT|INTERMEDIATE-THROW-EMAIL-EVENT|SERVICE-TASK
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $taskTypes
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSpecificTaskTypes($query, array $taskTypes)
    {
        $query->leftJoin('TASK', function ($join) use ($taskTypes) {
            $join->on('APP_DELEGATION.TAS_ID', '=', 'TASK.TAS_ID')
                ->whereIn('TASK.TAS_TYPE', $taskTypes);
        });

        return $query;
    }

    /**
     * Scope a join with APPLICATION with specific app status
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param integer $statusId
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAppStatusId($query, $statusId = 2)
    {
        $query->leftJoin('APPLICATION', function ($join) use ($statusId) {
            $join->on('APP_DELEGATION.APP_NUMBER', '=', 'APPLICATION.APP_NUMBER')
                ->where('APPLICATION.APP_STATUS_ID', $statusId);
        });

        return $query;
    }

    /**
     * Scope the Process is in list
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $processes
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcessInList($query, array $processes)
    {
        return $query->whereIn('APP_DELEGATION.PRO_ID', $processes);
    }

    /**
     * Scope the Inbox cases
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInbox($query, $userId)
    {
        // This scope is for the join with the APP_DELEGATION table
        $query->joinApplication();
        // Filter the status to_do
        $query->status(Application::STATUS_TODO);
        // Scope that return the results for an specific user
        $query->userId($userId);
        // Scope that establish that the DEL_THREAD_STATUS must be OPEN
        $query->threadOpen();

        return $query;
    }

    /**
     * Scope a self service cases
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $usrUid
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSelfService($query, string $usrUid)
    {
        // Add Join with task filtering only the type self-service
        $query->taskAssignType('SELF_SERVICE');
        // Filtering the open threads and without users
        $query->threadOpen()->withoutUserId();
        // Filtering the cases unassigned that the user can view
        $this->casesUnassigned($query, $usrUid);

        return $query;
    }

    /**
     * Scope a draft cases
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDraft($query, $user)
    {
        // Add join for application, for get the case title when the case status is DRAFT
        $query->joinApplication();
        $query->status(Application::STATUS_DRAFT);
        $query->threadOpen();
        // Case assigned to the user
        $query->userId($user);

        return $query;
    }

    /**
     * Scope a participated cases
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeParticipated($query, $user)
    {
        // Scope to set the user
        $query->userId($user);

        return $query;
    }

    /**
     * Scope process category id
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $category
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCategoryId($query, int $category)
    {
        return $query->where('PROCESS.CATEGORY_ID', $category);
    }

    /**
     * Scope top ten
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param string $order
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTopTen($query, $column, $order)
    {
        return $query->orderBy($column, $order)->limit(10);
    }

    /**
     * Scope join with delegation for get the previous index
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinPreviousIndex($query)
    {
        $query->leftJoin('APP_DELEGATION AS AD', function ($leftJoin) {
            $leftJoin->on('APP_DELEGATION.APP_NUMBER', '=', 'AD.APP_NUMBER')
                ->on('APP_DELEGATION.DEL_PREVIOUS', '=', 'AD.DEL_INDEX');
        });

        return $query;
    }

    /**
     * Scope a join with APPLICATION with specific app status
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $category
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinCategoryProcess($query, $category = '')
    {
        $query->leftJoin('PROCESS', function ($join) use ($category) {
            $join->on('APP_DELEGATION.PRO_ID', '=', 'PROCESS.PRO_ID');
            if ($category) {
                $join->where('PROCESS.PRO_CATEGORY', $category);
            }
        });

        return $query;
    }

    /**
     * Scope join with process
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinProcess($query)
    {
        $query->leftJoin('PROCESS', function ($leftJoin) {
            $leftJoin->on('APP_DELEGATION.PRO_ID', '=', 'PROCESS.PRO_ID');
        });

        return $query;
    }

    /**
     * Scope join with task
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinTask($query)
    {
        $query->leftJoin('TASK', function ($leftJoin) {
            $leftJoin->on('APP_DELEGATION.TAS_ID', '=', 'TASK.TAS_ID');
        });

        return $query;
    }

    /**
     * Scope join with user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinUser($query)
    {
        $query->leftJoin('USERS', function ($leftJoin) {
            $leftJoin->on('APP_DELEGATION.USR_ID', '=', 'USERS.USR_ID');
        });

        return $query;
    }

    /**
     * Scope join with application
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinApplication($query)
    {
        $query->leftJoin('APPLICATION', function ($leftJoin) {
            $leftJoin->on('APP_DELEGATION.APP_NUMBER', '=', 'APPLICATION.APP_NUMBER');
        });

        return $query;
    }

    /**
     * Scope join with AppDelay
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinAppDelay($query, $type = 'PAUSE')
    {
        $query->leftJoin('APP_DELAY', function ($leftJoin)  use ($type) {
            $leftJoin->on('APP_DELAY.APP_NUMBER', '=', 'APP_DELEGATION.APP_NUMBER')
                ->on('APP_DELEGATION.DEL_INDEX', '=', 'APP_DELAY.APP_DEL_INDEX');
        });
        $query->where('APP_DELAY.APP_DISABLE_ACTION_USER', '=', '0');
        $query->where('APP_DELAY.APP_TYPE', '=', $type);

        return $query;
    }

    /**
     * Scope join with AppDelay and users
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinAppDelayUsers($query, int $userId)
    {
        $query->leftJoin('USERS', function ($leftJoin) {
            $leftJoin->on('APP_DELAY.APP_DELEGATION_USER', '=', 'USERS.USR_UID');
        });
        // Add filter related to the user
        if (!empty($userId)) {
            $query->where('USERS.USR_ID', $userId);
        }

        return $query;
    }

    /**
     * Scope paused cases list
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaused($query, int $userId)
    {
        // This scope is for the join with the APP_DELAY and considerate only the PAUSE
        $query->joinAppDelay('PAUSE');
        // This scope is for the join with the APP_DELAY with USERS table
        $query->joinAppDelayUsers($userId);
        // This scope is for the join with the APP_DELEGATION table
        $query->joinApplication();

        return $query;
    }

    /**
     * Scope sendBy.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $usrId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSendBy($query, int $usrId)
    {
        $query->where(DB::raw($usrId), function ($sql) {
            $sql->from('APP_DELEGATION AS B')
                ->select('B.USR_ID')
                ->where('B.APP_NUMBER', '=', DB::raw('APP_DELEGATION.APP_NUMBER'))
                ->where('B.DEL_INDEX', '=', DB::raw('APP_DELEGATION.DEL_PREVIOUS'))
                ->limit(1);
        });
        return $query;
    }

    /**
     * Scope a participated user in the case
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeParticipatedUser($query, $user)
    {
        // Scope to set the user who participated in the case
        $query->whereIn('APP_DELEGATION.APP_NUMBER', function ($query) use ($user) {
            $query->select('APP_NUMBER')->from('APP_DELEGATION')
                ->where('USR_ID', $user)->distinct();
        });

        return $query;
    }

    /**
     * Scope the Inbox cases no matter the user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInboxMetrics($query)
    {
        $query->joinApplication();
        $query->status(Application::STATUS_TODO);
        $query->threadOpen();
        return $query;
    }

    /**
     * Scope a draft cases no matter the user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDraftMetrics($query)
    {
        $query->joinApplication();
        $query->status(Application::STATUS_DRAFT);
        $query->threadOpen();
        return $query;
    }

    /**
     * Scope paused cases list no matter the user
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePausedMetrics($query)
    {
        $query->joinAppDelay('PAUSE');
        $query->joinApplication();
        return $query;
    }

    /**
     * Scope a self service cases no matter the user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSelfServiceMetrics($query)
    {
        $query->taskAssignType('SELF_SERVICE');
        $query->threadOpen()->withoutUserId();
        return $query;
    }

    /**
     * Get specific cases unassigned that the user can view
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $usrUid
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function casesUnassigned(&$query, string $usrUid)
    {
        // Get the task self services related to the user
        $taskSelfService = TaskUser::getSelfServicePerUser($usrUid);
        // Get the task self services value based related to the user
        $selfServiceValueBased = AppAssignSelfServiceValue::getSelfServiceCasesByEvaluatePerUser($usrUid);
        // Get the cases unassigned
        if (!empty($selfServiceValueBased)) {
            $query->where(function ($query) use ($selfServiceValueBased, $taskSelfService) {
                // Get the cases related to the task self service
                $query->specificTasks($taskSelfService);
                foreach ($selfServiceValueBased as $case) {
                    // Get the cases related to the task self service value based
                    $query->orWhere(function ($query) use ($case) {
                        $query->case($case['APP_NUMBER'])->index($case['DEL_INDEX'])->task($case['TAS_ID']);
                    });
                }
            });
        } else {
            // Get the cases related to the task self service
            $query->specificTasks($taskSelfService);
        }

        return $query;
    }

    /**
     * Searches for delegations which match certain criteria
     *
     * The query is related to advanced search with different filters
     * We can search by process, status of case, category of process, users, delegate date from and to
     *
     * @param integer $userId The USR_ID to search for (Note, this is no longer the USR_UID)
     * @param integer $start for the pagination
     * @param integer $limit for the pagination
     * @param string $search
     * @param integer $process the pro_id
     * @param integer $status of the case
     * @param string $dir if the order is DESC or ASC
     * @param string $sort name of column by sort, can be:
     *        [APP_NUMBER, APP_TITLE, APP_PRO_TITLE, APP_TAS_TITLE, APP_CURRENT_USER, APP_UPDATE_DATE, DEL_DELEGATE_DATE, DEL_TASK_DUE_DATE, APP_STATUS_LABEL]
     * @param string $category uid for the process
     * @param date $dateFrom
     * @param date $dateTo
     * @param string $filterBy name of column for a specific search, can be: [APP_NUMBER, APP_TITLE, TAS_TITLE]
     * @return array $result result of the query
     */

    public static function search(
        $userId = null,
        // Default pagination values
        $start = 0,
        $limit = 25,
        $search = null,
        $process = null,
        $status = null,
        $dir = null,
        $sort = null,
        $category = null,
        $dateFrom = null,
        $dateTo = null,
        $filterBy = 'APP_TITLE'
    ) {
        $search = trim($search);

        // Start the query builder, selecting our base attributes
        $selectColumns = [
            'APPLICATION.APP_NUMBER',
            'APPLICATION.APP_UID',
            'APPLICATION.APP_STATUS',
            'APPLICATION.APP_STATUS AS APP_STATUS_LABEL',
            'APPLICATION.PRO_UID',
            'APPLICATION.APP_CREATE_DATE',
            'APPLICATION.APP_FINISH_DATE',
            'APPLICATION.APP_UPDATE_DATE',
            'APP_DELEGATION.DEL_TITLE AS APP_TITLE',
            'APP_DELEGATION.USR_UID',
            'APP_DELEGATION.TAS_UID',
            'APP_DELEGATION.USR_ID',
            'APP_DELEGATION.PRO_ID',
            'APP_DELEGATION.DEL_INDEX',
            'APP_DELEGATION.DEL_LAST_INDEX',
            'APP_DELEGATION.DEL_DELEGATE_DATE',
            'APP_DELEGATION.DEL_INIT_DATE',
            'APP_DELEGATION.DEL_FINISH_DATE',
            'APP_DELEGATION.DEL_TASK_DUE_DATE',
            'APP_DELEGATION.DEL_RISK_DATE',
            'APP_DELEGATION.DEL_THREAD_STATUS',
            'APP_DELEGATION.DEL_PRIORITY',
            'APP_DELEGATION.DEL_DURATION',
            'APP_DELEGATION.DEL_QUEUE_DURATION',
            'APP_DELEGATION.DEL_STARTED',
            'APP_DELEGATION.DEL_DELAY_DURATION',
            'APP_DELEGATION.DEL_FINISHED',
            'APP_DELEGATION.DEL_DELAYED',
            'APP_DELEGATION.DEL_DELAY_DURATION',
            'TASK.TAS_TITLE AS APP_TAS_TITLE',
            'TASK.TAS_TYPE AS APP_TAS_TYPE',
        ];
        $query = DB::table('APP_DELEGATION')->select(DB::raw(implode(',', $selectColumns)));

        // Add join for task, filtering for task title if needed
        // It doesn't make sense for us to search for any delegations that match tasks that are events or web entry
        $query->join('TASK', function ($join) use ($filterBy, $search) {
            $join->on('APP_DELEGATION.TAS_ID', '=', 'TASK.TAS_ID')
                ->whereNotIn('TASK.TAS_TYPE', [
                    'WEBENTRYEVENT',
                    'END-MESSAGE-EVENT',
                    'START-MESSAGE-EVENT',
                    'INTERMEDIATE-THROW',
                ]);
            if ($filterBy == 'TAS_TITLE' && $search) {
                $join->where('TASK.TAS_TITLE', 'LIKE', "%${search}%");
            }
        });

        // Add join for application, taking care of status and filtering if necessary
        $query->join('APPLICATION', function ($join) use ($filterBy, $search, $status, $query) {
            $join->on('APP_DELEGATION.APP_NUMBER', '=', 'APPLICATION.APP_NUMBER');
            if ($filterBy == 'APP_TITLE' && $search) {
                $config = System::getSystemConfiguration();
                if ((int)$config['disable_advanced_search_case_title_fulltext'] === 0) {
                    // Cleaning "fulltext" operators in order to avoid unexpected results
                    $search = str_replace(
                        ['-', '+', '<', '>', '(', ')', '~', '*', '"'],
                        ['', '', '', '', '', '', '', '', ''],
                        $search
                    );

                    // Build the "fulltext" expression
                    $search = '+"' . preg_replace('/\s+/', '" +"', addslashes($search)) . '"';
                    // Searching using "fulltext" index
                    $join->whereRaw("MATCH(APP_DELEGATION.DEL_TITLE) AGAINST('{$search}' IN BOOLEAN MODE)");
                } else {
                    // Searching using "like" operator
                    $join->where('APP_DELEGATION.DEL_TITLE', 'LIKE', "%${search}%");
                }
            }
            // Based on the below, we can further limit the join so that we have a smaller data set based on join criteria
            switch ($status) {
                case 1: //DRAFT
                    $join->where('APPLICATION.APP_STATUS_ID', 1);
                    break;
                case 2: //TO_DO
                    $join->where('APPLICATION.APP_STATUS_ID', 2);
                    break;
                case 3: //COMPLETED
                    $join->where('APPLICATION.APP_STATUS_ID', 3);
                    break;
                case 4: //CANCELLED
                    $join->where('APPLICATION.APP_STATUS_ID', 4);
                    break;
                case "PAUSED":
                    $join->where('APPLICATION.APP_STATUS', 'TO_DO');
                    break;
                default: //All status
                    // Don't do anything here, we'll need to do the more advanced where below
            }
        });
        // Add join for process, but only for certain scenarios such as category or process
        if ($category || $process || $sort == 'APP_PRO_TITLE') {
            $query->join('PROCESS', function ($join) use ($category) {
                $join->on('APP_DELEGATION.PRO_ID', '=', 'PROCESS.PRO_ID');
                if ($category) {
                    $join->where('PROCESS.PRO_CATEGORY', $category);
                }
            });
        }

        // Add join for user, but only for certain scenarios as sorting
        if ($sort == 'APP_CURRENT_USER') {
            $query->join('USERS', function ($join) use ($userId) {
                $join->on('APP_DELEGATION.USR_ID', '=', 'USERS.USR_ID');
            });
        }

        // Search for specified user
        if ($userId) {
            $query->where('APP_DELEGATION.USR_ID', $userId);
        }

        // Search for specified process
        if ($process) {
            $query->where('APP_DELEGATION.PRO_ID', $process);
        }

        // Search for an app/case number
        if ($filterBy == 'APP_NUMBER' && $search) {
            $query->where('APP_DELEGATION.APP_NUMBER', '=', $search);
        }

        // Date range filter
        if (!empty($dateFrom)) {
            $query->where('APP_DELEGATION.DEL_DELEGATE_DATE', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $dateTo = $dateTo . " 23:59:59";
            // This is inclusive
            $query->where('APP_DELEGATION.DEL_DELEGATE_DATE', '<=', $dateTo);
        }

        // Status Filter
        // This is tricky, the below behavior is combined with the application join behavior above
        switch ($status) {
            case 1: //DRAFT
                $query->where('APP_DELEGATION.DEL_THREAD_STATUS', 'OPEN');
                break;
            case 2: //TO_DO
                $query->where('APP_DELEGATION.DEL_THREAD_STATUS', 'OPEN');
                break;
            case 3: //COMPLETED
                $query->where('APP_DELEGATION.DEL_LAST_INDEX', 1);
                break;
            case 4: //CANCELLED
                $query->where('APP_DELEGATION.DEL_LAST_INDEX', 1);
                break;
            case "PAUSED":
                // Do nothing, as the app status check for TO_DO is performed in the join above
                break;
            default: //All statuses.
                $query->where(function ($query) {
                    // Check to see if thread status is open
                    $query->where('APP_DELEGATION.DEL_THREAD_STATUS', 'OPEN')
                        ->orWhere(function ($query) {
                            // Or, we make sure if the thread is closed, and it's the last delegation, and if the app is completed or cancelled
                            $query->where('APP_DELEGATION.DEL_THREAD_STATUS', 'CLOSED')
                                ->where('APP_DELEGATION.DEL_LAST_INDEX', 1)
                                ->whereIn('APPLICATION.APP_STATUS_ID', [3, 4]);
                        });
                });
                break;
        }

        // Add any sort if needed
        if ($sort) {
            switch ($sort) {
                case 'APP_NUMBER':
                    $query->orderBy('APP_DELEGATION.APP_NUMBER', $dir);
                    break;
                case 'APP_PRO_TITLE':
                    // We can do this because we joined the process table if sorting by it
                    $query->orderBy('PROCESS.PRO_TITLE', $dir);
                    break;
                case 'APP_TAS_TITLE':
                    $query->orderBy('TASK.TAS_TITLE', $dir);
                    break;
                case 'APP_CURRENT_USER':
                    // We can do this because we joined the user table if sorting by it
                    $query->orderBy('USERS.USR_LASTNAME', $dir);
                    $query->orderBy('USERS.USR_FIRSTNAME', $dir);
                    break;
                default:
                    $query->orderBy($sort, $dir);
            }
        }

        // Add pagination to the query
        $query = $query->offset($start)
            ->limit($limit);

        // Fetch results and transform to a laravel collection
        $results = $query->get();

        // Transform with additional data
        $priorities = ['1' => 'VL', '2' => 'L', '3' => 'N', '4' => 'H', '5' => 'VH'];
        $results->transform(function ($item, $key) use ($priorities) {
            // Convert to an array as our results must be an array
            $item = json_decode(json_encode($item), true);
            // If it's assigned, fetch the user
            if ($item['USR_ID']) {
                $user = User::where('USR_ID', $item['USR_ID'])->first();
            } else {
                $user = null;
            }
            $process = Process::where('PRO_ID', $item['PRO_ID'])->first();

            // Rewrite priority string
            if ($item['DEL_PRIORITY']) {
                $item['DEL_PRIORITY'] = G::LoadTranslation("ID_PRIORITY_{$priorities[$item['DEL_PRIORITY']]}");
            }

            // Merge in desired application data
            if ($item['APP_STATUS']) {
                $item['APP_STATUS_LABEL'] = G::LoadTranslation("ID_${item['APP_STATUS']}");
            } else {
                $item['APP_STATUS_LABEL'] = $item['APP_STATUS'];
            }

            // Merge in desired process data
            // Handle situation where the process might not be in the system anymore
            $item['APP_PRO_TITLE'] = $process ? $process->PRO_TITLE : '';

            // Merge in desired user data
            $item['USR_LASTNAME'] = $user ? $user->USR_LASTNAME : '';
            $item['USR_FIRSTNAME'] = $user ? $user->USR_FIRSTNAME : '';
            $item['USR_USERNAME'] = $user ? $user->USR_USERNAME : '';

            //@todo: this section needs to use 'User Name Display Format', currently in the extJs is defined this
            $item["APP_CURRENT_USER"] = $item["USR_LASTNAME"] . ' ' . $item["USR_FIRSTNAME"];

            $item["APPDELCR_APP_TAS_TITLE"] = '';

            $item["USRCR_USR_UID"] = $item["USR_UID"];
            $item["USRCR_USR_FIRSTNAME"] = $item["USR_FIRSTNAME"];
            $item["USRCR_USR_LASTNAME"] = $item["USR_LASTNAME"];
            $item["USRCR_USR_USERNAME"] = $item["USR_USERNAME"];
            $item["APP_OVERDUE_PERCENTAGE"] = '';

            return $item;
        });

        // Remove any empty erroenous data
        $results = $results->filter();

        // Bundle into response array
        $response = [
            // Fake totalCount to show pagination
            'totalCount' => $start + $limit + 1,
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'data' => $results->values()->toArray(),
        ];

        return $response;
    }

    /**
     * Get participation information for a case
     *
     * @param string $appUid
     * @return array
     *
     * @see \ProcessMaker\BusinessModel\Cases::getStatusInfo()
     */
    public static function getParticipatedInfo($appUid)
    {
        // Build the query
        $query = Delegation::query()->select([
            'APP_UID',
            'DEL_INDEX',
            'PRO_UID'
        ]);
        $query->appUid($appUid);
        $query->orderBy('DEL_INDEX', 'ASC');

        // Fetch results
        $results = $query->get();

        // Initialize the array to return
        $arrayData = [];

        // If the collection have at least one item, build the main array to return
        if ($results->count() > 0) {
            // Get the first item
            $first = $results->first();

            // Build the main array to return
            $arrayData = [
                'APP_STATUS' => 'PARTICIPATED',
                // Value hardcoded because we need to return the same structure previously sent
                'DEL_INDEX' => [],
                // Initialize this item like an array
                'PRO_UID' => $first->PRO_UID
            ];

            // Populate the DEL_INDEX key with the values of the items collected
            $results->each(function ($item) use (&$arrayData) {
                $arrayData['DEL_INDEX'][] = $item->DEL_INDEX;
            });
        }

        return $arrayData;
    }

    /**
     * Get the self-services query by user
     *
     * @param string $usrUid
     * @param bool $count
     * @param array $selectedColumns
     * @param string $categoryUid
     * @param string $processUid
     * @param string $textToSearch
     * @param string $sort
     * @param string $dir
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function getSelfServiceQuery(
        $usrUid,
        $count = false,
        $selectedColumns = ['APP_DELEGATION.APP_NUMBER', 'APP_DELEGATION.DEL_INDEX'],
        $categoryUid = null,
        $processUid = null,
        $textToSearch = null,
        $sort = null,
        $dir = null
    ) {
        // Set the 'usrUid' property to preserve
        Delegation::$usrUid = $usrUid;

        // Get and build the groups parameter related to the user
        $groups = GroupUser::getGroups($usrUid);
        $groups = array_map(function ($value) {
            return "'" . $value['GRP_ID'] . "'";
        }, $groups);

        // Add dummy value to avoid syntax error in complex join
        $groups[] = "'-1'";

        // Set the 'groups' property to preserve
        Delegation::$groups = $groups;

        // Add an extra column with alias if is needed to join with the previous delegation
        if (array_search('APP_DELEGATION.DEL_PREVIOUS', $selectedColumns) !== false) {
            $selectedColumns[] = 'ADP.USR_ID';
        }

        // Start the first query
        $query1 = Delegation::query()->select($selectedColumns);

        // Add join clause with the previous APP_DELEGATION record if required
        if (array_search('APP_DELEGATION.DEL_PREVIOUS', $selectedColumns) !== false) {
            $query1->join('APP_DELEGATION AS ADP', function ($join) {
                $join->on('APP_DELEGATION.APP_NUMBER', '=', 'ADP.APP_NUMBER');
                $join->on('APP_DELEGATION.DEL_PREVIOUS', '=', 'ADP.DEL_INDEX');
            });
        }

        // Add the join clause with TASK table
        $query1->join('TASK', function ($join) {
            // Build partial plain query for a complex Join, because Eloquent doesn't support this type of Join
            $complexJoin = "
                ((`APP_DELEGATION`.`APP_NUMBER`, `APP_DELEGATION`.`DEL_INDEX`, `APP_DELEGATION`.`TAS_ID`) IN (
                    SELECT
                        `APP_ASSIGN_SELF_SERVICE_VALUE`.`APP_NUMBER`,
                        `APP_ASSIGN_SELF_SERVICE_VALUE`.`DEL_INDEX`,
                        `APP_ASSIGN_SELF_SERVICE_VALUE`.`TAS_ID`
                    FROM
                        `APP_ASSIGN_SELF_SERVICE_VALUE`
                    INNER JOIN `APP_ASSIGN_SELF_SERVICE_VALUE_GROUP` ON
                        `APP_ASSIGN_SELF_SERVICE_VALUE`.`ID` = `APP_ASSIGN_SELF_SERVICE_VALUE_GROUP`.`ID`
                    WHERE (
                        `APP_ASSIGN_SELF_SERVICE_VALUE_GROUP`.`GRP_UID` = '%s' OR (
                        `APP_ASSIGN_SELF_SERVICE_VALUE_GROUP`.`ASSIGNEE_ID` IN (%s) AND
                        `APP_ASSIGN_SELF_SERVICE_VALUE_GROUP`.`ASSIGNEE_TYPE` = '2')
                    )
                ))";
            $groups = implode(',', Delegation::$groups);
            $complexJoin = sprintf($complexJoin, Delegation::$usrUid, $groups);

            // Add joins
            $join->on('APP_DELEGATION.TAS_ID', '=', 'TASK.TAS_ID');
            $join->on('TASK.TAS_ASSIGN_TYPE', '=', DB::raw("'SELF_SERVICE'"));
            $join->on('APP_DELEGATION.DEL_THREAD_STATUS', '=', DB::raw("'OPEN'"));
            $join->on('APP_DELEGATION.USR_ID', '=', DB::raw("'0'"))->whereRaw($complexJoin);
        });

        // Add join clause with APPLICATION table if required
        if (array_search('APP_DELEGATION.DEL_TITLE AS APP_TITLE', $selectedColumns) !== false || array_search('APPLICATION.APP_TITLE', $selectedColumns) !== false || !empty($textToSearch) || $sort == 'APP_TITLE') {
            $query1->join('APPLICATION', function ($join) {
                $join->on('APP_DELEGATION.APP_NUMBER', '=', 'APPLICATION.APP_NUMBER');
            });
        }

        // Add join clause with PROCESS table if required
        if (array_search('PROCESS.PRO_TITLE', $selectedColumns) !== false || !empty($categoryUid) || !empty($processUid) || !empty($textToSearch) || $sort == 'PRO_TITLE') {
            $query1->join('PROCESS', function ($join) use ($categoryUid, $processUid) {
                $join->on('APP_DELEGATION.PRO_ID', '=', 'PROCESS.PRO_ID');
                if (!empty($categoryUid)) {
                    $join->where('PROCESS.PRO_CATEGORY', $categoryUid);
                }
                if (!empty($processUid)) {
                    $join->where('PROCESS.PRO_UID', $processUid);
                }
            });
        }

        // Build where clause for the text to search
        if (!empty($textToSearch)) {
            $query1->where('APP_DELEGATION.DEL_TITLE', 'LIKE', "%$textToSearch%")
                ->orWhere('TASK.TAS_TITLE', 'LIKE', "%$textToSearch%")
                ->orWhere('PROCESS.PRO_TITLE', 'LIKE', "%$textToSearch%");
        }

        // Clean static properties
        Delegation::$usrUid = '';
        Delegation::$groups = [];

        // Get self services tasks related to the user
        $selfServiceTasks = TaskUser::getSelfServicePerUser($usrUid);

        if (!empty($selfServiceTasks)) {
            // Start the second query
            $query2 = Delegation::query()->select($selectedColumns);
            $query2->tasksIn($selfServiceTasks);
            $query2->threadOpen();
            $query2->noUserInThread();

            // Add join clause with the previous APP_DELEGATION record if required
            if (array_search('APP_DELEGATION.DEL_PREVIOUS', $selectedColumns) !== false) {
                $query2->join('APP_DELEGATION AS ADP', function ($join) {
                    $join->on('APP_DELEGATION.APP_NUMBER', '=', 'ADP.APP_NUMBER');
                    $join->on('APP_DELEGATION.DEL_PREVIOUS', '=', 'ADP.DEL_INDEX');
                });
            }

            // Add the join clause with TASK table if required
            if (array_search('TASK.TAS_TITLE', $selectedColumns) !== false || !empty($textToSearch) || $sort == 'TAS_TITLE') {
                $query2->join('TASK', function ($join) {
                    $join->on('APP_DELEGATION.TAS_ID', '=', 'TASK.TAS_ID');
                });
            }
            // Add join clause with APPLICATION table if required
            if (array_search('APP_DELEGATION.DEL_TITLE AS APP_TITLE', $selectedColumns) !== false || !empty($textToSearch) || $sort == 'APP_TITLE') {
                $query2->join('APPLICATION', function ($join) {
                    $join->on('APP_DELEGATION.APP_NUMBER', '=', 'APPLICATION.APP_NUMBER');
                });
            }

            // Add join clause with PROCESS table if required
            if (array_search('PROCESS.PRO_TITLE', $selectedColumns) !== false || !empty($categoryUid) || !empty($processUid) || !empty($textToSearch) || $sort == 'PRO_TITLE') {
                $query2->join('PROCESS', function ($join) use ($categoryUid, $processUid) {
                    $join->on('APP_DELEGATION.PRO_ID', '=', 'PROCESS.PRO_ID');
                    if (!empty($categoryUid)) {
                        $join->where('PROCESS.PRO_CATEGORY', $categoryUid);
                    }
                    if (!empty($processUid)) {
                        $join->where('PROCESS.PRO_UID', $processUid);
                    }
                });
            }

            // Build where clause for the text to search
            if (!empty($textToSearch)) {
                $query2->where('APP_DELEGATION.DEL_TITLE', 'LIKE', "%$textToSearch%")
                    ->orWhere('TASK.TAS_TITLE', 'LIKE', "%$textToSearch%")
                    ->orWhere('PROCESS.PRO_TITLE', 'LIKE', "%$textToSearch%");
            }

            // Build the complex query that uses "UNION DISTINCT" clause
            $query = sprintf(
                'select '  . ($count ? 'count(*) as aggregate' : '*') .
                    ' from ((%s) union distinct (%s)) self_service_cases' . (!empty($sort) && !empty($dir) ? ' ORDER BY %s %s' : ''),
                toSqlWithBindings($query1),
                toSqlWithBindings($query2),
                $sort,
                $dir
            );

            return $query;
        } else {
            if (!empty($sort) && !empty($dir)) {
                $query1->orderBy($sort, $dir);
            }
            return $query1;
        }
    }

    /**
     * Get the self-services cases by user
     *
     * @param string $usrUid
     * @param array $selectedColumns
     * @param string $categoryUid
     * @param string $processUid
     * @param string $textToSearch
     * @param string $sort
     * @param string $dir
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public static function getSelfService(
        $usrUid,
        $selectedColumns = ['APP_DELEGATION.APP_NUMBER', 'APP_DELEGATION.DEL_INDEX'],
        $categoryUid = null,
        $processUid = null,
        $textToSearch = null,
        $sort = null,
        $dir = null,
        $offset = null,
        $limit = null
    ) {
        // Initializing the variable to return
        $data = [];

        // Get the query
        $query = self::getSelfServiceQuery($usrUid, false, $selectedColumns, $categoryUid, $processUid, $textToSearch, $sort, $dir);

        // Get data
        if (!is_string($query)) {
            // Set offset and limit if were sent
            if (!is_null($offset) && !is_null($limit)) {
                $query->offset($offset);
                $query->limit($limit);
            }
            $items = $query->get();
            $items->each(function ($item) use (&$data) {
                $data[] = $item->toArray();
            });
        } else {
            // Set offset and limit if were sent
            if (!is_null($offset) && !is_null($limit)) {
                $query .= " LIMIT {$offset}, {$limit}";
            }
            $items = DB::select($query);
            foreach ($items as $item) {
                $data[] = get_object_vars($item);
            }
        }

        // Return data
        return $data;
    }

    /**
     * Count the self-services cases by user
     *
     * @param string $usrUid
     * @param string $categoryUid
     * @param string $processUid
     * @param string $textToSearch
     *
     * @return integer
     */
    public static function countSelfService($usrUid, $categoryUid = null, $processUid = null, $textToSearch = null)
    {
        // Get the query
        $query = self::getSelfServiceQuery(
            $usrUid,
            true,
            ['APP_DELEGATION.APP_NUMBER', 'APP_DELEGATION.DEL_INDEX'],
            $categoryUid,
            $processUid,
            $textToSearch
        );

        // Get count value
        if (!is_string($query)) {
            $count = $query->count();
        } else {
            $result = DB::selectOne($query);
            $count = $result->aggregate;
        }
        // Return data
        return $count;
    }

    /**
     * This function get the current user related to the specific case and index
     *
     * @param int $appNumber, Case number
     * @param int $index, Index to review
     * @param string $status, The status of the thread
     *
     * @return string
     */
    public static function getCurrentUser(int $appNumber, int $index, $status = 'OPEN')
    {
        $query = Delegation::query()->select('USR_UID');
        $query->where('APP_NUMBER', $appNumber);
        $query->where('DEL_INDEX', $index);
        $query->where('DEL_THREAD_STATUS', $status);
        $query->first();
        $results = $query->get();

        $userUid = '';
        $results->each(function ($item, $key) use (&$userUid) {
            $userUid = $item->USR_UID;
        });

        return $userUid;
    }

    /**
     * Return the open thread related to the task
     *
     * @param int $appNumber, Case number
     * @param int $delIndex
     *
     * @return array
     */
    public static function getOpenThread(int $appNumber, int $delIndex)
    {
        $query = Delegation::query()->select();
        $query->where('DEL_THREAD_STATUS', 'OPEN');
        $query->where('DEL_FINISH_DATE', null);
        $query->where('APP_NUMBER', $appNumber);
        $query->where('DEL_INDEX', $delIndex);
        $results = $query->get();

        $arrayOpenThread = [];
        $results->each(function ($item, $key) use (&$arrayOpenThread) {
            $arrayOpenThread = $item->toArray();
        });

        return $arrayOpenThread;
    }

    /**
     * Return if the user has participation in the case
     *
     * @param string $appUid, Case key
     * @param string $userUid, User key
     *
     * @return boolean
     */
    public static function participation(string $appUid, string $userUid)
    {
        $query = Delegation::query()->select();
        $query->where('APP_UID', $appUid);
        $query->where('USR_UID', $userUid);
        $query->limit(1);

        return ($query->count() > 0);
    }

    /**
     * Return the task related to the thread
     *
     * @param int $appNumber
     * @param int $index
     *
     * @return array
     */
    public static function getThreadInfo(int $appNumber, int $index)
    {
        $query = Delegation::query()->select(['APP_NUMBER', 'TAS_UID', 'TAS_ID', 'DEL_PREVIOUS', 'DEL_TITLE', 'USR_ID']);
        $query->where('APP_NUMBER', $appNumber);
        $query->where('DEL_INDEX', $index);
        $query->limit(1);
        $result = $query->get()->toArray();

        return is_null($result) ? [] : head($result);
    }

    /**
     * Return the thread related to the specific task-index
     *
     * @param string $appUid
     * @param string $delIndex
     * @param string $tasUid
     * @param string $taskType
     *
     * @return array
     */
    public static function getDatesFromThread(string $appUid, string $delIndex, string $tasUid, string $taskType)
    {
        $query = Delegation::query()->select([
            'DEL_INIT_DATE',
            'DEL_DELEGATE_DATE',
            'DEL_FINISH_DATE',
            'DEL_RISK_DATE',
            'DEL_TASK_DUE_DATE'
        ]);
        $query->where('APP_UID', $appUid);
        $query->where('DEL_INDEX', $delIndex);
        $query->where('TAS_UID', $tasUid);
        $results = $query->get();

        $thread = [];
        $results->each(function ($item, $key) use (&$thread, $taskType) {
            $thread = $item->toArray();
            if (in_array($taskType, Task::$typesRunAutomatically)) {
                $startDate = $thread['DEL_DELEGATE_DATE'];
            } else {
                $startDate = $thread['DEL_INIT_DATE'];
            }
            $endDate = $thread['DEL_FINISH_DATE'];
            // Calculate the task-thread duration
            if (!empty($startDate) && !empty($endDate)) {
                $initDate = new DateTime($startDate);
                $finishDate = new DateTime($endDate);
                $diff = $initDate->diff($finishDate);
                $format = ' %a ' . G::LoadTranslation('ID_DAY_DAYS');
                $format .= ' %H ' . G::LoadTranslation('ID_HOUR_ABBREVIATE');
                $format .= ' %I ' . G::LoadTranslation('ID_MINUTE_ABBREVIATE');
                $format .= ' %S ' . G::LoadTranslation('ID_SECOND_ABBREVIATE');
                $thread['DEL_THREAD_DURATION'] = $diff->format($format);
            }
        });

        return $thread;
    }

    /**
     * Return the open thread related to the task
     *
     * @param int $appNumber
     * @param bool $onlyOpen
     *
     * @return array
     */
    public static function getPendingThreads(int $appNumber, $onlyOpen = true)
    {
        $query = Delegation::query()->select([
            'TASK.TAS_UID',
            'TASK.TAS_TITLE',
            'TASK.TAS_ASSIGN_TYPE',
            'APP_DELEGATION.DELEGATION_ID',
            'APP_DELEGATION.DEL_INDEX',
            'APP_DELEGATION.DEL_TITLE',
            'APP_DELEGATION.USR_ID',
            'APP_DELEGATION.DEL_THREAD_STATUS',
            'APP_DELEGATION.DEL_DELEGATE_DATE',
            'APP_DELEGATION.DEL_FINISH_DATE',
            'APP_DELEGATION.DEL_INIT_DATE',
            'APP_DELEGATION.DEL_TASK_DUE_DATE'
        ]);
        // Join with task
        $query->joinTask();
        // Get the open threads
        if ($onlyOpen) {
            $query->threadOpen();
        } else {
            $query->openAndPause();
        }
        // Related to the specific case number
        $query->case($appNumber);
        // Get the results
        $results = $query->get()->values()->toArray();

        return $results;
    }

    /**
     * Return the last thread created
     *
     * @param int $appNumber
     *
     * @return array
     */
    public static function getLastThread(int $appNumber)
    {
        $query = Delegation::query()->select([
            'TASK.TAS_UID',
            'TASK.TAS_TITLE',
            'TASK.TAS_ASSIGN_TYPE',
            'APP_DELEGATION.DELEGATION_ID',
            'APP_DELEGATION.DEL_INDEX',
            'APP_DELEGATION.DEL_TITLE',
            'APP_DELEGATION.USR_ID',
            'APP_DELEGATION.DEL_TASK_DUE_DATE'
        ]);
        // Join with task
        $query->joinTask();
        // Related to the specific case number
        $query->case($appNumber);
        // Get the last thread created
        $query->lastThread();
        // Get the results
        $results = $query->get()->values()->toArray();

        return $results;
    }

    /**
     * Get the thread title related to the delegation
     *
     * @param string $tasUid
     * @param int $appNumber
     * @param int $delIndexPrevious
     * @param array $caseData
     *
     * @return string
     */
    public static function getThreadTitle(string $tasUid, int $appNumber, int $delIndexPrevious, $caseData = [])
    {
        $cases = new Cases;
        if (!is_array($caseData)) {
            $r = $cases->unserializeData($caseData);
            if ($r !== false) {
                $caseData = $r;
            }
        }
        //
        $task = new Task();
        // Get case title defined
        $taskTitle = $task->taskCaseTitle($tasUid);
        // Get case description defined
        $taskDescription = $task->taskCaseDescription($tasUid);
        // If exist we will to replace the variables data
        if (!empty($taskTitle)) {
            $threadTitle = G::replaceDataField($taskTitle, $caseData, 'mysql', false);
        } else {
            // If is empty get the previous title
            if ($delIndexPrevious > 0) {
                $thread = self::getThreadInfo($appNumber, $delIndexPrevious);
                if (empty($thread['DEL_TITLE'])) {
                    $threadTitle = '# ' . $appNumber;
                } else {
                    $threadTitle = $thread['DEL_TITLE'];
                }
            } else {
                $threadTitle = '# ' . $appNumber;
            }
        }
        // If exist we will to replace the variables data
        $threadDescription = '';
        if (!empty($taskDescription)) {
            $threadDescription = G::replaceDataField($taskDescription, $caseData, 'mysql', false);
        }

        return [
            'title' => $threadTitle,
            'description' => $threadDescription
        ];
    }

    /**
     * Get the DEL_TITLE related to DELEGATION table
     * 
     * @param int $appNumber
     * @param int $delIndex
     * @return string
     */
    public static function getDeltitle($appNumber, $delIndex)
    {
        $query = Delegation::select(['DEL_TITLE'])->where('APP_NUMBER', $appNumber)->where('DEL_INDEX', $delIndex);
        $res = $query->first();
        return $res->DEL_TITLE;
    }

    /**
     * Return the pending task related to the appNumber
     *
     * @param int $appNumber
     *
     * @return array
     */
    public static function getPendingTask(int $appNumber)
    {
        $query = Delegation::query()->select([
            'TASK.TAS_TITLE', // Task
            'TASK.TAS_ASSIGN_TYPE', // Task assign rule
            'APP_DELEGATION.DEL_TITLE', // Thread title
            'APP_DELEGATION.DEL_THREAD_STATUS', // Thread status
            'APP_DELEGATION.USR_UID', // Current UserUid
            'APP_DELEGATION.USR_ID', // Current UserId
            'APP_DELEGATION.DEL_TASK_DUE_DATE', // Due Date
            // Additional column for other functionalities
            'APP_DELEGATION.APP_UID', // Case Uid for Open case
            'APP_DELEGATION.DEL_INDEX', // Del Index for Open case
            'APP_DELEGATION.PRO_UID', // Process Uid for Case notes
            'APP_DELEGATION.TAS_UID', // Task Uid for Case notes
        ]);
        // Join with task
        $query->joinTask();
        // Get the open and paused threads
        $query->openAndPause();
        // Related to the specific case number
        $query->case($appNumber);
        // Get the results
        $results = $query->get();
        $results->transform(function ($item) {
            $abs = new AbstractCases();
            $item['TAS_COLOR'] = $abs->getTaskColor($item['DEL_TASK_DUE_DATE'], $item['DEL_THREAD_STATUS']);
            $item['TAS_COLOR_LABEL'] = AbstractCases::TASK_COLORS[$item['TAS_COLOR']];
            $item['UNASSIGNED'] = ($item['TAS_ASSIGN_TYPE'] === 'SELF_SERVICE' ? true : false);
            $userInfo = User::getInformation($item['USR_ID']);
            $item['user_tooltip'] = $userInfo;
            $item['USR_USERNAME'] = !empty($userInfo['usr_username']) ? $userInfo['usr_username'] : '';
            $item['USR_LASTNAME'] = !empty($userInfo['usr_lastname']) ? $userInfo['usr_lastname'] : '';
            $item['USR_FIRSTNAME'] = !empty($userInfo['usr_firstname']) ? $userInfo['usr_firstname'] : '';

            return $item;
        });

        return $results;
    }

    /**
     * Check if a subprocess has active parent cases
     * 
     * @param array $parents
     * @return bool
     */
    public static function hasActiveParentsCases($parents)
    {
        foreach ($parents as $parent) {
            $query = Delegation::select()->where('PRO_UID', $parent['PRO_PARENT'])
                ->where('TAS_UID', $parent['TAS_PARENT'])->where('DEL_THREAD_STATUS', 'OPEN')
                ->limit(1);
            $res = $query->get()->values()->toArray();
            if (!empty($res)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get cases completed by specific user
     *
     * @param int $userId
     *
     * @return array
     */
    public static function casesCompletedBy(int $userId)
    {
        // Get the case numbers related to this filter
        $query = Delegation::query()->select(['APP_NUMBER']);
        // Filter the user
        $query->participated($userId);
        // Filter the last thread
        $query->lastThread();
        // Get the result
        $results = $query->get();

        return $results->values()->toArray();
    }

    /**
     * Get cases started by specific user
     *
     * @param int $userId
     *
     * @return array
     */
    public static function casesStartedBy(int $userId)
    {
        // Get the case numbers related to this filter
        $query = Delegation::query()->select(['APP_NUMBER']);
        // Filter the user
        $query->participated($userId);
        // Filter the first thread
        $query->caseStarted();
        // Get the result
        $results = $query->get();

        return $results->values()->toArray();
    }

    /**
     * Get cases filter by thread title
     *
     * @param string $search
     *
     * @return array
     */
    public static function casesThreadTitle(string $search)
    {
        // Get the case numbers related to this filter
        $query = Delegation::query()->select(['APP_NUMBER']);
        // Filter the title
        $query->title($search);
        // Get open or last thread
        $query->where(function ($query) {
            // Get open threads
            $query->threadIdOpen();
            // Get last
            $query->orWhere(function ($query) {
                $query->lastThread();
            });
        });
        // Group by
        $query->groupBy('APP_NUMBER');
        // Get the result
        $results = $query->get();

        return $results->values()->toArray();
    }
}
