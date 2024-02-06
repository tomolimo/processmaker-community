<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class Documents extends Model
{
    // Set our table name
    protected $table = 'APP_DOCUMENT';
    // No timestamps
    public $timestamps = false;
    // Primary key
    protected $primaryKey = 'NOTE_ID';
    // The IDs are auto-incrementing
    public $incrementing = false;
    // Valid AppDocType's
    const DOC_TYPE_ATTACHED = 'ATTACHED';
    const DOC_TYPE_CASE_NOTE = 'CASE_NOTE';
    const DOC_TYPE_INPUT = 'INPUT';
    const DOC_TYPE_OUTPUT = 'OUTPUT';

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'APP_DOC_TITLE' => '',
        'APP_DOC_COMMENT' => '',
        'DOC_UID' => '-1',
        'FOLDER_UID' => '',
        'APP_DOC_PLUGIN' => '',
        'APP_DOC_TAGS' => '',
        'APP_DOC_FIELDNAME' => '',
        'APP_DOC_DRIVE_DOWNLOAD' => 'a:0:{}',
        'SYNC_WITH_DRIVE' => 'UNSYNCHRONIZED',
        'SYNC_PERMISSIONS' => '',
        'APP_DOC_STATUS_DATE'  => '',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'DOC_ID',
        'APP_DOC_UID',
        'DOC_VERSION',
        'APP_DOC_FILENAME',
        'APP_UID',
        'DEL_INDEX',
        'DOC_UID',
        'USR_UID',
        'APP_DOC_TYPE',
        'APP_DOC_CREATE_DATE',
        'APP_DOC_INDEX',
        'FOLDER_UID',
        'APP_DOC_STATUS',
    ];

    /**
     * Scope a query to filter an specific case
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $appUid
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAppUid($query, string $appUid)
    {
        return $query->where('APP_UID', $appUid);
    }

    /**
     * Scope a query to filter an specific reference file
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $docId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDocId($query, int $docId)
    {
        return $query->where('DOC_ID', $docId);
    }

    /**
     * Return the documents related to the case
     *
     * @param string $appUid
     * @param string $type
     *
     * @return array
     */
    public static function getAppFiles(string $appUid, $type = 'CASE_NOTES')
    {
        $query = Documents::query()->select();
        $query->appUid($appUid);
        $query->where('APP_DOC_TYPE', $type);
        $results = $query->get();
        $documentList = [];
        $results->each(function ($item, $key) use (&$documentList) {
            $documentList[] = $item->toArray();
        });

        return $documentList;
    }

    /**
     * Get attached files from the case note.
     *
     * @param string $appUid
     *
     * @return object
     */
    public static function getAttachedFilesFromTheCaseNote(string $appUid)
    {
        $result = Documents::select('APP_DOCUMENT.APP_DOC_UID', 'APP_DOCUMENT.DOC_VERSION', 'APP_DOCUMENT.APP_DOC_FILENAME')
                ->join('APP_NOTES', function($join) use($appUid) {
                    $join->on('APP_NOTES.NOTE_ID', '=', 'APP_DOCUMENT.DOC_ID')
                    ->where('APP_DOCUMENT.APP_UID', '=', $appUid);
                })
                ->get();
        return $result;
    }

    /**
     * Return the documents related to the specific DOC_ID
     *
     * @param int $docId
     *
     * @return array
     */
    public static function getFiles(int $docId)
    {
        $query = Documents::query()->select(['APP_DOC_UID', 'APP_DOC_FILENAME', 'DOC_VERSION']);
        $query->docId($docId);
        $results = $query->get();
        $documentList = [];
        $results->each(function ($item, $key) use (&$documentList) {
            $row = $item->toArray();
            $row['LINK'] = "../cases/casesShowCaseNotes?a=" . $row["APP_DOC_UID"] . "&v=" . $row["DOC_VERSION"];
            $documentList[] = $row;
        });

        return $documentList;
    }
}
