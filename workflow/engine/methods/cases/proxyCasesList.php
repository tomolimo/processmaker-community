<?php

use ProcessMaker\Model\Delegation;

/**
 * Authentication check for session. If not logged in, return json error
 */
if (!isset($_SESSION['USER_LOGGED'])) {
    $responseObject = new stdclass();
    $responseObject->error = G::LoadTranslation('ID_LOGIN_AGAIN');
    $responseObject->success = true;
    $responseObject->lostSession = true;
    print G::json_encode($responseObject);
    die();
}


/**
 * Do input filtering, although filtering should be done on the frontend rendering, not here
 */
$filter = new InputFilter();
$_GET = $filter->xssFilterHard($_GET);
$_REQUEST = $filter->xssFilterHard($_REQUEST);
$userLogged = $filter->xssFilterHard($_SESSION['USER_LOGGED']);

$filters = [];
// Callback in the UI to utilize
$callback = isset($_REQUEST["callback"]) ? $_REQUEST["callback"] : "stcCallback1001";

// Sort column
$filters['sort'] = $sort = isset($_REQUEST["sort"]) ? $_REQUEST["sort"] : "APP_NUMBER";
// Sort direction
$filters['dir'] = $dir = isset($_REQUEST["dir"]) ? $_REQUEST["dir"] : "DESC";

// Pagination control
$filters['start'] = $start = !empty($_REQUEST["start"]) ? $_REQUEST["start"] : 0;
$filters['limit'] = $limit = !empty($_REQUEST["limit"]) ? $_REQUEST["limit"] : 25;

// Our search filter
$filter = isset($_REQUEST["filter"]) ? $_REQUEST["filter"] : "";

// What process
$filters['process'] = $process = isset($_REQUEST["process"]) ? $_REQUEST["process"] : "";
$filters['process_label'] = $processLabel = isset($_REQUEST["process_label"]) ? $_REQUEST["process_label"] : "";

// What category
$filters['category'] = $category = isset($_REQUEST["category"]) ? $_REQUEST["category"] : "";

// What status
$status = isset($_REQUEST["status"]) ? strtoupper($_REQUEST["status"]) : "";
$filters['filterStatus'] = $filterStatus = isset($_REQUEST["filterStatus"]) ? strtoupper($_REQUEST["filterStatus"]) : "";

// What user
$filters['user'] = $user = isset($_REQUEST["user"]) ? $_REQUEST["user"] : "";
$filters['user_label'] = $userLabel = isset($_REQUEST["user_label"]) ? $_REQUEST["user_label"] : "";

// What keywords to search
$filters['search'] = $search = isset($_REQUEST["search"]) ? $_REQUEST["search"] : "";

// What kind of action
$action = isset($_GET["action"]) ? $_GET["action"] : (isset($_REQUEST["action"]) ? $_REQUEST["action"] : "todo");

// What kind of search
$type = isset($_GET["type"]) ? $_GET["type"] : (isset($_REQUEST["type"]) ? $_REQUEST["type"] : "extjs");

// Date ranges
$filters['dateFrom'] = $dateFrom = isset($_REQUEST["dateFrom"]) ? substr($_REQUEST["dateFrom"], 0, 10) : "";
$filters['dateTo'] = $dateTo = isset($_REQUEST["dateTo"]) ? substr($_REQUEST["dateTo"], 0, 10) : "";

// First define if we need to return empty data the first time
$first = isset($_REQUEST["first"]);

// Do search define if the action was defined from the button search
$doSearch = isset($_REQUEST["doSearch"]);

// Open case from case link
$openApplicationUid = (isset($_REQUEST['openApplicationUid']) && $_REQUEST['openApplicationUid'] != '') ?
    $_REQUEST['openApplicationUid'] : null;
$search = (!is_null($openApplicationUid)) ? $openApplicationUid : $search;
$filters['columnSearch'] = $columnSearch = isset($_REQUEST["columnSearch"]) ? strtoupper($_REQUEST["columnSearch"]) : "";

if ($sort == 'CASE_SUMMARY' || $sort == 'CASE_NOTES_COUNT') {
    $sort = 'APP_NUMBER';//DEFAULT VALUE
}
if ($sort == 'APP_STATUS_LABEL') {
    $sort = 'APP_STATUS';
}

//Load Configurations
$conf = new Configurations();
//Load the user preferences
$conf->getUserPreferences('FILTERS', $userLogged);
//Save the filters used
if ($doSearch && (empty($conf->aConfig['FILTERS']['advanced']) || $conf->aConfig['FILTERS']['advanced'] != $filters)) {
    //The user does not have filters or we need to update the user preferences
    $conf->aConfig['FILTERS']['advanced'] = $filters;
    $conf->saveConfig('USER_PREFERENCES', '', '', $userLogged);
}

try {
    $result = [];
    //Define the user logged into the system
    $userUid = (isset($userLogged) && $userLogged != "") ? $userLogged : null;

    if ($action == 'search') {
        //Return empty if does not have filters
        if (empty($conf->aConfig['FILTERS']['advanced'])) {
            $result['totalCount'] = 0;
            $result['data'] = [];
            $result = G::json_encode($result);
            echo $result;
            return;
        } else {
            //Define the user
            $user = ($user == "CURRENT_USER") ? $userUid : $user;
            $userUid = $user;

            //Get the data from the specific search
            $data = Delegation::search(
                $userUid,
                $start,
                $limit,
                $search,
                $process,
                $filterStatus,
                $dir,
                $sort,
                $category,
                $dateFrom,
                $dateTo,
                $columnSearch
            );
        }
    } else {
        //We check if we need to return empty
        if ($action == "to_reassign" && $first) {
            $result['totalCount'] = 0;
            $result['data'] = [];
            $result = G::json_encode($result);
            echo $result;
            return;
        }

        //This section is used by the community version
        $apps = new Applications();
        $data = $apps->getAll(
            $userUid,
            $start,
            $limit,
            $action,
            $filter,
            $search,
            $process,
            $filterStatus,
            $type,
            $dateFrom,
            $dateTo,
            $callback,
            $dir,
            (strpos($sort, ".") !== false) ? $sort : "APP_CACHE_VIEW." . $sort,
            $category
        );
    }

    $data['data'] = \ProcessMaker\Util\DateTime::convertUtcToTimeZone($data['data']);
    $result = G::json_encode($data);
    echo $result;
} catch (Exception $e) {
    $msg = array("error" => $e->getMessage());
    echo G::json_encode($msg);
}
