<?php

use ProcessMaker\BusinessModel\Cases as BusinessModelCases;

if ($RBAC->userCanAccess('PM_SUPERVISOR') != 1) {
    switch ($RBAC->userCanAccess('PM_SUPERVISOR')) {
        case -2:
            G::SendTemporalMessage('ID_USER_HAVENT_RIGHTS_SYSTEM', 'error', 'labels');
            G::header('location: ../login/login');
            die();
            break;
        default:
            G::SendTemporalMessage('ID_USER_HAVENT_RIGHTS_PAGE', 'error', 'labels');
            G::header('location: ../login/login');
            die();
            break;
    }
}

/* GET , POST & $_SESSION Vars */
if (isset($_SESSION['APPLICATION'])) {
    unset($_SESSION['APPLICATION']);
}
if (isset($_SESSION['PROCESS'])) {
    unset($_SESSION['PROCESS']);
}
if (isset($_SESSION['INDEX'])) {
    unset($_SESSION['INDEX']);
}
if (isset($_SESSION['STEP_POSITION'])) {
    unset($_SESSION['STEP_POSITION']);
}
// Get information
$case = new Cases();
$appUid = $_GET['APP_UID'];
$delIndex = $_GET['DEL_INDEX'];
$tasUid = (isset($_GET['TAS_UID'])) ? $_GET['TAS_UID'] : '';
// Get case fields
$fields = $case->loadCase($appUid, $delIndex);
// Set some SESSION values
$_SESSION['APPLICATION'] = $appUid;
$_SESSION['INDEX'] = $delIndex ;
$_SESSION['PROCESS'] = $fields['PRO_UID'];
$_SESSION['TASK'] = $fields['TAS_UID'];
$_SESSION['STEP_POSITION'] = 0;
$_SESSION['CURRENT_TASK'] = $fields['TAS_UID'];
$flag = true;
$cases = new BusinessModelCases();
$urls = $cases->getAllUrlStepsToRevise($appUid, $delIndex);

if (!empty($urls)) {
    $url = $urls[0]['url'];
} else {
    $message = [];
    $message["MESSAGE"] = G::LoadTranslation("ID_NO_ASSOCIATED_INPUT_DOCUMENT_DYN");
    $G_PUBLISH = new Publisher();
    $G_PUBLISH->AddContent("xmlform", "xmlform", "login/showMessage", "", $message);
    G::RenderPage("publishBlank", "blank");
}

$processUser = new ProcessUser();
$userAccess = $processUser->validateUserAccess($_SESSION['PROCESS'], $_SESSION['USER_LOGGED']);
if (!$userAccess) {
    $flag = false;
}

if ($flag) {
    G::header("Location: " . $url);
} else {
    $message = [];
    $message["MESSAGE"] = G::LoadTranslation("ID_SUPERVISOR_DOES_NOT_HAVE_DYNAFORMS");
    $G_PUBLISH = new Publisher();
    $G_PUBLISH->AddContent("xmlform", "xmlform", "login/showMessage", "", $message);
    G::RenderPage("publishBlank", "blank");
}
