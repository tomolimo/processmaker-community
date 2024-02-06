<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'TASK';
    protected $primaryKey = 'TAS_ID';
    // We do not have create/update timestamps for this table
    public $timestamps = false;

    public function process()
    {
        return $this->belongsTo(Process::class, 'PRO_UID', 'PRO_UID');
    }

    public function delegations()
    {
        return $this->hasMany(Delegation::class, 'TAS_ID', 'TAS_ID');
    }

    /**
     * Scope a query to only include self-service
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsSelfService($query)
    {
        return $query->where('TAS_ASSIGN_TYPE', '=', 'SELF_SERVICE')
            ->where('TAS_GROUP_VARIABLE', '=', '');
    }
}
