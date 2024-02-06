<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class AppAssignSelfServiceValue extends Model
{
    protected $table = 'APP_ASSIGN_SELF_SERVICE_VALUE';
    protected $primaryKey = 'ID';
    // We do not have create/update timestamps for this table
    public $timestamps = false;

    /**
     * Return the case number this belongs to
     */
    public function appNumber()
    {
        return $this->belongsTo(Delegation::class, 'APP_NUMBER', 'APP_NUMBER');
    }

    /**
     * Return the index this belongs to
     */
    public function index()
    {
        return $this->belongsTo(Delegation::class, 'DEL_INDEX', 'DEL_INDEX');
    }

    /**
     * Return the task this belongs to
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'TAS_ID', 'TAS_ID');
    }

    /**
     * Get cases with assignment Self-Service Value Based
     *
     * @param string $usrUid
     *
     * @return array
     */
    public static function getSelfServiceCasesByEvaluatePerUser($usrUid)
    {
        //Get the groups related to the user
        $groups = GroupUser::getGroups($usrUid);

        // Build query
        $query = AppAssignSelfServiceValue::query()->select();
        $query->join('APP_ASSIGN_SELF_SERVICE_VALUE_GROUP', function ($join) {
            $join->on('APP_ASSIGN_SELF_SERVICE_VALUE.ID', '=', 'APP_ASSIGN_SELF_SERVICE_VALUE_GROUP.ID');
        });
        $query->where(function ($query) use ($usrUid, $groups) {
            //Filtering the user assigned in the task
            $query->where('APP_ASSIGN_SELF_SERVICE_VALUE_GROUP.GRP_UID', '=', $usrUid);
            if (!empty($groups)) {
                //Consider the group related to the user
                $query->orWhere(function ($query) use ($groups) {
                    $query->whereIn('APP_ASSIGN_SELF_SERVICE_VALUE_GROUP.ASSIGNEE_ID', $groups);
                    $query->where('APP_ASSIGN_SELF_SERVICE_VALUE_GROUP.ASSIGNEE_TYPE', '=', 2);
                });
            }
        });
        $query->distinct();
        $result = $query->get()->values()->toArray();

        return $result;
    }
}

