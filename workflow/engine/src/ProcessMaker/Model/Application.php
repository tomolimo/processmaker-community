<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Application extends Model
{
    use HasFactory;

    protected $table = "APPLICATION";
    protected $primaryKey = 'APP_NUMBER';
    public $incrementing = false;
    // No timestamps
    public $timestamps = false;
    // Status id
    const STATUS_DRAFT = 1;
    const STATUS_DRAFT_NAME = 'DRAFT';
    const STATUS_TODO = 2;
    const STATUS_TODO_NAME = 'TO_DO';
    const STATUS_COMPLETED = 3;
    const STATUS_COMPLETED_NAME = 'COMPLETED';
    const STATUS_CANCELED = 4;
    const STATUS_CANCELED_NAME = 'CANCELLED';
    // Status name and status id
    public static $app_status_values = ['DRAFT' => 1, 'TO_DO' => 2, 'COMPLETED' => 3, 'CANCELLED' => 4];

    /**
     * Current user related to the application
     */
    public function currentUser()
    {
        return $this->belongsTo(User::class, 'APP_CUR_USER', 'USR_UID');
    }

    /**
     * Creator user related to the application
     */
    public function creatorUser()
    {
        return $this->belongsTo(User::class, 'APP_INIT_USER', 'USR_UID');
    }

    /**
     * Scope a query to only include specific user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUserId($query, int $user)
    {
        return $query->where('APP_DELEGATION.USR_ID', '=', $user);
    }

    /**
     * Scope for query to get the creator
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $usrId
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCreator($query, $usrId)
    {
        return $query->where('APP_INIT_USER_ID', '=', $usrId);
    }

    /**
     * Scope for query to get the application by APP_UID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $appUid
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAppUid($query, $appUid)
    {
        return $query->where('APP_UID', '=', $appUid);
    }

    /**
     * Scope a query to only include specific cases by APP_UID
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $cases
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSpecificCasesByUid($query, array $cases)
    {
        return $query->whereIn('APP_UID', $cases);
    }

    /**
     * Scope for query to get the application by APP_NUMBER
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $appNumber
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCase($query, $appNumber)
    {
        return $query->where('APPLICATION.APP_NUMBER', '=', $appNumber);
    }

    /**
     * Scope for query to get the positive cases
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePositiveCases($query)
    {
        return $query->where('APPLICATION.APP_NUMBER', '>', 0);
    }

    /**
     * Scope a query to only include specific cases
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $cases
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSpecificCases($query, array $cases)
    {
        return $query->whereIn('APPLICATION.APP_NUMBER', $cases);
    }

    /**
     * Scope more than one range of cases
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array $rangeCases
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRangeOfCases($query, array $rangeCases)
    {
        $query->where(function ($query) use ($rangeCases) {
            foreach ($rangeCases as $fromTo) {
                $fromTo = explode("-", $fromTo);
                if (count($fromTo) === 2) {
                    $from = $fromTo[0];
                    $to = $fromTo[1];
                    if ($to > $from) {
                        $query->orWhere(function ($query) use ($from, $to) {
                            $query->casesFrom($from)->casesTo($to);
                        });
                    }
                }
            }
        });
    }

    /**
     * Scope more than one range of cases
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array $cases
     * @param  array $rangeCases
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCasesOrRangeOfCases($query, array $cases, array $rangeCases)
    {
        $query->where(function ($query) use ($cases, $rangeCases) {
            // Get the cases related to the task self service
            $query->specificCases($cases);
            foreach ($rangeCases as $fromTo) {
                $fromTo = explode("-", $fromTo);
                if (count($fromTo) === 2) {
                    $from = $fromTo[0];
                    $to = $fromTo[1];
                    if ($to > $from) {
                        $query->orWhere(function ($query) use ($from, $to) {
                            $query->casesFrom($from)->casesTo($to);
                        });
                    }
                }
            }
        });
    }

    /**
     * Scope a query to only include cases from a range
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $from
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCasesFrom($query, int $from)
    {
        return $query->where('APPLICATION.APP_NUMBER', '>=', $from);
    }

    /**
     * Scope a query to only include cases from a range
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $to
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCasesTo($query, int $to)
    {
        return $query->where('APPLICATION.APP_NUMBER', '<=', $to);
    }

    /**
     * Scope for query to get the application by status Id
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param integer $status
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatusId($query, int $status)
    {
        return $query->where('APP_STATUS_ID', '=', $status);
    }

    /**
     * Scope a more status
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $statuses
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatusIds($query, array $statuses)
    {
        return $query->whereIn('APPLICATION.APP_STATUS_ID', $statuses);
    }

    /**
     * Scope a query to only include specific category
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $category
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('PROCESS.CATEGORY_ID', $category);
    }

    /**
     * Scope for query to get the applications by PRO_UID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $proUid
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProUid($query, $proUid)
    {
        return $query->where('APPLICATION.PRO_UID', '=', $proUid);
    }

    /**
     * Scope a query to only include a specific start date
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $from
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStartDateFrom($query, string $from)
    {
        return $query->where('APPLICATION.APP_CREATE_DATE', '>=', $from);
    }

    /**
     * Scope a query to only include a specific start date
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $to
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStartDateTo($query, string $to)
    {
        return $query->where('APPLICATION.APP_CREATE_DATE', '<=', $to);
    }

    /**
     * Scope a query to only include a specific finish date
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $from
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFinishCaseFrom($query, string $from)
    {
        return $query->where('APPLICATION.APP_FINISH_DATE', '>=', $from);
    }

    /**
     * Scope a query to only include a specific finish date
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $to
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFinishCaseTo($query, string $to)
    {
        return $query->where('APPLICATION.APP_FINISH_DATE', '<=', $to);
    }

    /**
     * Scope a query to only include a specific task
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $task
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTask($query, int $task)
    {
        return $query->where('APP_DELEGATION.TAS_ID', '=', $task);
    }

    /**
     * Scope join with process
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinProcess($query)
    {
        $query->leftJoin('PROCESS', function ($leftJoin) {
            $leftJoin->on('APPLICATION.PRO_UID', '=', 'PROCESS.PRO_UID');
        });

        return $query;
    }

    /**
     * Scope join with delegation
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinDelegation($query)
    {
        $query->leftJoin('APP_DELEGATION', function ($leftJoin) {
            $leftJoin->on('APPLICATION.APP_NUMBER', '=', 'APP_DELEGATION.APP_NUMBER');
        });

        return $query;
    }

    /**
     * Scope the Draft cases
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDraft($query, $user)
    {
        // Filter the status draft
        $query->statusId(Application::STATUS_DRAFT);
        // Filter the creator
        $query->creator($user);

        return $query;
    }

    /**
     * Get Applications by PRO_UID, ordered by APP_NUMBER.
     *
     * @param string $proUid
     *
     * @return object
     * @see ReportTables->populateTable()
     */
    public static function getByProUid($proUid)
    {
        $query = Application::query()
            ->select()
            ->proUid($proUid)
            ->positiveCases()
            ->orderBy('APP_NUMBER', 'ASC');
        return $query->get();
    }

    /**
     * Get information related to the case, avoiding to load the APP_DATA
     *
     * @param string $appUid
     *
     * @return array|bool
     */
    public static function getCase($appUid)
    {
        $query = Application::query()->select([
            'APP_NUMBER',
            'APP_STATUS',
            'PRO_UID',
            'PRO_ID',
            'APP_INIT_USER'
        ]);
        $query->appUid($appUid);
        $result = $query->get()->toArray();
        $firstElement = head($result);

        return $firstElement;
    }

    /**
     * Get app number
     *
     * @param string $appUid
     *
     * @return int
     */
    public static function getCaseNumber($appUid)
    {
        $query = Application::query()->select(['APP_NUMBER'])
            ->appUid($appUid)
            ->limit(1);
        $results = $query->get();
        $caseNumber = 0;
        $results->each(function ($item) use (&$caseNumber) {
            $caseNumber = $item->APP_NUMBER;
        });

        return $caseNumber;
    }

    /**
     * Update properties
     *
     * @param string $appUid
     * @param array $fields
     *
     * @return array
    */
    public static function updateColumns($appUid, $fields)
    {
        $properties = [];
        $properties['APP_ROUTING_DATA'] = !empty($fields['APP_ROUTING_DATA']) ? serialize($fields['APP_ROUTING_DATA']) : serialize([]);

        // This column will to update only when the thread is related to the user
        if (!empty($fields['APP_CUR_USER'])) {
            $properties['APP_CUR_USER'] = $fields['APP_CUR_USER'];
        }
        Application::query()->appUid($appUid)->update($properties);

        return $properties;
    }

    /**
     * Get Applications by PRO_UID, ordered by APP_NUMBER.
     *
     * @param string $proUid
     * @param int $status
     *
     * @return object
     * @see ReportTables->populateTable()
     */
    public static function getCountByProUid(string $proUid, $status = 2)
    {
        $query = Application::query()
            ->select()
            ->proUid($proUid)
            ->statusId($status)
            ->positiveCases();

        return $query->count(['APP_NUMBER']);
    }
}
