<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    use HasFactory;

    protected $table = "STEP";
    public $timestamps = false;

    /**
     * Get object step by process, task, object type and his Uid.
     */
    public static function getByProcessTaskAndStepType($proUid, $tasUid, $stepTypeObj, $stepUidObj)
    {
        $step = self::where('PRO_UID', '=', $proUid)
            ->where('TAS_UID', '=', $tasUid)
            ->where('STEP_TYPE_OBJ', '=', $stepTypeObj)
            ->where('STEP_UID_OBJ', '=', $stepUidObj)
            ->first();
        return $step;
    }
}
