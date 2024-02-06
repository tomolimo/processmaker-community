<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

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
}
