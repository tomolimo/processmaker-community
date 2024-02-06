<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Dynaform
 * @package ProcessMaker\Model
 *
 * Represents a dynaform object in the system.
 */
class Dynaform extends Model
{
    protected $table = 'DYNAFORM';
    protected $primaryKey = "DYN_ID";
    public $timestamps = false;

    /**
     * Return relation process.
     * @return object
     */
    public function process()
    {
        return $this->belongsTo(Process::class, 'PRO_UID', 'PRO_UID');
    }

    /**
     * Get dynaforms by PRO_UID.
     * @param string $proUid
     * @return object
     */
    public static function getByProUid($proUid)
    {
        return DB::table('DYNAFORM')
                        ->select()
                        ->where('DYNAFORM.PRO_UID', '=', $proUid)
                        ->get();
    }

    /**
     * Get dynaform by DYN_UID.
     * @param string $dynUid
     * @return object
     */
    public static function getByDynUid($dynUid)
    {
        return DB::table('DYNAFORM')
                        ->select()
                        ->where('DYNAFORM.DYN_UID', '=', $dynUid)
                        ->first();
    }

    /**
     * Get dynaforms by PRO_UID except the DYN_UID specified in the second parameter.
     * @param string $proUid
     * @param string $dynUid
     * @return object
     */
    public static function getByProUidExceptDynUid($proUid, $dynUid)
    {
        return DB::table('DYNAFORM')
                        ->select()
                        ->where('DYNAFORM.PRO_UID', '=', $proUid)
                        ->where('DYNAFORM.DYN_UID', '!=', $dynUid)
                        ->get();
    }

    /**
     * Scope a query to filter an specific process
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcess($query, string $proUID)
    {
        return $query->where('PRO_UID', $proUID);
    }
}
