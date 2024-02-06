<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Represents a output document object in the system.
 */
class OutputDocument extends Model
{
    protected $table = 'OUTPUT_DOCUMENT';
    protected $primaryKey = 'OUT_DOC_ID';
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
     * Get output documents by PRO_UID.
     * @param string $proUid
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByProUid($proUid)
    {
        return OutputDocument::where('PRO_UID', '=', $proUid)->get();
    }

    /**
     * Get output document by OUT_DOC_UID.
     * @param string $outDocUid
     * @return Model
     */
    public static function getByOutDocUid($outDocUid)
    {
        return OutputDocument::where('OUT_DOC_UID', '=', $outDocUid)->first();
    }
}
