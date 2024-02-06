<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Configurations;
use Exception;
use G;
use Illuminate\Database\Eloquent\Model;
use RBAC;

class Process extends Model
{
    use HasFactory;

    // Set our table name
    protected $table = 'PROCESS';
    protected $primaryKey = 'PRO_ID';

    // Our custom timestamp columns
    const CREATED_AT = 'PRO_CREATE_DATE';
    const UPDATED_AT = 'PRO_UPDATE_DATE';

    // Columns to see in the process list
    public $listColumns = [
        'PRO_UID',
        'PRO_TITLE',
        'PRO_DESCRIPTION',
        'PRO_PARENT',
        'PRO_STATUS',
        'PRO_TYPE',
        'PRO_CATEGORY',
        'PRO_UPDATE_DATE',
        'PRO_CREATE_DATE',
        'PRO_CREATE_USER',
        'PRO_DEBUG',
        'PRO_TYPE_PROCESS',
        'USR_UID',
        'USR_USERNAME',
        'USR_FIRSTNAME',
        'USR_LASTNAME',
        'CATEGORY_UID',
        'CATEGORY_NAME'
    ];

    /**
     * Get the columns related to the process list
     * @return array
     */
    public function getListColumns()
    {
        return $this->listColumns;
    }

    /**
     * Returns the task related to the process belongs to
     */
    public function tasks()
    {
        return $this->belongsTo(Task::class, 'PRO_ID', 'PRO_ID');
    }

    /**
     * Returns the user creator belongs to
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'PRO_CREATE_USER', 'USR_UID');
    }

    /**
     * Returns the category related to the process belongs to
     */
    public function category()
    {
        return $this->belongsTo(ProcessCategory::class, 'PRO_CATEGORY', 'CATEGORY_UID');
    }

    /**
     * Scope a query to specific process
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $proUid
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcess($query, $proUid)
    {
        return $query->where('PRO_UID', '=', $proUid);
    }

    /**
     * Scope a query to specific title
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $title
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTitle($query, $title)
    {
        return $query->where('PRO_TITLE', 'LIKE', "%{$title}%");
    }

    /**
     * Scope a query to exclude a specific status
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNoStatus($query, $status = 'DISABLED')
    {
        return $query->where('PRO_STATUS', '!=', $status);
    }

    /**
     * Scope a query to include subprocess
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSubProcess($query)
    {
        return $query->where('PRO_SUBPROCESS', '=', 1);
    }

    /**
     * Scope a query to include a specific process categoryId
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param int $category
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCategoryId($query, $category)
    {
        return $query->where('PROCESS.CATEGORY_ID', $category);
    }

    /**
     * Scope a query to include a specific process category
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('PROCESS.PRO_CATEGORY', $category);
    }

    /**
     * Scope a query to include the user owner or public process
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $userUid
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePerUser($query, string $userUid)
    {
        $query->where(function ($query) use ($userUid) {
            $query->orWhere('PRO_CREATE_USER', $userUid);
            $query->orWhere('PRO_TYPE_PROCESS', 'PUBLIC');
        });
        return $query;
    }

    /**
     * Scope a query to include the process related to the specific user
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinUsers($query)
    {
        $query->join('USERS', function ($join) {
            $join->on('PROCESS.PRO_CREATE_USER', '=', 'USERS.USR_UID');
        });
        return $query;
    }

    /**
     * Scope a query to join with categories
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeJoinCategory($query)
    {
        $query->leftJoin('PROCESS_CATEGORY', function ($join) {
            $join->on('PROCESS.PRO_CATEGORY', '=', 'PROCESS_CATEGORY.CATEGORY_UID');
        });
        return $query;
    }

    /**
     * Scope a query to include a specific process status
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('PROCESS.PRO_STATUS', $status);
    }

    /**
     * Return process
     * @param int|string $id
     * @param string $key
     * 
     * @return array
     */
    public static function getIds($id, $key)
    {
        $process = Process::query()
            ->select()
            ->where($key, $id)
            ->get()
            ->values()
            ->toArray();

        return $process;
    }

    /**
     * Obtains the process list for an specific user and/or for the specific category
     *
     * @param string $categoryUid
     * @param string $userUid
     * @return array
     *
     * @see ProcessMaker\BusinessModel\Light::getProcessList()
     */
    public function getProcessList($categoryUid, $userUid)
    {
        $selectedColumns = ['PRO_UID', 'PRO_TITLE'];
        $query = Process::query()
            ->select($selectedColumns)
            ->where('PRO_STATUS', 'ACTIVE')
            ->where('PRO_CREATE_USER', $userUid);

        if (!empty($categoryUid)) {
            $query->where('PRO_CATEGORY', $categoryUid);
        }

        return ($query->get()->values()->toArray());
    }

    /**
     * Obtains the list of private processes assigned to the user
     * 
     * @param string $userUid
     * @return array
     */
    public static function getProcessPrivateListByUser($userUid)
    {
        $query = Process::query()
            ->select()
            ->where('PRO_CREATE_USER', $userUid)
            ->where('PRO_TYPE_PROCESS', 'PRIVATE');

        return ($query->get()->values()->toArray());
    }

    /**
     * Converts the private processes to public
     * 
     * @param array $privateProcesses
     * @return void
     */
    public static function convertPrivateProcessesToPublicAndUpdateUser($privateProcesses, $userUid)
    {
        $admin = RBAC::ADMIN_USER_UID;

        $processes = array_column($privateProcesses, 'PRO_ID');
        Process::whereIn('PRO_ID', $processes)
            ->update(['PRO_TYPE_PROCESS' => 'PUBLIC']);

        Process::where('PRO_CREATE_USER', $userUid)
            ->update(['PRO_CREATE_USER' => $admin]);
    }

    /**
     * Get the process list applying some extra filters
     *
     * @param string $catUid
     * @param string $proUid
     * @param string $title
     * @param string $userUid
     * @param int $start
     * @param int $limit
     * @param string $dir
     * @param string $sort
     * @param boolean $counterByProcess
     * @param boolean $subProcess
     *
     * @return array
     * @throw Exception
     */
    public static function getProcessesFilter(
        $catUid = null,
        $proUid = null,
        $title = null,
        $userUid = null,
        $start = 0,
        $limit = 25,
        $dir = 'ASC',
        $sort = 'PRO_CREATE_DATE',
        $counterByProcess = true,
        $subProcess = false
    )
    {
        $process = new Process();
        $rows = $process->getListColumns();
        if (!in_array($sort, $rows)) {
            throw new Exception('The column ' . $sort . ' does not exist');
        }
        // Select rows
        $query = Process::query()->select($rows)->noStatus();

        // Join with users
        $query->joinUsers();

        // Join with category
        $query->joinCategory();

        // Check if the owner is the user logged or if the process is PUBLIC
        if (!empty($userUid)) {
            //Only process PRO_TYPE_PROCESS = "PUBLIC" or related user owner
            $query->perUser($userUid);
        }

        // Check if we can list only the sub-process
        if ($subProcess) {
            $query->subProcess();
        }

        // Specific process
        if ($proUid) {
            $query->process($proUid);
        }

        // Specific process title
        if ($title) {
            $query->title($title);
        }

        // Search a specific category
        if (!empty($catUid)) {
            if ($catUid == 'NONE') {
                // Processes without category
                $query->category('');
            } else {
                // Processes with the category $catUid
                $query->category($catUid);
            }
        }

        // Order the data
        $query->orderBy($sort, $dir);

        // Define the pagination
        $query->offset($start)->limit($limit);

        // Get the results
        $results = $query->get();

        // Define the class for get workspace configurations
        $systemConf = new Configurations();
        $systemConf->loadConfig($obj, 'ENVIRONMENT_SETTINGS', '');
        $mask = isset($systemConf->aConfig['dateFormat']) ? $systemConf->aConfig['dateFormat'] : '';

        // Prepare the final result
        $results->transform(function ($item, $key) use ($counterByProcess, $systemConf, $mask) {
            // Get the counter related to the status
            // todo: those counters needs to remove when the PMCORE-2314 was implemented
            $item['CASES_COUNT_DRAFT'] = $counterByProcess ? Application::getCountByProUid($item['PRO_UID'], 1) : 0;
            $item['CASES_COUNT_TO_DO'] = $counterByProcess ? Application::getCountByProUid($item['PRO_UID'], 2) : 0;
            $item['CASES_COUNT_COMPLETED'] = $counterByProcess ? Application::getCountByProUid($item['PRO_UID'], 3) : 0;
            $item['CASES_COUNT_CANCELLED'] = $counterByProcess ? Application::getCountByProUid($item['PRO_UID'], 4) : 0;
            $item['CASES_COUNT'] = $item['CASES_COUNT_DRAFT'] + $item['CASES_COUNT_TO_DO'] + $item['CASES_COUNT_COMPLETED'] + $item['CASES_COUNT_CANCELLED'];

            // Get the description
            // todo: we will to remove htmlspecialchars but frontEnd needs to add application wide XSS prevention measures
            $item['PRO_DESCRIPTION'] = empty($item['PRO_DESCRIPTION']) ? '' : htmlspecialchars($item['PRO_DESCRIPTION']);

            // Get the type: bpmn or classic
            $bpmnProcess = BpmnProject::isBpmnProcess($item['PRO_UID']);
            $item['PROJECT_TYPE'] = ($bpmnProcess) ? 'bpmn' : 'classic';

            // Get the process type: PUBLIC or PRIVATE
            $item['PRO_TYPE_PROCESS'] = ($item['PRO_TYPE_PROCESS'] == 'PUBLIC') ? G::LoadTranslation("ID_PUBLIC") : G::LoadTranslation("ID_PRIVATE");

            // Get information about the owner, with the format defined
            $creatorOwner = $systemConf->usersNameFormat($item['USR_USERNAME'], $item['USR_FIRSTNAME'], $item['USR_LASTNAME']);
            $item['PRO_CREATE_USER_LABEL'] = empty($creatorOwner) ? $item['USR_FIRSTNAME'] . ' ' . $item['USR_LASTNAME'] : $creatorOwner;

            // Get debug label
            $item['PRO_DEBUG_LABEL'] = ($item['PRO_DEBUG'] == '1') ? G::LoadTranslation('ID_ON') : G::LoadTranslation('ID_OFF');

            // Get status label
            $item['PRO_STATUS_LABEL'] = $item['PRO_STATUS'] == 'ACTIVE' ? G::LoadTranslation('ID_ACTIVE') : G::LoadTranslation('ID_INACTIVE');

            // Get category label
            $item['PRO_CATEGORY_LABEL'] = trim($item['PRO_CATEGORY']) != '' ? $item['CATEGORY_NAME'] : G::LoadTranslation('ID_PROCESS_NONE_CATEGORY');

            // Apply the date format defined in environment
            if (!empty($mask)) {
                $item['PRO_CREATE_DATE_LABEL'] = !empty($item['PRO_CREATE_DATE']) ? $item['PRO_CREATE_DATE']->format($mask) : '';
                $item['PRO_UPDATE_DATE_LABEL'] = !empty($item['PRO_UPDATE_DATE']) ? $item['PRO_UPDATE_DATE']->format($mask) : '';
            } else {
                $item['PRO_CREATE_DATE_LABEL'] = !empty($item['PRO_CREATE_DATE']) ? $item['PRO_CREATE_DATE']->format('Y-m-d H:i:s') : '';
                $item['PRO_UPDATE_DATE_LABEL'] = !empty($item['PRO_UPDATE_DATE']) ? $item['PRO_UPDATE_DATE']->format('Y-m-d H:i:s') : '';
            }

            return $item;
        });

        return $results->values()->toArray();
    }

    /**
     * Get the number of rows corresponding to the process
     *
     * @param string $userUid
     * @return integer
     */
    public static function getCounter($userUid = '')
    {
        $query = Process::query()->select();
        $query->noStatus();
        if (!empty($userUid)) {
            //Only process PRO_TYPE_PROCESS = "PUBLIC" or related user owner
            $query->perUser($userUid);
        }

        return $query->count();
    }

    /**
     * Get all processes, paged optionally, can be sent a string to filter results by "PRO_TITLE"
     *
     * @param string $text
     * @param int $catId
     * @param int $offset
     * @param int $limit
     * @param bool $paged
     *
     * @return array
     */
    public static function getProcessesForHome($text = null, $catId = 0, $offset = null, $limit = null, $paged = true)
    {
        // Get base query
        $query = Process::query()->select(['PRO_ID', 'PRO_TITLE']);

        // Set "PRO_TITLE" condition if is sent
        if (!is_null($text)) {
            $query->title($text);
        }

        // Set "CATEGORY_ID" condition if is sent
        if ($catId) {
            $query->categoryId($catId);
        }

        // Set "PRO_STATUS" condition
        $query->status('ACTIVE');

        if ($paged) {
            // Set pagination if offset and limit are sent
            if (!is_null($offset) && !is_null($limit)) {
                $query->offset($offset);
                $query->limit($limit);
            }
        }


        // Order by "PRO_TITLE"
        $query->orderBy('PRO_TITLE');

        // Return processes
        return $query->get()->toArray();
    }

    /**
     * Return true if process is active, false otherwise.
     * @param int|string $proId
     * @param string $key
     * 
     * @return bool
     */
    public static function isActive($proId, string $key = 'PRO_ID'): bool
    {
        $process = Process::query()
            ->where($key, $proId)
            ->where('PRO_STATUS', 'ACTIVE')
            ->first();
        return !empty($process);
    }
}
