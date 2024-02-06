<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProcessVariables extends Model
{
    use HasFactory;

    // Set our table name
    protected $table = 'PROCESS_VARIABLES';
    // No timestamps
    public $timestamps = false;
    // Primary key
    protected $primaryKey = 'VAR_UID';
    // The IDs are auto-incrementing
    public $incrementing = false;
    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'VAR_FIELD_SIZE' => 0,
        'VAR_DBCONNECTION' => '',
        'VAR_SQL' => '',
        'VAR_NULL' => 0,
        'VAR_DEFAULT' => '',
        'VAR_ACCEPTED_VALUES' => '[]',
        'INP_DOC_UID' => '',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'VAR_UID',
        'PRJ_UID',
        'PRO_ID',
        'VAR_NAME',
        'VAR_FIELD_TYPE',
        'VAR_FIELD_TYPE_ID',
        'VAR_FIELD_SIZE',
        'VAR_LABEL',
        'VAR_DBCONNECTION',
        'VAR_SQL',
        'VAR_NULL',
        'VAR_DEFAULT',
        'VAR_ACCEPTED_VALUES',
        'INP_DOC_UID'
    ];

    /**
     * Scope a query to filter an specific process
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $proUid
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcess($query, string $proUid)
    {
        return $query->where('PRJ_UID', $proUid);
    }

    /**
     * Scope a query to filter an specific process
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $proId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcessId($query, int $proId)
    {
        return $query->where('PROCESS_VARIABLES.PRO_ID', $proId);
    }

    /**
     * Scope a query to filter a specific type for variable
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $typeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTypeId($query, int $typeId)
    {
        return $query->where('VAR_FIELD_TYPE_ID', $typeId);
    }

    /**
     * Return the variable information
     *
     * @param string $varUid
     *
     * @return array
     */
    public static function getVariable(string $varUid)
    {
        $query = ProcessVariables::query()->select();
        $query->where('VAR_UID', $varUid)->limit(1);
        $result = $query->get()->values()->toArray();
        $result = head($result);

        return $result;
    }

    /**
     * Return the variables list
     *
     * @param int $proId
     *
     * @return array
     */
    public static function getVariables(int $proId)
    {
        $query = ProcessVariables::query()->select();
        $query->leftJoin('DB_SOURCE', function ($join) {
            $join->on('DB_SOURCE.PRO_ID', '=', 'PROCESS_VARIABLES.PRO_ID');
        });
        $query->where('PROCESS_VARIABLES.PRO_ID', $proId);
        $results = $query->get();
        $variablesList = [];
        $results->each(function ($item, $key) use (&$variablesList) {
            $variablesList[] = $item->toArray();
        });

        return $variablesList;
    }

    /**
     * Return the variables list
     *
     * @param int $proId
     * @param int $typeId
     * @param int $start
     * @param int $limit
     * @param string $search
     *
     * @return array
     */
    public static function getVariablesByType(int $proId, int $typeId = 0, $start = null, $limit = null, $search = null)
    {
        $query = ProcessVariables::query()->select();
        $query->leftJoin('DB_SOURCE', function ($join) {
            $join->on('DB_SOURCE.PRO_ID', '=', 'PROCESS_VARIABLES.PRO_ID');
        });
        $query->processId($proId);
        // Check if we need to filter the type of variables
        if ($typeId > 0) {
            $query->typeId($typeId);
        }
        // search a specific variable name
        if (!empty($search)) {
            $query->where('VAR_NAME', 'LIKE', "${search}%");
        }
        // order by varNane
        $query->orderBy('VAR_NAME', 'ASC');
        // Check if we need to add a pagination
        if(!is_null($start) && !is_null($limit)) {
            $query->offset($start)->limit($limit);
        }
        // Get records
        $results = $query->get();
        $variablesList = [];
        $results->each(function ($item, $key) use (&$variablesList) {
            $variablesList[] = $item->toArray();
        });

        return $variablesList;
    }
}