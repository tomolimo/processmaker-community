<?php
/**
 * processes_DeleteCases.php
 *
 * Deleting all Cases of a Process
 *
 * @link https://wiki.processmaker.com/3.2/Processes#Deleting_all_Cases_of_a_Process
 */

global $RBAC;
$RBAC->requirePermissions('PM_DELETE_PROCESS_CASES', 'PM_FACTORY');
$resp = new stdClass();
try {
    $uids = explode(',', $_POST['PRO_UIDS']);
    $process = new Process();
    foreach ($uids as $uid) {
        $process->deleteProcessCases($uid);
    }

    $resp->status = true;
    $resp->msg = G::LoadTranslation('ID_ALL_RECORDS_DELETED_SUCESSFULLY');

    echo G::json_encode($resp);

} catch (Exception $e) {
    $resp->status = false;
    $resp->msg = $e->getMessage();
    $resp->trace = $e->getTraceAsString();
    echo G::json_encode($resp);
}

