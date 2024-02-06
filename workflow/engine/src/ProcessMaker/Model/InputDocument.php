<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Represents a input document object in the system.
 */
class InputDocument extends Model
{
    protected $table = 'INPUT_DOCUMENT';
    protected $primaryKey = 'INP_DOC_ID';
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
     * Get input documents by PRO_UID 
     * @param string $proUid
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByProUid($proUid)
    {
        return InputDocument::where('PRO_UID', '=', $proUid)->get();
    }

    /**
     * Get input document by INP_DOC_UID
     * @param type $inpDocUid
     * @return Model
     */
    public static function getByInpDocUid($inpDocUid)
    {
        return InputDocument::where('INP_DOC_UID', '=', $inpDocUid)->first();
    }
}
