<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ProcessMaker\Model\Application;

class SubProcess extends Model
{
    use HasFactory;

    protected $table = 'SUB_PROCESS';
    protected $primaryKey = 'SP_UID';
    // We do not have create/update timestamps for this table
    public $timestamps = false;

    /**
     * Get he Process parents of a subprocess
     * 
     * @param string $proUid
     * @return array
     */
    public static function getProParents($proUid)
    {
        $query = SubProcess::select('PRO_PARENT', 'TAS_PARENT')->where('PRO_UID', $proUid);
        return $query->get()->values()->toArray();
    }
}
