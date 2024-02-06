<?php

namespace ProcessMaker\Model;

use G;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'TASK';
    protected $primaryKey = 'TAS_ID';
    // We do not have create/update timestamps for this table
    public $timestamps = false;
    // The following types will execute without user and run automatically
    public static $typesRunAutomatically = [
        "END-MESSAGE-EVENT",
        "INTERMEDIATE-THROW-MESSAGE-EVENT",
        "INTERMEDIATE-CATCH-MESSAGE-EVENT",
        "INTERMEDIATE-CATCH-TIMER-EVENT",
        "SCRIPT-TASK",
        "SERVICE-TASK",
        "START-MESSAGE-EVENT",
        "START-TIMER-EVENT",
        "WEBENTRYEVENT",
    ];

    public function process()
    {
        return $this->belongsTo(Process::class, 'PRO_UID', 'PRO_UID');
    }

    public function delegations()
    {
        return $this->hasMany(Delegation::class, 'TAS_ID', 'TAS_ID');
    }

    /**
     * Scope a query to only include self-service
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsSelfService($query)
    {
        return $query->where('TAS_ASSIGN_TYPE', '=', 'SELF_SERVICE')
            ->where('TAS_GROUP_VARIABLE', '=', '');
    }

    /**
     * Get the title of the task
     *
     * @param  integer $tasId
     *
     * @return string
     */
    public function title($tasId)
    {
        $query = Task::query()->select('TAS_TITLE');
        $query->where('TAS_ID', $tasId);
        $results = $query->get();
        $title = '';
        $results->each(function ($item, $key) use (&$title) {
            $title = $item->TAS_TITLE;
            switch ($title) {
                case "INTERMEDIATE-THROW-EMAIL-EVENT":
                    $title = G::LoadTranslation('ID_INTERMEDIATE_THROW_EMAIL_EVENT');
                    break;
                case "INTERMEDIATE-THROW-MESSAGE-EVENT":
                    $title = G::LoadTranslation('ID_INTERMEDIATE_THROW_MESSAGE_EVENT');
                    break;
                case "INTERMEDIATE-CATCH-MESSAGE-EVENT":
                    $title = G::LoadTranslation('ID_INTERMEDIATE_CATCH_MESSAGE_EVENT');
                    break;
                case "INTERMEDIATE-CATCH-TIMER-EVENT":
                    $title = G::LoadTranslation('ID_INTERMEDIATE_CATCH_TIMER_EVENT');
                    break;
            }
        });

        return $title;
    }

    /**
     * Get task data
     *
     * @param  string $tasUid
     *
     * @return array
     */
    public function load($tasUid)
    {
        $query = Task::query();
        $query->where('TAS_UID', $tasUid);

        return $query->get()->toArray();
    }

    /**
     * Get task thread information
     *
     * @param string $appUid
     * @param string $tasUid
     * @param string $delIndex
     *
     * @return array
     */
    public function information(string $appUid, string $tasUid, string $delIndex)
    {
        // Load the the task information
        $taskInfo = $this->load($tasUid);
        $taskInfo = head($taskInfo);
        $taskType = $taskInfo['TAS_TYPE'];
        // Load the dates related to the thread
        $dates = Delegation::getDatesFromThread($appUid, $delIndex, $tasUid, $taskType);
        // Set the dates
        $taskInfo['INIT_DATE'] = !empty($dates['DEL_INIT_DATE']) ? $dates['DEL_INIT_DATE'] : G::LoadTranslation('ID_CASE_NOT_YET_STARTED');
        $taskInfo['DUE_DATE'] = !empty($dates['DEL_TASK_DUE_DATE']) ? $dates['DEL_TASK_DUE_DATE'] : G::LoadTranslation('ID_NOT_FINISHED');
        $taskInfo['FINISH'] = !empty($dates['DEL_FINISH_DATE']) ? $dates['DEL_FINISH_DATE'] : G::LoadTranslation('ID_NOT_FINISHED');
        $taskInfo['DURATION'] = !empty($dates['DEL_THREAD_DURATION']) ? $dates['DEL_THREAD_DURATION'] : G::LoadTranslation('ID_NOT_FINISHED');

        return $taskInfo;
    }
}
