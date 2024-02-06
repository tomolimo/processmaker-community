<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessUser extends Model
{
    use HasFactory;

    protected $table = 'PROCESS_USER';
    protected $primaryKey = 'PU_UID';
    // We do not have create/update timestamps for this table
    public $timestamps = false;

    /**
     * Scope process supervisor
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $userUid
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcessSupervisor($query, $userUid)
    {
        $query->where('USR_UID', $userUid);
        $query->where('PU_TYPE', 'SUPERVISOR');
        $query->joinProcess();

        return $query;
    }

    /**
     * Scope process group supervisor
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $userUid
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcessGroupSupervisor($query, $userUid)
    {
        // Ge the groups related to the user, Todo, implement the field PROCESS_USER.GRP_ID
        $groups = GroupUser::getGroups($userUid, 'GRP_UID');
        $query->where('PROCESS_USER.PU_TYPE', 'GROUP_SUPERVISOR');
        $query->whereIn('PROCESS_USER.USR_UID', $groups);
        $query->joinProcess();

        return $query;
    }

    /**
     * Scope join with process table
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinProcess($query)
    {
        $query->leftJoin('PROCESS', function ($leftJoin) {
            $leftJoin->on('PROCESS.PRO_UID', '=', 'PROCESS_USER.PRO_UID');
        });

        return $query;
    }

    /**
     * It returns a list of processes of the supervisor
     * 
     * @param string $userUid
     * @return array
     */
    public static function getProcessesOfSupervisor(string $userUid)
    {
        // Get the list of process when the user is supervisor
        $query = ProcessUser::query()->select(['PRO_ID']);
        $query->processSupervisor($userUid);
        $results = $query->get();
        $processes = [];
        $results->each(function ($item, $key) use (&$processes) {
            $processes[] = $item->PRO_ID;
        });
        // Get the list of process when the group related to the user is supervisor
        $query = ProcessUser::query()->select(['PRO_ID']);
        $query->processGroupSupervisor($userUid);
        $results = $query->get();
        $results->each(function ($item, $key) use (&$processes) {
            $processes[] = $item->PRO_ID;
        });

        return $processes;
    }
}