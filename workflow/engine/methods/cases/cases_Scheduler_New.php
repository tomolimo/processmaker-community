<?php

try {
    global $RBAC;

    require_once 'classes/model/CaseScheduler.php';
    require_once 'classes/model/Process.php';
    require_once 'classes/model/Task.php';

    $G_MAIN_MENU = 'processmaker';
    $G_SUB_MENU = 'cases';

    $G_ID_MENU_SELECTED = 'CASES';
    $G_ID_SUB_MENU_SELECTED = 'CASES_SCHEDULER';

    $G_PUBLISH = new Publisher();

    $aFields['PHP_START_DATE'] = date( 'Y-m-d' );
    $aFields['PRO_UID'] = isset( $_GET['PRO_UID'] ) ? $_GET['PRO_UID'] : $_SESSION['PROCESS'];
    $aFields['PHP_CURRENT_DATE'] = $aFields['PHP_START_DATE'];
    $aFields['PHP_END_DATE'] = date( 'Y-m-d', mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' ) + 5 ) );

    /* Prepare page before to show */

    /*-- Base
    $aFields = array();
    $oCase = new Cases();
    $_DBArray['NewCase'] = $oCase->getStartCases( $_SESSION['USER_LOGGED'] );
    */

    $oCaseScheduler = new CaseScheduler();
    //$_DBArray['NewProcess'] = $oCaseScheduler->getProcessDescription();
    //$_DBArray['NewTask'] = $oCaseScheduler->getTaskDescription();
    // var_dump($oCaseScheduler->getAllProcess()); die;

    $aFields['UID_SCHEDULER'] = "scheduler";

    $aFields['SCH_LIST'] = '';
    foreach ($_SESSION['_DBArray']['cases_scheduler'] as $key => $item) {
        $aFields['SCH_LIST'] .=  htmlspecialchars($item['SCH_NAME'], ENT_QUOTES) . '^';
    }

    $G_PUBLISH->AddContent("xmlform", "xmlform", "cases" . PATH_SEP . "cases_Scheduler_New.xml", "", $aFields, "CaseSchedulerCreateUpdate");
    G::RenderPage( 'publishBlank', 'blank' );

} catch (Exception $oException) {
    $token = strtotime("now");
    PMException::registerErrorLog($oException, $token);
    G::outRes( G::LoadTranslation("ID_EXCEPTION_LOG_INTERFAZ", array($token)) );
    die;
}

