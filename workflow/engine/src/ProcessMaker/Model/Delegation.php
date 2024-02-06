<?php

namespace ProcessMaker\Model;

use DateTime;
use G;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use ProcessMaker\Core\System;

class Delegation extends Model
{
    protected $table = "APP_DELEGATION";

    // We don't have our standard timestamp columns
    public $timestamps = false;

    // Static properties to preserve values
    public static $usrUid = '';
    public static $groups = [];

    /**
     * Returns the application this delegation belongs to
     */
    public function application()
    {
        return $this->belongsTo(Application::class, 'APP_UID', 'APP_UID');
    }

    /**
     * Returns the user this delegation belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'USR_ID', 'USR_ID');
    }

    /**
     * Return the process task this belongs to
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'TAS_ID', 'TAS_ID');
    }

    /**
     * Return the process this delegation belongs to
     */
    public function process()
    {
        return $this->belongsTo(Process::class, 'PRO_ID', 'PRO_ID');
    }

    /**
     * Scope a query to get the delegations from a case by APP_UID
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $appUid
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAppUid($query, $appUid)
    {
        return $query->where('APP_UID', '=', $appUid);
    }

    /**
     * Scope a query to only include open threads
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsThreadOpen($query)
    {
        return $query->where('APP_DELEGATION.DEL_THREAD_STATUS', '=', 'OPEN');
    }

    /**
     * Scope a query to only include threads without user
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNoUserInThread($query)
    {
        return $query->where('APP_DELEGATION.USR_ID', '=', 0);
    }

    /**
     * Scope a query to only include specific tasks
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array $tasks
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTasksIn($query, array $tasks)
    {
        return $query->whereIn('APP_DELEGATION.TAS_ID', $tasks);
    }

    /**
     * Scope a query to only include a specific case
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  integer $appNumber
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
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  integer $index
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
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  integer $task
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTask($query, $task)
    {
        return $query->where('APP_DELEGATION.TAS_ID', '=', $task);
    }

    /**
     * Searches for delegations which match certain criteria
     *
     * The query is related to advanced search with different filters
     * We can search by process, status of case, category of process, users, delegate date from and to
     *
     * @param integer $userId The USR_ID to search for (Note, this is no longer the USR_UID)
     * @param integer $start for the pagination
     * @param integer $limit for the pagination
     * @param string $search
     * @param integer $process the pro_id
     * @param integer $status of the case
     * @param string $dir if the order is DESC or ASC
     * @param string $sort name of column by sort, can be:
     *        [APP_NUMBER, APP_TITLE, APP_PRO_TITLE, APP_TAS_TITLE, APP_CURRENT_USER, APP_UPDATE_DATE, DEL_DELEGATE_DATE, DEL_TASK_DUE_DATE, APP_STATUS_LABEL]
     * @param string $category uid for the process
     * @param date $dateFrom
     * @param date $dateTo
     * @param string $filterBy name of column for a specific search, can be: [APP_NUMBER, APP_TITLE, TAS_TITLE]
     * @return array $result result of the query
     */

    public static function search(
        $userId = null,
        // Default pagination values
        $start = 0,
        $limit = 25,
        $search = null,
        $process = null,
        $status = null,
        $dir = null,
        $sort = null,
        $category = null,
        $dateFrom = null,
        $dateTo = null,
        $filterBy = 'APP_TITLE'
    ) {
        $search = trim($search);

        // Start the query builder, selecting our base attributes
        $selectColumns = [
            'APPLICATION.APP_NUMBER',
            'APPLICATION.APP_UID',
            'APPLICATION.APP_STATUS',
            'APPLICATION.APP_STATUS AS APP_STATUS_LABEL',
            'APPLICATION.PRO_UID',
            'APPLICATION.APP_CREATE_DATE',
            'APPLICATION.APP_FINISH_DATE',
            'APPLICATION.APP_UPDATE_DATE',
            'APPLICATION.APP_TITLE',
            'APP_DELEGATION.USR_UID',
            'APP_DELEGATION.TAS_UID',
            'APP_DELEGATION.USR_ID',
            'APP_DELEGATION.PRO_ID',
            'APP_DELEGATION.DEL_INDEX',
            'APP_DELEGATION.DEL_LAST_INDEX',
            'APP_DELEGATION.DEL_DELEGATE_DATE',
            'APP_DELEGATION.DEL_INIT_DATE',
            'APP_DELEGATION.DEL_FINISH_DATE',
            'APP_DELEGATION.DEL_TASK_DUE_DATE',
            'APP_DELEGATION.DEL_RISK_DATE',
            'APP_DELEGATION.DEL_THREAD_STATUS',
            'APP_DELEGATION.DEL_PRIORITY',
            'APP_DELEGATION.DEL_DURATION',
            'APP_DELEGATION.DEL_QUEUE_DURATION',
            'APP_DELEGATION.DEL_STARTED',
            'APP_DELEGATION.DEL_DELAY_DURATION',
            'APP_DELEGATION.DEL_FINISHED',
            'APP_DELEGATION.DEL_DELAYED',
            'APP_DELEGATION.DEL_DELAY_DURATION',
            'TASK.TAS_TITLE AS APP_TAS_TITLE',
            'TASK.TAS_TYPE AS APP_TAS_TYPE',
        ];
        $query = DB::table('APP_DELEGATION')->select(DB::raw(implode(',', $selectColumns)));

        // Add join for task, filtering for task title if needed
        // It doesn't make sense for us to search for any delegations that match tasks that are events or web entry
        $query->join('TASK', function ($join) use ($filterBy, $search) {
            $join->on('APP_DELEGATION.TAS_ID', '=', 'TASK.TAS_ID')
                ->whereNotIn('TASK.TAS_TYPE', [
                    'WEBENTRYEVENT',
                    'END-MESSAGE-EVENT',
                    'START-MESSAGE-EVENT',
                    'INTERMEDIATE-THROW',
                ]);
            if ($filterBy == 'TAS_TITLE' && $search) {
                $join->where('TASK.TAS_TITLE', 'LIKE', "%${search}%");
            }
        });

        // Add join for application, taking care of status and filtering if necessary
        $query->join('APPLICATION', function ($join) use ($filterBy, $search, $status, $query) {
            $join->on('APP_DELEGATION.APP_NUMBER', '=', 'APPLICATION.APP_NUMBER');
            if ($filterBy == 'APP_TITLE' && $search) {
                $config = System::getSystemConfiguration();
                if ((int)$config['disable_advanced_search_case_title_fulltext'] === 0) {
                    // Cleaning "fulltext" operators in order to avoid unexpected results
                    $search = str_replace(['-', '+', '<', '>', '(', ')', '~', '*', '"'],
                        ['', '', '', '', '', '', '', '', ''], $search);

                    // Build the "fulltext" expression
                    $search = '+"' . preg_replace('/\s+/', '" +"', addslashes($search)) . '"';

                    // Searching using "fulltext" index
                    $join->whereRaw("MATCH(APPLICATION.APP_TITLE) AGAINST('{$search}' IN BOOLEAN MODE)");
                } else {
                    // Searching using "like" operator
                    $join->where('APPLICATION.APP_TITLE', 'LIKE', "%${search}%");
                }
            }
            // Based on the below, we can further limit the join so that we have a smaller data set based on join criteria
            switch ($status) {
                case 1: //DRAFT
                    $join->where('APPLICATION.APP_STATUS_ID', 1);
                    break;
                case 2: //TO_DO
                    $join->where('APPLICATION.APP_STATUS_ID', 2);
                    break;
                case 3: //COMPLETED
                    $join->where('APPLICATION.APP_STATUS_ID', 3);
                    break;
                case 4: //CANCELLED
                    $join->where('APPLICATION.APP_STATUS_ID', 4);
                    break;
                case "PAUSED":
                    $join->where('APPLICATION.APP_STATUS', 'TO_DO');
                    break;
                default: //All status
                    // Don't do anything here, we'll need to do the more advanced where below
            }
        });

        // Add join for process, but only for certain scenarios such as category or process
        if ($category || $process || $sort == 'APP_PRO_TITLE') {
            $query->join('PROCESS', function ($join) use ($category) {
                $join->on('APP_DELEGATION.PRO_ID', '=', 'PROCESS.PRO_ID');
                if ($category) {
                    $join->where('PROCESS.PRO_CATEGORY', $category);
                }
            });
        }

        // Add join for user, but only for certain scenarios as sorting
        if ($sort == 'APP_CURRENT_USER') {
            $query->join('USERS', function ($join) use ($userId) {
                $join->on('APP_DELEGATION.USR_ID', '=', 'USERS.USR_ID');
            });
        }

        // Search for specified user
        if ($userId) {
            $query->where('APP_DELEGATION.USR_ID', $userId);
        }

        // Search for specified process
        if ($process) {
            $query->where('APP_DELEGATION.PRO_ID', $process);
        }

        // Search for an app/case number
        if ($filterBy == 'APP_NUMBER' && $search) {
            $query->where('APP_DELEGATION.APP_NUMBER', '=', $search);
        }

        // Date range filter
        if (!empty($dateFrom)) {
            $query->where('APP_DELEGATION.DEL_DELEGATE_DATE', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $dateTo = $dateTo . " 23:59:59";
            // This is inclusive
            $query->where('APP_DELEGATION.DEL_DELEGATE_DATE', '<=', $dateTo);
        }

        // Status Filter
        // This is tricky, the below behavior is combined with the application join behavior above
        switch ($status) {
            case 1: //DRAFT
                $query->where('APP_DELEGATION.DEL_THREAD_STATUS', 'OPEN');
                break;
            case 2: //TO_DO
                $query->where('APP_DELEGATION.DEL_THREAD_STATUS', 'OPEN');
                break;
            case 3: //COMPLETED
                $query->where('APP_DELEGATION.DEL_LAST_INDEX', 1);
                break;
            case 4: //CANCELLED
                $query->where('APP_DELEGATION.DEL_LAST_INDEX', 1);
                break;
            case "PAUSED":
                // Do nothing, as the app status check for TO_DO is performed in the join above
                break;
            default: //All statuses.
                $query->where(function ($query) {
                    // Check to see if thread status is open
                    $query->where('APP_DELEGATION.DEL_THREAD_STATUS', 'OPEN')
                        ->orWhere(function ($query) {
                            // Or, we make sure if the thread is closed, and it's the last delegation, and if the app is completed or cancelled
                            $query->where('APP_DELEGATION.DEL_THREAD_STATUS', 'CLOSED')
                                ->where('APP_DELEGATION.DEL_LAST_INDEX', 1)
                                ->whereIn('APPLICATION.APP_STATUS_ID', [3, 4]);
                        });
                });
                break;
        }

        // Add any sort if needed
        if($sort) {
            switch ($sort) {
                case 'APP_NUMBER':
                    $query->orderBy('APP_DELEGATION.APP_NUMBER', $dir);
                    break;
                case 'APP_PRO_TITLE':
                    // We can do this because we joined the process table if sorting by it
                    $query->orderBy('PROCESS.PRO_TITLE', $dir);
                    break;
                case 'APP_TAS_TITLE':
                    $query->orderBy('TASK.TAS_TITLE', $dir);
                    break;
                case 'APP_CURRENT_USER':
                    // We can do this because we joined the user table if sorting by it
                    $query->orderBy('USERS.USR_LASTNAME', $dir);
                    $query->orderBy('USERS.USR_FIRSTNAME', $dir);
                    break;
                default:
                    $query->orderBy($sort, $dir);
            }
        }

        // Add pagination to the query
        $query = $query->offset($start)
            ->limit($limit);

        // Fetch results and transform to a laravel collection
        $results = $query->get();

        // Transform with additional data
        $priorities = ['1' => 'VL', '2' => 'L', '3' => 'N', '4' => 'H', '5' => 'VH'];
        $results->transform(function ($item, $key) use ($priorities) {
            // Convert to an array as our results must be an array
            $item = json_decode(json_encode($item), true);
            // If it's assigned, fetch the user
            if($item['USR_ID']) {
                $user = User::where('USR_ID', $item['USR_ID'])->first();
            } else  {
                $user = null;
            }
            $process = Process::where('PRO_ID', $item['PRO_ID'])->first();

            // Rewrite priority string
            if ($item['DEL_PRIORITY']) {
                $item['DEL_PRIORITY'] = G::LoadTranslation("ID_PRIORITY_{$priorities[$item['DEL_PRIORITY']]}");
            }

            // Merge in desired application data
            if ($item['APP_STATUS']) {
                $item['APP_STATUS_LABEL'] = G::LoadTranslation("ID_${item['APP_STATUS']}");
            } else {
                $item['APP_STATUS_LABEL'] = $item['APP_STATUS'];
            }

            // Merge in desired process data
            // Handle situation where the process might not be in the system anymore
            $item['APP_PRO_TITLE'] = $process ? $process->PRO_TITLE : '';

            // Merge in desired user data
            $item['USR_LASTNAME'] = $user ? $user->USR_LASTNAME : '';
            $item['USR_FIRSTNAME'] = $user ? $user->USR_FIRSTNAME : '';
            $item['USR_USERNAME'] = $user ? $user->USR_USERNAME : '';

            //@todo: this section needs to use 'User Name Display Format', currently in the extJs is defined this
            $item["APP_CURRENT_USER"] = $item["USR_LASTNAME"] . ' ' . $item["USR_FIRSTNAME"];

            $item["APPDELCR_APP_TAS_TITLE"] = '';

            $item["USRCR_USR_UID"] = $item["USR_UID"];
            $item["USRCR_USR_FIRSTNAME"] = $item["USR_FIRSTNAME"];
            $item["USRCR_USR_LASTNAME"] = $item["USR_LASTNAME"];
            $item["USRCR_USR_USERNAME"] = $item["USR_USERNAME"];
            $item["APP_OVERDUE_PERCENTAGE"] = '';

            return $item;
        });

        // Remove any empty erroenous data
        $results = $results->filter();

        // Bundle into response array
        $response = [
            // Fake totalCount to show pagination
            'totalCount' => $start + $limit + 1,
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'data' => $results->values()->toArray(),
        ];

        return $response;
    }

    /**
     * Get participation information for a case
     *
     * @param string $appUid
     * @return array
     *
     * @see ProcessMaker\BusinessModel\Cases:getStatusInfo()
     */
    public static function getParticipatedInfo($appUid)
    {
        // Build the query
        $query = Delegation::query()->select([
            'APP_UID',
            'DEL_INDEX',
            'PRO_UID'
        ]);
        $query->appUid($appUid);
        $query->orderBy('DEL_INDEX', 'ASC');

        // Fetch results
        $results = $query->get();

        // Initialize the array to return
        $arrayData = [];

        // If the collection have at least one item, build the main array to return
        if ($results->count() > 0) {
            // Get the first item
            $first = $results->first();

            // Build the main array to return
            $arrayData = [
                'APP_STATUS' => 'PARTICIPATED', // Value hardcoded because we need to return the same structure previously sent
                'DEL_INDEX' => [], // Initialize this item like an array
                'PRO_UID' => $first->PRO_UID
            ];

            // Populate the DEL_INDEX key with the values of the items collected
            $results->each(function ($item) use (&$arrayData) {
                $arrayData['DEL_INDEX'][] = $item->DEL_INDEX;
            });
        }

        return $arrayData;
    }

    /**
     * Get the self-services query by user
     *
     * @param string $usrUid
     * @param bool $count
     * @param array $selectedColumns
     * @param string $categoryUid
     * @param string $processUid
     * @param string $textToSearch
     * @param string $sort
     * @param string $dir
     *
     * @return \Illuminate\Database\Query\Builder | string
     */
    public static function getSelfServiceQuery($usrUid, $count = false, $selectedColumns = ['APP_DELEGATION.APP_NUMBER', 'APP_DELEGATION.DEL_INDEX'],
        $categoryUid = null, $processUid = null, $textToSearch = null, $sort = null, $dir = null)
    {
        // Set the 'usrUid' property to preserve
        Delegation::$usrUid = $usrUid;

        // Get and build the groups parameter related to the user
        $groups = GroupUser::getGroups($usrUid);
        $groups = array_map(function ($value) {
            return "'" . $value['GRP_ID'] . "'";
        }, $groups);

        // Add dummy value to avoid syntax error in complex join
        $groups[] = "'-1'";

        // Set the 'groups' property to preserve
        Delegation::$groups = $groups;

        // Add an extra column with alias if is needed to join with the previous delegation
        if (array_search('APP_DELEGATION.DEL_PREVIOUS', $selectedColumns) !== false) {
            $selectedColumns[] = 'ADP.USR_ID';
        }

        // Start the first query
        $query1 = Delegation::query()->select($selectedColumns);

        // Add join clause with the previous APP_DELEGATION record if required
        if (array_search('APP_DELEGATION.DEL_PREVIOUS', $selectedColumns) !== false) {
            $query1->join('APP_DELEGATION AS ADP', function ($join)  {
                $join->on('APP_DELEGATION.APP_NUMBER', '=', 'ADP.APP_NUMBER');
                $join->on('APP_DELEGATION.DEL_PREVIOUS', '=', 'ADP.DEL_INDEX');
            });
        }

        // Add the join clause with TASK table
        $query1->join('TASK', function ($join) {
            // Build partial plain query for a complex Join, because Eloquent doesn't support this type of Join
            $complexJoin = "
                ((`APP_DELEGATION`.`APP_NUMBER`, `APP_DELEGATION`.`DEL_INDEX`, `APP_DELEGATION`.`TAS_ID`) IN (
                    SELECT
                        `APP_ASSIGN_SELF_SERVICE_VALUE`.`APP_NUMBER`,
                        `APP_ASSIGN_SELF_SERVICE_VALUE`.`DEL_INDEX`,
                        `APP_ASSIGN_SELF_SERVICE_VALUE`.`TAS_ID`
                    FROM
                        `APP_ASSIGN_SELF_SERVICE_VALUE`
                    INNER JOIN `APP_ASSIGN_SELF_SERVICE_VALUE_GROUP` ON
                        `APP_ASSIGN_SELF_SERVICE_VALUE`.`ID` = `APP_ASSIGN_SELF_SERVICE_VALUE_GROUP`.`ID`
                    WHERE (
                        `APP_ASSIGN_SELF_SERVICE_VALUE_GROUP`.`GRP_UID` = '%s' OR (
                        `APP_ASSIGN_SELF_SERVICE_VALUE_GROUP`.`ASSIGNEE_ID` IN (%s) AND
                        `APP_ASSIGN_SELF_SERVICE_VALUE_GROUP`.`ASSIGNEE_TYPE` = '2')
                    )
                ))";
            $groups = implode(',', Delegation::$groups);
            $complexJoin = sprintf($complexJoin, Delegation::$usrUid, $groups);

            // Add joins
            $join->on('APP_DELEGATION.TAS_ID', '=', 'TASK.TAS_ID');
            $join->on('TASK.TAS_ASSIGN_TYPE', '=', DB::raw("'SELF_SERVICE'"));
            $join->on('APP_DELEGATION.DEL_THREAD_STATUS', '=', DB::raw("'OPEN'"));
            $join->on('APP_DELEGATION.USR_ID', '=', DB::raw("'0'"))->
            whereRaw($complexJoin);
        });

        // Add join clause with APPLICATION table if required
        if (array_search('APPLICATION.APP_TITLE', $selectedColumns) !== false || !empty($textToSearch) || $sort == 'APP_TITLE') {
            $query1->join('APPLICATION', function ($join) {
                $join->on('APP_DELEGATION.APP_NUMBER', '=', 'APPLICATION.APP_NUMBER');
            });
        }

        // Add join clause with PROCESS table if required
        if (array_search('PROCESS.PRO_TITLE', $selectedColumns) !== false || !empty($categoryUid) || !empty($processUid) || !empty($textToSearch) || $sort == 'PRO_TITLE') {
            $query1->join('PROCESS', function ($join) use ($categoryUid, $processUid) {
                $join->on('APP_DELEGATION.PRO_ID', '=', 'PROCESS.PRO_ID');
                if (!empty($categoryUid)) {
                    $join->where('PROCESS.PRO_CATEGORY', $categoryUid);
                }
                if (!empty($processUid)) {
                    $join->where('PROCESS.PRO_UID', $processUid);
                }
            });
        }

        // Build where clause for the text to search
        if (!empty($textToSearch)) {
            $query1->where('APPLICATION.APP_TITLE', 'LIKE', "%$textToSearch%")
                ->orWhere('TASK.TAS_TITLE', 'LIKE', "%$textToSearch%")
                ->orWhere('PROCESS.PRO_TITLE', 'LIKE', "%$textToSearch%");
        }

        // Clean static properties
        Delegation::$usrUid = '';
        Delegation::$groups = [];

        // Get self services tasks related to the user
        $selfServiceTasks = TaskUser::getSelfServicePerUser($usrUid);

        if (!empty($selfServiceTasks)) {
            // Start the second query
            $query2 = Delegation::query()->select($selectedColumns);
            $query2->tasksIn($selfServiceTasks);
            $query2->isThreadOpen();
            $query2->noUserInThread();

            // Add join clause with the previous APP_DELEGATION record if required
            if (array_search('APP_DELEGATION.DEL_PREVIOUS', $selectedColumns) !== false) {
                $query2->join('APP_DELEGATION AS ADP', function ($join)  {
                    $join->on('APP_DELEGATION.APP_NUMBER', '=', 'ADP.APP_NUMBER');
                    $join->on('APP_DELEGATION.DEL_PREVIOUS', '=', 'ADP.DEL_INDEX');
                });
            }

            // Add the join clause with TASK table if required
            if (array_search('TASK.TAS_TITLE', $selectedColumns) !== false || !empty($textToSearch) || $sort == 'TAS_TITLE') {
                $query2->join('TASK', function ($join) {
                    $join->on('APP_DELEGATION.TAS_ID', '=', 'TASK.TAS_ID');
                });

            }
            // Add join clause with APPLICATION table if required
            if (array_search('APPLICATION.APP_TITLE', $selectedColumns) !== false || !empty($textToSearch) || $sort == 'APP_TITLE') {
                $query2->join('APPLICATION', function ($join) {
                    $join->on('APP_DELEGATION.APP_NUMBER', '=', 'APPLICATION.APP_NUMBER');
                });
            }

            // Add join clause with PROCESS table if required
            if (array_search('PROCESS.PRO_TITLE', $selectedColumns) !== false || !empty($categoryUid) || !empty($processUid) || !empty($textToSearch) || $sort == 'PRO_TITLE') {
                $query2->join('PROCESS', function ($join) use ($categoryUid, $processUid) {
                    $join->on('APP_DELEGATION.PRO_ID', '=', 'PROCESS.PRO_ID');
                    if (!empty($categoryUid)) {
                        $join->where('PROCESS.PRO_CATEGORY', $categoryUid);
                    }
                    if (!empty($processUid)) {
                        $join->where('PROCESS.PRO_UID', $processUid);
                    }
                });
            }

            // Build where clause for the text to search
            if (!empty($textToSearch)) {
                $query2->where('APPLICATION.APP_TITLE', 'LIKE', "%$textToSearch%")
                    ->orWhere('TASK.TAS_TITLE', 'LIKE', "%$textToSearch%")
                    ->orWhere('PROCESS.PRO_TITLE', 'LIKE', "%$textToSearch%");
            }

            // Build the complex query that uses "UNION DISTINCT" clause
            $query = sprintf('select '  . ($count ? 'count(*) as aggregate' : '*') .
                ' from ((%s) union distinct (%s)) self_service_cases' . (!empty($sort) && !empty($dir) ? ' ORDER BY %s %s' : ''),
                toSqlWithBindings($query1), toSqlWithBindings($query2), $sort, $dir);

            return $query;
        } else {
            if (!empty($sort) && !empty($dir)) {
                $query1->orderBy($sort, $dir);
            }
            return $query1;
        }
    }

    /**
     * Get the self-services cases by user
     *
     * @param string $usrUid
     * @param array $selectedColumns
     * @param string $categoryUid
     * @param string $processUid
     * @param string $textToSearch
     * @param string $sort
     * @param string $dir
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public static function getSelfService($usrUid, $selectedColumns = ['APP_DELEGATION.APP_NUMBER', 'APP_DELEGATION.DEL_INDEX'],
        $categoryUid = null, $processUid = null, $textToSearch = null, $sort = null, $dir = null, $offset = null, $limit = null)
    {
        // Initializing the variable to return
        $data = [];

        // Get the query
        $query = self::getSelfServiceQuery($usrUid, false, $selectedColumns, $categoryUid, $processUid, $textToSearch, $sort, $dir);

        // Get data
        if (!is_string($query)) {
            // Set offset and limit if were sent
            if (!is_null($offset) && !is_null($limit)) {
                $query->offset($offset);
                $query->limit($limit);
            }
            $items = $query->get();
            $items->each(function ($item) use (&$data) {
                $data[] = get_object_vars($item);
            });
        } else {
            // Set offset and limit if were sent
            if (!is_null($offset) && !is_null($limit)) {
                $query .= " LIMIT {$offset}, {$limit}";
            }
            $items = DB::select($query);
            foreach ($items as $item) {
                $data[] = get_object_vars($item);
            }
        }

        // Return data
        return $data;
    }

    /**
     * Count the self-services cases by user
     *
     * @param string $usrUid
     * @param string $categoryUid
     * @param string $processUid
     * @param string $textToSearch
     *
     * @return integer
     */
    public static function countSelfService($usrUid, $categoryUid = null, $processUid = null, $textToSearch = null)
    {
        // Get the query
        $query = self::getSelfServiceQuery($usrUid, true, ['APP_DELEGATION.APP_NUMBER', 'APP_DELEGATION.DEL_INDEX'],
            $categoryUid, $processUid, $textToSearch);

        // Get count value
        if (!is_string($query)) {
            $count = $query->count();
        } else {
            $result = DB::selectOne($query);
            $count = $result->aggregate;
        }

        // Return data
        return $count;
    }

    /**
     * This function get the current user related to the specific case and index
     *
     * @param integer $appNumber, Case number
     * @param integer $index, Index to review
     * @param string $status, The status of the thread
     *
     * @return string
     */
    public static function getCurrentUser($appNumber, $index, $status = 'OPEN')
    {
        $query = Delegation::query()->select('USR_UID');
        $query->where('APP_NUMBER', $appNumber);
        $query->where('DEL_INDEX', $index);
        $query->where('DEL_THREAD_STATUS', $status);
        $query->first();
        $results = $query->get();

        $userUid = '';
        $results->each(function ($item, $key) use (&$userUid) {
            $userUid = $item->USR_UID;
        });

        return $userUid;
    }

    /**
     * Return the open thread related to the task
     *
     * @param integer $appNumber, Case number
     * @param string $tasUid, The task uid
     *
     * @return array
     */
    public static function getOpenThreads($appNumber, $tasUid)
    {
        $query = Delegation::query()->select();
        $query->where('DEL_THREAD_STATUS', 'OPEN');
        $query->where('DEL_FINISH_DATE', null);
        $query->where('APP_NUMBER', $appNumber);
        $query->where('TAS_UID', $tasUid);
        $results = $query->get();

        $arrayOpenThreads = [];
        $results->each(function ($item, $key) use (&$arrayOpenThreads) {
            $arrayOpenThreads = $item->toArray();
        });

        return $arrayOpenThreads;
    }

    /**
     * Return if the user has participation in the case
     *
     * @param string $appUid, Case key
     * @param string $userUid, User key
     *
     * @return boolean
     */
    public static function participation($appUid, $userUid)
    {
        $query = Delegation::query()->select();
        $query->where('APP_UID', $appUid);
        $query->where('USR_UID', $userUid);
        $query->limit(1);

        return ($query->count() > 0);
    }

    /**
     * Return the thread related to the specific task-index
     *
     * @param string $appUid
     * @param string $delIndex
     * @param string $tasUid
     * @param string $taskType
     *
     * @return array
     */
    public static function getDatesFromThread(string $appUid, string $delIndex, string $tasUid, string $taskType)
    {
        $query = Delegation::query()->select([
            'DEL_INIT_DATE',
            'DEL_DELEGATE_DATE',
            'DEL_FINISH_DATE',
            'DEL_RISK_DATE',
            'DEL_TASK_DUE_DATE'
        ]);
        $query->where('APP_UID', $appUid);
        $query->where('DEL_INDEX', $delIndex);
        $query->where('TAS_UID', $tasUid);
        $results = $query->get();

        $thread = [];
        $results->each(function ($item, $key) use (&$thread, $taskType) {
            $thread = $item->toArray();
            if (in_array($taskType, Task::$typesRunAutomatically)) {
                $startDate = $thread['DEL_DELEGATE_DATE'];
            } else {
                $startDate = $thread['DEL_INIT_DATE'];
            }
            $endDate = $thread['DEL_FINISH_DATE'];
            // Calculate the task-thread duration
            if (!empty($startDate) && !empty($endDate)) {
                $initDate = new DateTime($startDate);
                $finishDate = new DateTime($endDate);
                $diff = $initDate->diff($finishDate);
                $format = ' %a ' . G::LoadTranslation('ID_DAY_DAYS');
                $format .= ' %H '. G::LoadTranslation('ID_HOUR_ABBREVIATE');
                $format .= ' %I '. G::LoadTranslation('ID_MINUTE_ABBREVIATE');
                $format .= ' %S '. G::LoadTranslation('ID_SECOND_ABBREVIATE');
                $thread['DEL_THREAD_DURATION'] = $diff->format($format);
            }
        });

        return $thread;
    }
}
