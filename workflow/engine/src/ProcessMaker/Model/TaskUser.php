<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class TaskUser extends Model
{
    protected $table = 'TASK_USER';

    public $timestamps = false;

    /**
     * Return the task this belongs to
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'TAS_UID', 'TAS_UID');
    }

    /**
     * Return the user this belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'USR_UID', 'USR_UID');
    }

    /**
     * Get the task self services related to the user
     *
     * @param string $usrUid
     *
     * @return array
     */
    public static function getSelfServicePerUser($usrUid)
    {
        //Get the groups related to the user
        $groups = GroupUser::getGroups($usrUid, 'GRP_UID');

        // Build query
        $query = Task::query()->select('TASK.TAS_ID');
        //Add Join with process filtering only the active process
        $query->join('PROCESS', function ($join) {
            $join->on('PROCESS.PRO_UID', '=', 'TASK.PRO_UID')
                ->where('PROCESS.PRO_STATUS', 'ACTIVE');
        });
        //Add join with with the task users
        $query->join('TASK_USER', function ($join) {
            $join->on('TASK.TAS_UID', '=', 'TASK_USER.TAS_UID')
                //We not considered the Ad-hoc
                ->where('TASK_USER.TU_TYPE', '=', 1);
        });
        //Filtering only the task self-service
        $query->isSelfService();
        //Filtering the task related to the user
        $query->where(function ($query) use ($usrUid, $groups) {
            //Filtering the user assigned in the task
            $query->where('TASK_USER.USR_UID', '=', $usrUid);
            if (!empty($groups)) {
                //Consider the group related to the user
                $query->orWhere(function ($query) use ($groups) {
                    $query->whereIn('TASK_USER.USR_UID', $groups);
                });
            }
        });
        $query->distinct();
        $tasks = $query->get()->values()->toArray();

        return $tasks;
    }
}