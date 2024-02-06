<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Exception;
use G;
use Illuminate\Database\Eloquent\Model;
use ProcessMaker\Model\Groupwf;
use ProcessMaker\Model\RbacUsers;

class GroupUser extends Model
{
    use HasFactory;

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

    /**
     * Verify if a user is already assigned to a group
     * 
     * @param int $usrId
     * @param int $grpId
     * 
     * @return boolean
     */
    public static function verifyUserIsInGroup($usrId, $grpId)
    {
        $query = GroupUser::select()->where('GRP_ID', $grpId)->where('USR_ID', $usrId);
        if (empty($query->get()->values()->toArray())) {
            return false;
        }
        return true;
    }

    /**
     * Assign user to group
     * 
     * @param string $usrUid
     * @param int $usrId
     * @param string $grpUid
     * @param int $grpId
     * 
     * @return void
     * @throws Exception
     */
    public static function assignUserToGroup($usrUid, $usrId, $grpUid, $grpId)
    {
        if (!RbacUsers::verifyUserExists($usrUid)) {
            return ['message' => G::loadTranslation('ID_USER_NOT_REGISTERED_SYSTEM')];
        }
        if (!Groupwf::verifyGroupExists($grpUid)) {
            return ['message' => G::loadTranslation('ID_GROUP_NOT_REGISTERED_SYSTEM')];
        }
        if (GroupUser::verifyUserIsInGroup($usrId, $grpId)) {
            return ['message' => G::loadTranslation('ID_USER_ALREADY_EXISTS_GROUP')];
        }

        try {
            $data = [
                'GRP_UID' => $grpUid,
                'GRP_ID' => $grpId,
                'USR_UID' => $usrUid,
                'USR_ID' => $usrId,
            ];
            GroupUser::insert($data);
        } catch (Exception $e) {
            throw new Exception("Error: {$e->getMessage()}.");
        }
    }
}
