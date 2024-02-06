<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;
use Exception;

class User extends Model
{
    protected $table = "USERS";
    protected $primaryKey = 'USR_ID';
    // Our custom timestamp columns
    const CREATED_AT = 'USR_CREATE_DATE';
    const UPDATED_AT = 'USR_UPDATE_DATE';

    /**
     * Returns the delegations this user has (all of them)
     */
    public function delegations()
    {
        return $this->hasMany(Delegation::class, 'USR_ID', 'USR_ID');
    }

    /**
     * Return the user this belongs to
     */
    public function groups()
    {
        return $this->belongsTo(GroupUser::class, 'USR_UID', 'USR_UID');
    }

    /**
     * Return the groups from a user
     *
     * @param boolean $usrUid
     *
     * @return array
     */
    public static function getGroups($usrUid)
    {
        return User::find($usrUid)->groups()->get();
    }

    /**
     * Scope for the specified user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     *
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws Exception
     */
    public function scopeUserFilters($query, array $filters)
    {
        if (!empty($filters['USR_ID'])) {
            $query->where('USR_ID', $filters['USR_ID']);
        } elseif (!empty($filters['USR_UID'])) {
            $query->where('USR_UID', $filters['USR_UID']);
        } else {
            throw new Exception("There are no filter for loading a user model");
        }

        return $query;
    }
}
