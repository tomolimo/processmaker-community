<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class Groupwf extends Model
{
    protected $table = 'GROUPWF';
    protected $primaryKey = 'GRP_ID';
    // We do not have create/update timestamps for this table
    public $timestamps = false;

    /**
     * Scope a query to active groups
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('GRP_STATUS', '=', 'ACTIVE');
    }

    /**
     * Return the user this belongs to
     */
    public function groupUsers()
    {
        return $this->belongsTo(GroupUser::class, 'GRP_ID', 'GRP_ID');
    }
}

