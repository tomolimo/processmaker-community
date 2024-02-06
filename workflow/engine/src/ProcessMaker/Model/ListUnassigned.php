<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;
use ListUnassigned as PropelListUnassigned;

class ListUnassigned extends Model
{
    protected $table = "LIST_UNASSIGNED";
    // No timestamps
    public $timestamps = false;

    /**
     * Returns the application this belongs to
     */
    public function application()
    {
        return $this->belongsTo(Application::class, 'APP_UID', 'APP_UID');
    }

    /**
     * Return the process task this belongs to
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'TAS_ID', 'TAS_ID');
    }

    /**
     * Return the process this belongs to
     */
    public function process()
    {
        return $this->belongsTo(Process::class, 'PRO_ID', 'PRO_ID');
    }

    /**
     * Return the user this belongs to
     */
    public function previousUser()
    {
        return $this->belongsTo(User::class, 'DEL_PREVIOUS_USR_UID', 'USR_UID');
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
        return $query->whereIn('TAS_ID', $tasks);
    }

    /**
     * Scope a query to only include a specific case
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  integer $appNumber
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCase($query, $appNumber)
    {
        return $query->where('APP_NUMBER', '=', $appNumber);
    }

    /**
     * Scope a query to only include a specific index
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  integer $index
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIndex($query, $index)
    {
        return $query->where('DEL_INDEX', '=', $index);
    }

    /**
     * Scope a query to only include a specific task
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  integer $task
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTask($query, $task)
    {
        return $query->where('TAS_ID', '=', $task);
    }

    /**
     * Get count
     *
     * @param string $userUid
     * @param array $filters
     *
     * @return array
     */
    public static function doCount($userUid, $filters = [])
    {
        $list = new PropelListUnassigned();
        $result = $list->getCountList($userUid, $filters);

        return $result;
    }

    /**
     * Count the self-services cases by user
     *
     * @param string $usrUid
     *
     * @return integer
     */
    public static function countSelfService($usrUid)
    {
        //Get the task self services related to the user
        $taskSelfService = TaskUser::getSelfServicePerUser($usrUid);
        //Get the task self services value based related to the user
        $selfServiceValueBased = AppAssignSelfServiceValue::getSelfServiceCasesByEvaluatePerUser($usrUid);

        //Start the query for get the cases related to the user
        $query = ListUnassigned::query()->select('APP_NUMBER');

        //Get the cases unassigned
        if (!empty($selfServiceValueBased)) {
            $query->where(function ($query) use ($selfServiceValueBased, $taskSelfService) {
                //Get the cases related to the task self service
                $query->tasksIn($taskSelfService);
                foreach ($selfServiceValueBased as $case) {
                    //Get the cases related to the task self service value based
                    $query->orWhere(function ($query) use ($case) {
                        $query->case($case['APP_NUMBER'])->index($case['DEL_INDEX'])->task($case['TAS_ID']);
                    });
                }
            });
        } else {
            //Get the cases related to the task self service
            $query->tasksIn($taskSelfService);
        }

        return $query->count();
    }

    /**
     * Search data
     *
     * @param string $userUid
     * @param array $filters
     *
     * @return array
     */
    public static function loadList($userUid, $filters = [])
    {
        $list = new PropelListUnassigned();
        $result = $list->loadList($userUid, $filters);

        return $result;
    }
}
