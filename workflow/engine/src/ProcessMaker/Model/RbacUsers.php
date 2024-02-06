<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Exception;
use Illuminate\Database\Eloquent\Model;
use ProcessMaker\Model\RbacUsersRoles;

class RbacUsers extends Model
{
    use HasFactory;

    protected $table = 'RBAC_USERS';
    public $timestamps = false;

    /**
     * Create a new user
     * 
     * @param array $data
     * @return array
     * @throws Exception
     */
    public static function createUser($data)
    {
        try {
            $dataInsert = [
                'USR_UID' => $data['USR_UID'],
                'USR_USERNAME' => $data['USR_USERNAME'],
                'USR_PASSWORD' => $data['USR_PASSWORD'],
                'USR_FIRSTNAME' => $data['USR_FIRSTNAME'],
                'USR_LASTNAME' => $data['USR_LASTNAME'],
                'USR_EMAIL' => $data['USR_EMAIL'],
                'USR_DUE_DATE' => $data['USR_DUE_DATE'],
                'USR_CREATE_DATE' => $data['USR_CREATE_DATE'],
                'USR_UPDATE_DATE' => $data['USR_UPDATE_DATE'],
                'USR_STATUS' => $data['USR_STATUS_ID'],
                'USR_AUTH_TYPE' => $data['USR_AUTH_TYPE'],
                'UID_AUTH_SOURCE' => $data['UID_AUTH_SOURCE'],
                'USR_AUTH_USER_DN' => $data['USR_AUTH_USER_DN'],
                'USR_AUTH_SUPERVISOR_DN' => $data['USR_AUTH_SUPERVISOR_DN'],
            ];
            RbacUsers::insert($dataInsert);
            RbacUsersRoles::assignRolToUser($data['USR_UID'], $data['ROL_UID']);
        } catch (Exception $e) {
            throw new Exception("Error: {$e->getMessage()}.");
        }
        return $data;
    }

    /**
     * Verify if username exists
     * 
     * @param string $username
     * @return boolean
     */
    public static function verifyUsernameExists($username)
    {
        $query = RbacUsers::select()->where('USR_USERNAME', $username);
        $result = $query->get()->values()->toArray();
        if (empty($result)) {
            return false;
        }
        return true;
    }

    /**
     * Verify if user exists
     * 
     * @param string $usrUid
     * @return boolean
     */
    public static function verifyUserExists($usrUid)
    {
        $query = RbacUsers::select()->where('USR_UID', $usrUid);
        if (empty($query->get()->values()->toArray())) {
            return false;
        }
        return true;
    }
}
