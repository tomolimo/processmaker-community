<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fields extends Model
{
    use HasFactory;

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
     * Scope a query to get the field name
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $name
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeField($query, $name)
    {
        return $query->where('FLD_NAME', $name);
    }

    /**
     * Scope a query to get the field name or label name
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $field
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFieldOrLabel($query, $field)
    {
        $query->where(function ($query) use ($field) {
            $query->field($field);
            $fieldLabel = $field . '_label';
            $query->orWhere(function ($query) use ($fieldLabel) {
                $query->field($fieldLabel);
            });
        });
        return $query;
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

    /**
     * Search a field related to the table
     *
     * @param string $tabUid
     * @param string $field
     *
     * @return bool
     */
    public static function searchVariable(string $tabUid, string $field)
    {
        $query = Fields::query();
        $query->table($tabUid);
        $query->fieldOrLabel($field);
        $result = $query->get()->values()->toArray();

        return !empty($result);
    }
}