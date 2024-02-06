<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use G;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

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

    const DUMMY_TASKS = [
        'END-EMAIL-EVENT',
        'INTERMEDIATE-CATCH-TIMER-EVENT',
        'INTERMEDIATE-THROW-EMAIL-EVENT',
        'START-TIMER-EVENT',
        'SCRIPT-TASK',
        'WEBENTRYEVENT',
        'END-MESSAGE-EVENT',
        'GATEWAYTOGATEWAY'
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
     * Get the task by taskId
     * 
     * @param int $tasId
     * @return \ProcessMaker\Model\Task
     */
    public static function getTask($tasId)
    {
        $query = Task::query()->select()->where('TAS_ID', $tasId);
        return $query->first();
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
     * Scope a query to specific title
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $title
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTitle($query, $title)
    {
        return $query->where('TAS_TITLE', 'LIKE', "%{$title}%");
    }

    /**
     * Scope a query to include a specific process
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param int $proId
     * @return \Illuminate\Database\Eloquent\Builder
     * @todo Auto populate the PRO_ID in TASK table
     */
    public function scopeProcess($query, $proId = '')
    {
        $query->join('PROCESS', function ($join) use ($proId) {
            $join->on('TASK.PRO_UID', '=', 'PROCESS.PRO_UID');
            if (!empty($proId)) {
                $join->where('PROCESS.PRO_ID', '=', $proId);
            }
        });

        return $query;
    }

    /**
     * Scope a query to exclude determined tasks types
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExcludedTasks($query)
    {
        $query->whereNotIn('TAS_TYPE', Task::$typesRunAutomatically)
            ->whereNotIn('TAS_TYPE', Task::DUMMY_TASKS);

        return $query;
    }

    /**
     * Get the title of the task
     *
     * @param  integer $tasId
     *
     * @return array
     */
    public static function title($tasId)
    {
        $query = Task::query()->select('TAS_TITLE', 'TAS_TYPE');
        $query->where('TAS_ID', $tasId);
        $results = $query->get();
        $title = '';
        $type = '';
        $results->each(function ($item, $key) use (&$title, &$type) {
            $title = $item->TAS_TITLE;
            $type = $item->TAS_TYPE;
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
                case "SCRIPT-TASK":
                    $title = G::LoadTranslation('ID_SCRIPT_TASK_UNTITLED');
                    break;
                case "SERVICE-TASK":
                    $title = G::LoadTranslation('ID_SERVICE_TASK_UNTITLED');
                    break;
                default:
                    $title = G::LoadTranslation('ID_ANONYMOUS');
            }
            switch ($type) {
                case "INTERMEDIATE-THROW-EMAIL-EVENT":
                    $type = G::LoadTranslation('ID_EMAIL_EVENT');
                    break;
                case "INTERMEDIATE-THROW-MESSAGE-EVENT":
                case "INTERMEDIATE-CATCH-MESSAGE-EVENT":
                    $type = G::LoadTranslation('ID_MESSAGE_EVENT');
                    break;
                case "INTERMEDIATE-CATCH-TIMER-EVENT":
                    $type = G::LoadTranslation('ID_TIMER_EVENT');
                    break;
                case "SCRIPT-TASK":
                    $type = G::LoadTranslation('ID_SCRIPT_TASK');
                    break;
                case "SERVICE-TASK":
                    $type = G::LoadTranslation('ID_SERVICE_TASK');
                    break;
                default:
                    $type = G::LoadTranslation('ID_NONE');
            }
        });

        return [
            'title' => $title,
            'type' => $type,
        ];
    }

    /**
     * Get the title of the task
     *
     * @param string $tasUid
     *
     * @return string
     */
    public function taskCaseTitle(string $tasUid)
    {
        $query = Task::query()->select(['TAS_DEF_TITLE']);
        $query->where('TAS_UID', $tasUid);
        $query->limit(1);
        $results = $query->get();
        $title = '';
        $results->each(function ($item) use (&$title) {
            $title = $item->TAS_DEF_TITLE;
        });

        return $title;
    }

    /**
     * Get the description of the task
     *
     * @param string $tasUid
     *
     * @return string
     */
    public function taskCaseDescription(string $tasUid)
    {
        $query = Task::query()->select(['TAS_DEF_DESCRIPTION']);
        $query->where('TAS_UID', $tasUid);
        $query->limit(1);
        $results = $query->get();
        $title = '';
        $results->each(function ($item) use (&$title) {
            $title = $item->TAS_DEF_DESCRIPTION;
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

    /**
     * Set the TAS_DEF_TITLE value
     * 
     * @param string $evnUid
     * @param string $caseTitle
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function setTaskDefTitle($evnUid, $caseTitle)
    {
        $query = Task::select(['TASK.TAS_UID']);
        $query->join('ELEMENT_TASK_RELATION', function ($join) use ($evnUid) {
            $join->on('ELEMENT_TASK_RELATION.TAS_UID', '=', 'TASK.TAS_UID')
                ->where('ELEMENT_TASK_RELATION.ELEMENT_UID', '=', $evnUid);
        });
        $query->update(['TASK.TAS_DEF_TITLE' => $caseTitle]);

        return $query;
    }

    /**
     * Get the TAS_DEF_TITLE value
     * 
     * @param string $evnUid
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getTaskDefTitle($evnUid)
    {
        $query = Task::select(['TASK.TAS_DEF_TITLE']);
        $query->join('ELEMENT_TASK_RELATION', function ($join) use ($evnUid) {
            $join->on('ELEMENT_TASK_RELATION.TAS_UID', '=', 'TASK.TAS_UID')
                ->where('ELEMENT_TASK_RELATION.ELEMENT_UID', '=', $evnUid);
        });

        $res = $query->first();
        if (is_null($res)) {
            return "";
        } else {
            return $res->TAS_DEF_TITLE;
        }
    }

    /**
     * Get all tasks, paged optionally, can be sent a string to filter results by "TAS_TITLE"
     *
     * @param string $text
     * @param string $proId
     * @param int $offset
     * @param int $limit
     *
     * @return array
     */
    public static function getTasksForHome($text = null, $proId = null, $offset = null, $limit = null)
    {
        // Get base query
        $query = Task::query()->selectRaw("
            TAS_ID,
            TAS_TITLE,
            CONCAT(TAS_TITLE,' - ',PRO_TITLE) AS TAS_PROCESS
            ");

        // Set "TAS_TITLE" condition if is sent
        if (!is_null($text)) {
            $query->title($text);
        }

        // Join with process
        $query->process($proId);

        // Exclude the determined tasks
        $query->excludedTasks();

        // Set pagination if offset and limit are sent
        if (!is_null($offset) && !is_null($limit)) {
            $query->offset($offset);
            $query->limit($limit);
        }

        // Order by "TAS_TITLE"
        $query->orderBy('TAS_TITLE');

        // Return tasks
        return $query->get()->toArray();
    }
}
