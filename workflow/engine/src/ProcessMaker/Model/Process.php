<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;
use RbacUsers;
use RBAC;

/**
 * Class Process
 * @package ProcessMaker\Model
 *
 * Represents a business process object in the system.
 */
class Process extends Model
{
    // Set our table name
    protected $table = 'PROCESS';
    protected $primaryKey = 'PRO_ID';
    // Our custom timestamp columns
    const CREATED_AT = 'PRO_CREATE_DATE';
    const UPDATED_AT = 'PRO_UPDATE_DATE';

    public function tasks()
    {
        return $this->belongsTo(Task::class, 'PRO_ID', 'PRO_ID');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'PRO_CREATE_USER', 'USR_UID');
    }

    public function category()
    {
        return $this->belongsTo(ProcessCategory::class, 'PRO_CATEGORY', 'CATEGORY_UID');
    }

    /**
     * Obtains the process list for an specific user and/or for the specific category
     *
     * @param string $categoryUid
     * @param string $userUid
     * @return array
     *
     * @see ProcessMaker\BusinessModel\Light::getProcessList()
     */
    public function getProcessList($categoryUid, $userUid)
    {
        $selectedColumns = ['PRO_UID', 'PRO_TITLE'];
        $query = Process::query()
            ->select($selectedColumns)
            ->where('PRO_STATUS', 'ACTIVE')
            ->where('PRO_CREATE_USER', $userUid);

        if (!empty($categoryUid)) {
            $query->where('PRO_CATEGORY', $categoryUid);
        }

        return ($query->get()->values()->toArray());
    }

    /**
     * Obtains the list of private processes assigned to the user
     * 
     * @param string $userUid
     * @return array
     */
    public static function getProcessPrivateListByUser($userUid)
    {
        $query = Process::query()
            ->select()
            ->where('PRO_CREATE_USER', $userUid)
            ->where('PRO_TYPE_PROCESS', 'PRIVATE');

        return ($query->get()->values()->toArray());
    }

    /**
     * Converts the private processes to public
     * 
     * @param array $privateProcesses
     * @return void
     */
    public static function convertPrivateProcessesToPublicAndUpdateUser($privateProcesses, $userUid)
    {
        $admin = RBAC::ADMIN_USER_UID;

        $processes = array_column($privateProcesses, 'PRO_ID');
        Process::whereIn('PRO_ID', $processes)
                ->update(['PRO_TYPE_PROCESS' => 'PUBLIC']);

        Process::where('PRO_CREATE_USER', $userUid)
                ->update(['PRO_CREATE_USER' => $admin]);
    }
}
