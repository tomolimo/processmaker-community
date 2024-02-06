<?php

use ProcessMaker\BusinessModel\Files\Cron;
use ProcessMaker\Exception\RBACException;

// Include global object RBAC
global $RBAC;

// Check if the current user have the correct permissions to access to this resource, if not throws a RBAC Exception with code 403
if ($RBAC->userCanAccess('PM_SETUP') !== 1 || $RBAC->userCanAccess('PM_SETUP_LOGS') !== 1) {
    throw new RBACException('ID_ACCESS_DENIED', 403);
}

$option = isset($_REQUEST["option"]) ? $_REQUEST["option"] : null;

$response = [];

switch ($option) {
    case "LST":
        $pageSize = $_REQUEST["pageSize"];
        $workspace = config("system.workspace");
        $status = $_REQUEST["status"];
        $dateFrom = $_REQUEST["dateFrom"];
        $dateTo = $_REQUEST["dateTo"];

        $filter = [
            "workspace" => $workspace,
            "status" => $status,
            "dateFrom" => str_replace("T00:00:00", null, $dateFrom),
            "dateTo" => str_replace("T00:00:00", null, $dateTo)
        ];

        $start = (int) isset($_REQUEST["start"]) ? $_REQUEST["start"] : 0;
        $limit = (int) isset($_REQUEST["limit"]) ? $_REQUEST["limit"] : $pageSize;

        $cron = new Cron();
        list ($count, $data) = $cron->getData($filter, $start, $limit);

        $response = [
            "success" => true,
            "resultTotal" => $count,
            "resultRoot" => $data
        ];
        break;
    case "EMPTY":
        $status = 1;
        try {
            $file = PATH_DATA . "log" . PATH_SEP . "cron.log";
            if (file_exists($file)) {
                unlink($file);
            }
            $response["status"] = "OK";
            G::auditLog("ClearCron");
        } catch (Exception $e) {
            $response["message"] = $e->getMessage();
            $status = 0;
        }
        if ($status == 0) {
            $response["status"] = "ERROR";
        }
        break;
}

echo G::json_encode($response);
