<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class GroupUser extends Model
{
    protected $table = 'GROUP_USER';
    // We do not have create/update timestamps for this table
    public $timestamps = false;

    /**
     * Return the user this belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'USR_UID', 'USR_UID');
    }

    /**
     * Return the group user this belongs to
     */
    public function groupsWf()
    {
        return $this->belongsTo(Groupwf::class, 'GRP_ID', 'GRP_ID');
    }

    /**
     * Scope a query to specific user
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUser($query, $user)
    {
        return $query->where('USR_UID', '=', $user);
    }

    /**
     * Return the groups from a user
     *
     * @param string $usrUid
     * @param string $column
     *
     * @return array
     */
    public static function getGroups($usrUid, $column = 'GRP_ID')
    {
        $groups = GroupUser::query()->select(['GROUP_USER.' . $column])
            ->join('GROUPWF', function ($join) use ($usrUid) {
                $join->on('GROUPWF.GRP_ID', '=', 'GROUP_USER.GRP_ID')
                    ->where('GROUPWF.GRP_STATUS', 'ACTIVE')
                    ->where('GROUP_USER.USR_UID', $usrUid);
            })->get()->values()->toArray();

        return $groups;
    }
}

