<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groupwf extends Model
{
    use HasFactory;

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

    /**
     * Scope for query to get the group uid
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $uid
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGroup($query, $uid)
    {
        return $query->where('GRP_UID', $uid);
    }

    /**
     * Verify if group exists
     * 
     * @param string $grpUid
     * @return boolean
     */
    public static function verifyGroupExists($grpUid)
    {
        $query = Groupwf::select()->group($grpUid);
        if (empty($query->get()->values()->toArray())) {
            return false;
        }
        return true;
    }

    /**
     * Get group Id
     * 
     * @param string $grpUid
     * @return array
     */
    public static function getGroupId($grpUid)
    {
        $query = Groupwf::select('GRP_ID')->where('GRP_UID', $grpUid);
        return $query->first()->toArray();
    }
}
