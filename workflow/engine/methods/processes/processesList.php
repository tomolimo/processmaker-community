<?php

/**
 * processesList.php
 *
 * Get an overview information about the all the processes
 *
 * @link https://wiki.processmaker.com/3.2/Processes
 */

use ProcessMaker\Exception\RBACException;
use ProcessMaker\Model\Process;
use ProcessMaker\Util\DateTime;

// Include global object RBAC
global $RBAC;

// Check if the current user have the correct permissions to access to this resource, if not throws a RBAC Exception with code 403
if ($RBAC->userCanAccess('PM_FACTORY') !== 1) {
    throw new RBACException('ID_ACCESS_DENIED', 403);
}

require_once 'classes/model/Process.php';

$start = isset($_POST['start']) ? $_POST['start'] : 0;
$limit = isset($_POST['limit']) ? $_POST['limit'] : 25;
$dir = isset($_POST['dir']) ? $_POST['dir'] : 'ASC';
$sort = isset($_POST['sort']) ? $_POST['sort'] : 'PRO_CREATE_DATE';
switch ($sort) {
    case 'PRO_DEBUG_LABEL':
        $sort = 'PRO_DEBUG';
        break;
    case 'PRO_CREATE_USER_LABEL':
        $sort = 'USR_UID';
        break;
    case 'PRO_STATUS_LABEL':
        $sort = 'PRO_STATUS';
        break;
    case 'PROJECT_TYPE':
        $sort = 'PRO_TYPE';
        break;
    case 'PRO_CATEGORY_LABEL':
        $sort = 'PRO_CATEGORY';
        break;
    case 'PRO_CREATE_DATE_LABEL':
        $sort = 'PRO_CREATE_DATE';
        break;
    case 'PRO_UPDATE_DATE_LABEL':
        $sort = 'PRO_UPDATE_DATE';
        break;
    default:
        // keep the sort value
}
$totalCount = 0;

// Get the category uid to search
$catUid = !empty($_POST['category']) ? $_POST['category'] : null;

// Get the process name to search
$process = !empty($_POST['processName']) ? $_POST['processName'] : null;
$usrUid = $_SESSION["USER_LOGGED"];
$proData = Process::getProcessesFilter(
    $catUid,
    null,
    $process,
    $usrUid,
    $start,
    $limit,
    $dir,
    $sort
);

$response = new stdclass();
$response->data = DateTime::convertUtcToTimeZone($proData);
$response->totalCount = Process::getCounter($usrUid);

echo G::json_encode($response);

