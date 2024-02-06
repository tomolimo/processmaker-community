<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class Triggers extends Model
{
    // Set our table name
    protected $table = 'TRIGGERS';
    // No timestamps
    public $timestamps = false;
    //primary key
    protected $primaryKey = 'TRI_UID';

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