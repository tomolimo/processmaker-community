<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class Fields extends Model
{
    protected $table = 'FIELDS';
    public $timestamps = false;

    /**
     * Get the fields related to the table belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function table()
    {
        return $this->belongsTo(AdditionalTables::class, 'ADD_TAB_UID', 'ADD_TAB_UID');
    }

    /**
     * Scope a query to get the offline tables
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $tabUid
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTable($query, $tabUid)
    {
        return $query->where('ADD_TAB_UID', '=', $tabUid);
    }

    /**
     * Get the offline tables
     *
     * @param string $tabUid
     *
     * @return array
     */
    public static function getFields($tabUid)
    {
        $query = Fields::query();
        $query->table($tabUid);

        $results = $query->get();
        $data = [];
        $results->each(function ($item, $key) use (&$data) {
            $data[$key] = array_change_key_case($item->toArray(), CASE_LOWER);
        });

        return $data;
    }
}