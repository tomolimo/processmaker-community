<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppNotes extends Model
{
    use HasFactory;

    // Set our table name
    protected $table = 'APP_NOTES';
    // No timestamps
    public $timestamps = false;
    // Primary key
    protected $primaryKey = 'NOTE_ID';
    // The IDs are auto-incrementing
    public $incrementing = true;

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'NOTE_TYPE' => 'USER',
        'NOTE_ORIGIN_OBJ' => '',
        'NOTE_AFFECTED_OBJ1' => '',
        'NOTE_AFFECTED_OBJ2' => ''
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'APP_UID',
        'APP_NUMBER',
        'USR_UID',
        'NOTE_DATE',
        'NOTE_CONTENT',
        'NOTE_TYPE',
        'NOTE_AVAILABILITY',
        'NOTE_ORIGIN_OBJ',
        'NOTE_AFFECTED_OBJ1',
        'NOTE_AFFECTED_OBJ2',
        'NOTE_RECIPIENTS'
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
     * Scope a query to filter an specific case id
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $appNumber
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAppNumber($query, int $appNumber)
    {
        return $query->where('APP_NUMBER', $appNumber);
    }

    /**
     * Return the documents related to the case
     *
     * @param string $appUid
     * @param int $start
     * @param int $limit
     * @param string $dir
     *
     * @return array
     */
    public static function getNotes(string $appUid, $start = 0, $limit = 25, $dir = 'DESC')
    {
        $query = AppNotes::query()->select([
            'NOTE_ID',
            'APP_UID',
            'APP_NUMBER',
            'NOTE_DATE',
            'NOTE_CONTENT',
            'NOTE_TYPE',
            'NOTE_AVAILABILITY',
            'USERS.USR_UID',
            'USERS.USR_USERNAME',
            'USERS.USR_FIRSTNAME',
            'USERS.USR_LASTNAME'
        ]);
        $query->leftJoin('USERS', function ($join) {
            $join->on('USERS.USR_UID', '=', 'APP_NOTES.USR_UID');
        });
        $query->appUid($appUid);
        $query->orderBy('NOTE_DATE', $dir);
        // Add pagination to the query
        $query->offset($start)->limit($limit);

        $results = $query->get();
        $notes = [];
        $notes['notes'] = [];
        $results->each(function ($item, $key) use (&$notes) {
            $row = $item->toArray();
            $row['NOTE_CONTENT'] = stripslashes($row['NOTE_CONTENT']);
            $notes['notes'][] = $row;
        });

        return $notes;
    }

    /**
     * Return the total notes by case
     *
     * @param string $appUid
     *
     * @return int
     */
    public static function getTotal(string $appUid)
    {
        $query = AppNotes::query()->select(['NOTE_ID']);
        $query->appUid($appUid);
        $total = $query->count();

        return $total;
    }

    /**
     * Return the total notes by case
     *
     * @param int $appNumber
     *
     * @return int
     */
    public static function total(int $appNumber)
    {
        $query = AppNotes::query()->select(['NOTE_ID']);
        $query->appNumber($appNumber);

        return $query->count();
    }
}
