<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BpmnProject extends Model
{
    use HasFactory;

    // Set our table name
    protected $table = 'BPMN_PROJECT';
    protected $primaryKey = 'PRJ_UID';
    public $incrementing = false;
    // We do not have create/update timestamps for this table
    public $timestamps = false;

    /**
     * Check is the Process is BPMN.
     *
     * @param string $proUid
     *
     * @return int 1 if is BPMN process or 0 if a Normal process
     */
    public static function isBpmnProcess(string $proUid)
    {
        $query = BpmnProject::query()
            ->select()
            ->where('PRJ_UID', '=', $proUid);
        $result = $query->get()->values()->toArray();

        return empty($result) ? 0 : 1;
    }
}
