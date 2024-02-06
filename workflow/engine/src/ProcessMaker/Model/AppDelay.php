<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppDelay extends Model
{
    use HasFactory;

    protected $table = 'APP_DELAY';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'APP_DELAY_UID',
        'PRO_UID',
        'PRO_ID',
        'APP_UID',
        'APP_NUMBER',
        'APP_THREAD_INDEX',
        'APP_DEL_INDEX',
        'APP_TYPE',
        'APP_STATUS',
        'APP_DELEGATION_USER',
        'APP_DELEGATION_USER_ID'.
        'APP_ENABLE_ACTION_USER',
        'APP_ENABLE_ACTION_DATE',
        'APP_DISABLE_ACTION_DATE',
    ];

    /**
     * Scope a query to filter a specific type
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeType($query, string $type = 'PAUSE')
    {
        return $query->where('APP_DELAY.APP_TYPE', $type);
    }
    /**
     * Scope a query to filter a specific disable action
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotDisabled($query)
    {
        return $query->where('APP_DELAY.APP_DISABLE_ACTION_USER', 0);
    }

    /**
     * Scope a query to filter a specific case
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $appNumber
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCase($query, int $appNumber)
    {
        return $query->where('APP_DELAY.APP_NUMBER', $appNumber);
    }

    /**
     * Scope a query to filter a specific index
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $index
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIndex($query, int $index)
    {
        return $query->where('APP_DELAY.APP_DEL_INDEX', $index);
    }

    /**
     * Scope a query to filter a specific user
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDelegateUser($query, string $user)
    {
        return $query->where('APP_DELAY.APP_DELEGATION_USER', $user);
    }

    /**
     * Get the thread paused
     *
     * @param int $appNumber
     * @param int $index
     * @param string $userUid
     *
     * @return array
     */
    public static function getPaused(int $appNumber, int $index, string $userUid = '')
    {
        $query = AppDelay::query()->select([
            'APP_NUMBER',
            'APP_DEL_INDEX AS DEL_INDEX',
            'PRO_UID'
        ]);
        $query->type('PAUSE')->notDisabled();
        $query->case($appNumber);
        // Filter specific index
        if ($index > 0) {
            $query->index($index);
        }
        // Filter specific delegate user
        if (!empty($userUid)) {
            $query->delegateUser($userUid);
        }
        // Get the result
        $results = $query->get();

        return $results->values()->toArray();
    }
}
