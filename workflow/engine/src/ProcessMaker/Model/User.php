<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Configurations;
use Illuminate\Database\Eloquent\Model;
use Exception;
use RBAC;

class User extends Model
{
    use HasFactory;

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
     * Creates a user
     * 
     * @param array $data
     * 
     * @return integer
     * @throws Exception
     */
    public static function createUser($data)
    {
        try {
            $usrData = [
                'USR_UID' => $data['USR_UID'],
                'USR_USERNAME' => $data['USR_USERNAME'],
                'USR_PASSWORD' => $data['USR_PASSWORD'],
                'USR_FIRSTNAME' => $data['USR_FIRSTNAME'],
                'USR_LASTNAME' => $data['USR_LASTNAME'],
                'USR_EMAIL' => $data['USR_EMAIL'],
                'USR_DUE_DATE' => $data['USR_DUE_DATE'],
                'USR_CREATE_DATE' => $data['USR_CREATE_DATE'],
                'USR_UPDATE_DATE' => $data['USR_UPDATE_DATE'],
                'USR_STATUS' => $data['USR_STATUS'],
                'USR_STATUS_ID' => $data['USR_STATUS_ID'],
                'USR_COUNTRY' => $data['USR_COUNTRY'],
                'USR_CITY' => $data['USR_CITY'],
                'USR_LOCATION' => $data['USR_LOCATION'],
                'USR_ADDRESS' => $data['USR_ADDRESS'],
                'USR_PHONE' => $data['USR_PHONE'],
                'USR_FAX' => $data['USR_FAX'],
                'USR_CELLULAR' => $data['USR_CELLULAR'],
                'USR_ZIP_CODE' => $data['USR_ZIP_CODE'],
                'DEP_UID' => $data['DEP_UID'],
                'USR_POSITION' => $data['USR_POSITION'],
                'USR_RESUME' => $data['USR_RESUME'],
                'USR_ROLE' => $data['ROL_CODE']
            ];
            $usrId = User::insertGetId($usrData);
            return $usrId;
        } catch(Exception $e) {
            throw new Exception("Error: {$e->getMessage()}.");
        }
    }

    /**
     * Scope for query to get the user by USR_UID
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $usrUid
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUser($query, string $usrUid)
    {
        return $query->where('USR_UID', '=', $usrUid);
    }

    /**
     * Scope for query to get the user by USR_ID
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $usrId
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUserId($query, int $usrId)
    {
        return $query->where('USR_ID', '=', $usrId);
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

    /**
     * Scope a query to exclude the guest user
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutGuest($query)
    {
        $query->where('USR_UID', '<>', RBAC::GUEST_USER_UID);
    }

    /**
     * Scope a query to include only active users (ACTIVE, VACATION)
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('USERS.USR_STATUS', ['ACTIVE', 'VACATION']);
    }

    /**
     * Get all users, paged optionally, can be sent a text to filter results by user information (first name, last name, username)
     *
     * @param string $text
     * @param int $offset
     * @param int $limit
     *
     * @return array
     *
     * @throws Exception
     */
    public static function getUsersForHome($text = null, $offset = null, $limit = null)
    {
        try {
            // Load configurations of the environment
            $configurations = new Configurations();

            // Field to order the results
            $orderBy = $configurations->userNameFormatGetFirstFieldByUsersTable();

            // Format of the user names
            $formatName = $configurations->getFormats()['format'];

            // Get users from the current workspace
            $query = User::query()->select(['USR_ID', 'USR_USERNAME', 'USR_FIRSTNAME', 'USR_LASTNAME']);

            // Set full name condition if is sent
            if (!empty($text)) {
                $query->where(function ($query) use ($text) {
                    $query->orWhere('USR_USERNAME', 'LIKE', "%{$text}%");
                    $query->orWhere('USR_FIRSTNAME', 'LIKE', "%{$text}%");
                    $query->orWhere('USR_LASTNAME', 'LIKE', "%{$text}%");
                });
            }

            // Exclude guest user
            $query->withoutGuest();

            // Only get active
            $query->active();

            // Order by full name
            $query->orderBy($orderBy);

            // Set pagination if offset and limit are sent
            if (!is_null($offset) && !is_null($limit)) {
                $query->offset($offset);
                $query->limit($limit);
            }

            // Get users
            $users = $query->get()->toArray();

            // Populate the field with the user names in format
            $users = array_map(function ($user) use ($configurations, $formatName) {
                // Format the user names
                $user['USR_FULLNAME'] = $configurations->usersNameFormatBySetParameters($formatName,
                    $user['USR_USERNAME'], $user['USR_FIRSTNAME'], $user['USR_LASTNAME']);

                // Return value with the new element
                return $user;

            }, $users);

            // Return users
            return $users;
        } catch (Exception $e) {
            throw new Exception("Error getting the users: {$e->getMessage()}.");
        }
    }

    /**
     * Get the user id
     *
     * @param string $usrUid
     *
     * @return int
     */
    public static function getId($usrUid)
    {
        $query = User::query()->select(['USR_ID'])
            ->user($usrUid)
            ->limit(1);
        $results = $query->get();
        $id = 0;
        $results->each(function ($item) use (&$id) {
            $id = $item->USR_ID;
        });

        return $id;
    }

    /**
     * Get user information for the tooltip
     *
     * @param int $usrId
     *
     * @return array
     */
    public static function getInformation(int $usrId)
    {
        $query = User::query()->select([
            'USR_ID',
            'USR_USERNAME',
            'USR_FIRSTNAME',
            'USR_LASTNAME',
            'USR_EMAIL',
            'USR_POSITION'
        ])
            ->userId($usrId)
            ->limit(1);
        $results = $query->get();
        $info = [];
        $results->each(function ($item) use (&$info) {
            $info['usr_id'] = $item->USR_ID;
            $info['usr_username'] = $item->USR_USERNAME;
            $info['usr_firstname'] = $item->USR_FIRSTNAME;
            $info['usr_lastname'] = $item->USR_LASTNAME;
            $info['usr_email'] = $item->USR_EMAIL;
            $info['usr_position'] = $item->USR_POSITION;
        });

        return $info;
    }

    /**
     * Get user information
     *
     * @param int $usrId
     *
     * @return array
     */
    public static function getAllInformation($usrId)
    {
        $query = User::query()->select()
            ->userId($usrId)
            ->limit(1);
        $result = $query->get()->values()->toArray();

        return $result;
    }
}
