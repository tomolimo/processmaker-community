<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use AdditionalTables as ModelAdditionalTables;
use Illuminate\Database\Eloquent\Model;

class AdditionalTables extends Model
{
    use HasFactory;

    protected $table = 'ADDITIONAL_TABLES';
    public $timestamps = false;

    /**
     * Get the fields related to the table belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function columns()
    {
        return $this->belongsTo(Fields::class, 'ADD_TAB_UID', 'ADD_TAB_UID');
    }

    /**
     * Scope a query to get the offline tables
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOffline($query)
    {
        return $query->where('ADD_TAB_OFFLINE', '=', 1);
    }

    /**
     * Scope a query to get the tables related to the process
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $proUid
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcess($query, string $proUid)
    {
        return $query->where('PRO_UID', $proUid);
    }

    /**
     * Get tables related to the process
     * 
     * @param string $proUid
     * @return array
     */
    public static function getTables(string $proUid)
    {
        $query = AdditionalTables::query()->select();
        $query->process($proUid);
        $result = $query->get()->values()->toArray();

        return $result;
    }

    /**
     * Get the structure of offline tables
     *
     * @return array
     */
    public static function getTablesOfflineStructure()
    {
        $query = AdditionalTables::query()->select([
            'ADD_TAB_UID',
            'ADD_TAB_NAME',
            'ADD_TAB_DESCRIPTION',
            'ADD_TAB_CLASS_NAME'
        ]);
        $query->offline();

        $results = $query->get();
        $data = [];
        $results->each(function ($item, $key) use (&$data) {
            $data[$key] = array_change_key_case($item->toArray(), CASE_LOWER);
            $data[$key]['fields'] = Fields::getFields($item->ADD_TAB_UID);
        });

        return $data;
    }

    /**
     * Get the data of offline tables
     *
     * @return array
     */
    public static function getTablesOfflineData()
    {
        $query = AdditionalTables::query()->select([
            'ADD_TAB_UID',
            'ADD_TAB_NAME',
            'ADD_TAB_DESCRIPTION',
            'ADD_TAB_CLASS_NAME'
        ]);
        $query->offline();

        $results = $query->get();
        $data = [];
        $results->each(function ($item, $key) use (&$data) {
            $data[$key] = array_change_key_case($item->toArray(), CASE_LOWER);

            $additionalTables = new ModelAdditionalTables();
            $result = $additionalTables->getAllData($item->ADD_TAB_UID);
            if (empty($result['rows'])) {
                $data[$key]['rows'] = [];
            } else {
                foreach ($result['rows'] as $i => $row) {
                    $data[$key]['rows'][$i] = $row;
                }
            }
        });

        return $data;
    }

    /**
     * Update the offline property.
     * @param array $tablesUid
     * @param int $value
     * @return void
     */
    public static function updatePropertyOffline(array $tablesUid, $value): void
    {
        $query = AdditionalTables::whereIn('ADD_TAB_UID', $tablesUid)
                ->update(['ADD_TAB_OFFLINE' => $value]);
    }
}
