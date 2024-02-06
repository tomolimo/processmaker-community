<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ListUnassigned as PropelListUnassigned;

/**
 * Class ListUnassigned
 *
 * @deprecated Class deprecated in Release 3.6.0
 */
class ListUnassigned extends Model
{
    use HasFactory;

    protected $table = "LIST_UNASSIGNED";
    // No timestamps
    public $timestamps = false;

    /**
     * Returns the application this belongs to
     */
    public function application()
    {
        return $this->belongsTo(Application::class, 'APP_UID', 'APP_UID');
    }

    /**
     * Return the process task this belongs to
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'TAS_ID', 'TAS_ID');
    }

    /**
     * Return the process this belongs to
     */
    public function process()
    {
        return $this->belongsTo(Process::class, 'PRO_ID', 'PRO_ID');
    }

    /**
     * Return the user this belongs to
     */
    public function previousUser()
    {
        return $this->belongsTo(User::class, 'DEL_PREVIOUS_USR_UID', 'USR_UID');
    }

    /**
     * Scope a query to only include specific tasks
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $tasks
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTasksIn($query, array $tasks)
    {
        return $query->whereIn('TAS_ID', $tasks);
    }

    /**
     * Scope a query to only include a specific case
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param integer $appNumber
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCase($query, $appNumber)
    {
        return $query->where('APP_NUMBER', '=', $appNumber);
    }

    /**
     * Scope a query to only include a specific index
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param integer $index
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIndex($query, $index)
    {
        return $query->where('DEL_INDEX', '=', $index);
    }

    /**
     * Scope a query to only include a specific task
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param integer $task
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTask($query, $task)
    {
        return $query->where('TAS_ID', '=', $task);
    }

    /**
     * Get count
     *
     * @param string $userUid
     * @param array $filters
     *
     * @return int
     */
    public static function doCount($userUid, $filters = [])
    {
        $list = new PropelListUnassigned();
        $result = $list->getCountList($userUid, $filters);

        return $result;
    }

    /**
     * Search data
     *
     * @param string $userUid
     * @param array $filters
     *
     * @return array
     */
    public static function loadList($userUid, $filters = [])
    {
        $list = new PropelListUnassigned();
        $result = $list->loadList($userUid, $filters);

        return $result;
    }

    /**
     * Get the unassigned cases related to the self service timeout
     *
     * @return array
     */
    public static function selfServiceTimeout()
    {
        $query = ListUnassigned::query()->select();
        $query->join('TASK', function ($join) {
            $join->on('LIST_UNASSIGNED.TAS_ID', '=', 'TASK.TAS_ID')
                ->where('TASK.TAS_SELFSERVICE_TIMEOUT', '=', 1);
        });
        $results = $query->get()->toArray();

        return $results;
    }
}
