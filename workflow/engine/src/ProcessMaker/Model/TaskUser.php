<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskUser extends Model
{
    use HasFactory;

    protected $table = 'TASK_USER';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'TAS_UID',
        'TAS_ID',
        'USR_UID',
        'TU_TYPE',
        'TU_RELATION',
        'ASSIGNED_ID',
    ];

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
     * Scope for query to get the assigment
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $tasUid
     * @param string $usrUid
     * @param integer $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAssigment($query, $tasUid, $usrUid, $type = 1)
    {
        return $query->where('TAS_UID', $tasUid)->where('USR_UID', $usrUid)->where('TU_TYPE', $type);
    }

    /**
     * Get the task self services related to the user
     *
     * @param string $usrUid
     *
     * @return array
     */
    public static function getSelfServicePerUser(string $usrUid)
    {
        //Get the groups related to the user
        $groups = GroupUser::getGroups($usrUid, 'GRP_UID');
        // Build query
        $query = Task::query()->select('TASK.TAS_ID');
        // Add Join with process filtering only the active process
        $query->leftJoin('PROCESS', function ($join) {
            $join->on('PROCESS.PRO_UID', '=', 'TASK.PRO_UID')
                ->where('PROCESS.PRO_STATUS', 'ACTIVE');
        });
        // Add join with with the task users
        $query->leftJoin('TASK_USER', function ($join) {
            $join->on('TASK.TAS_UID', '=', 'TASK_USER.TAS_UID')
                // We not considered the Ad-hoc
                ->where('TASK_USER.TU_TYPE', '=', 1);
        });
        // Filtering only the task self-service
        $query->isSelfService();
        // Filtering the task related to the user
        $query->where(function ($query) use ($usrUid, $groups) {
            // Filtering the user assigned in the task
            $query->where('TASK_USER.USR_UID', '=', $usrUid);
            if (!empty($groups)) {
                // Consider the group related to the user
                $query->orWhere(function ($query) use ($groups) {
                    $query->whereIn('TASK_USER.USR_UID', $groups);
                });
            }
        });
        $query->distinct();
        $tasks = $query->get()->values()->toArray();

        return $tasks;
    }

    /**
     * Get the specific assigment related to the task, by default the normal assigment
     *
     * @param string $tasUid
     * @param string $uid
     * @param integer $type, can be 1 = Normal or 2 = Ad-hoc
     *
     * @return array
     */
    public static function getAssigment($tasUid, $uid, $type = 1)
    {
        $query = TaskUser::query()->select()
            ->assigment($tasUid, $uid, $type);
        $result = $query->get()->toArray();

        return head($result);
    }
}