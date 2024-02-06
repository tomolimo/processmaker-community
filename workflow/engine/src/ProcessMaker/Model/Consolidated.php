<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consolidated extends Model
{
    use HasFactory;

    // Set our table name
    protected $table = 'CASE_CONSOLIDATED';
    // Set the PK
    protected $primaryKey = 'TAS_UID';
    // No timestamps
    public $timestamps = false;
    // Incrementing
    public $incrementing = false;
    // Fillable fields
    protected $fillable = ['TAS_UID', 'DYN_UID ', 'REP_TAB_UID', 'CON_STATUS'];

    /**
     * Return the task this belongs to
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'TAS_UID', 'TAS_UID');
    }

    /**
     * Scope a query to only include active batch routing
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('CON_STATUS', 'ACTIVE');
    }

    /**
     * Scope a join for pending cases
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinPendingCases($query, $statusId = 2)
    {
        $query->join('APP_DELEGATION', function ($join) {
            $join->on('APP_DELEGATION.TAS_UID', '=', 'CASE_CONSOLIDATED.TAS_UID')
            ->where('APP_DELEGATION.DEL_THREAD_STATUS', 'OPEN');
        });

        return $query;
    }

    /**
     * Scope a join with PROCESS table
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinProcess($query)
    {
        $query->join('PROCESS', 'PROCESS.PRO_ID', '=', 'APP_DELEGATION.PRO_ID');
        return $query;
    }

    /**
     * Scope a join with TASK table
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinTask($query)
    {
        $query->join('TASK', 'TASK.TAS_UID', '=', 'CASE_CONSOLIDATED.TAS_UID');
        return $query;
    }

    /**
     * Count tasks configured with consolidated
     *
     * @return int
     */
    public function getCounterActive()
    {
        $query = Consolidated::query()->select();
        // Apply filters
        $query->active();
        // Return the number of rows
        return $query->count(['CASE_CONSOLIDATED.TAS_UID']);
    }

    /**
     * Count tasks configured with consolidated
     *
     * @return int
     */
    public function getConsolidated()
    {
        $query = Consolidated::query()->select([
            'APP_DELEGATION.APP_NUMBER',
            'APP_DELEGATION.PRO_UID',
            'CASE_CONSOLIDATED.TAS_UID',
            'CASE_CONSOLIDATED.DYN_UID',
            'PROCESS.PRO_TITLE',
            'TASK.TAS_TITLE'
        ]);
        // Scope get the pending consolidated task
        $query->joinPendingCases();
        // Get only active
        $query->active();
        $query->joinProcess();
        $query->joinTask();
        // Get the rows
        $bachPerTask = [];
        $results = $query->get();
        $results->each(function ($item, $key) use (&$bachPerTask) {
            $res = $item->toArray();
            $bachPerTask[$item->TAS_UID] = [];
            $bachPerTask[$item->TAS_UID][] = $res;
        });
        // Return
        return $bachPerTask;
    }
}