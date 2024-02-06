<?php

try {
    global $RBAC;

    if (! class_exists( 'LogCasesSchedulerPeer' )) {
        require_once ('classes/model/LogCasesScheduler.php');
    }

    $G_PUBLISH = new Publisher();
    $oCriteria = new Criteria( 'workflow' );
    //  var_dump(htmlspecialchars($_GET['WS_ROUTE']));
    //  var_dump(htmlentities($_GET['WS_ROUTE']));


    $oCriteria->add( LogCasesSchedulerPeer::LOG_CASE_UID, $_REQUEST['LOG_CASE_UID'] );
    $result = LogCasesSchedulerPeer::doSelectRS( $oCriteria );
    $result->next();
    $row = $result->getRow();
    $aFields['PRO_UID'] = $row[1];
    $aFields['TAS_UID'] = $row[2];
    $aFields['SCH_UID'] = $row[7];
    $aFields['USR_NAME'] = $row[3];
    $aFields['EXEC_DATE'] = $row[4];
    $aFields['EXEC_HOUR'] = $row[5];
    $aFields['RESULT'] = $row[6];
    $aFields['WS_CREATE_CASE_STATUS'] = $row[8];
    $aFields['WS_ROUTE_CASE_STATUS'] = htmlentities( $row[9] );
    //var_dump($aFields);
    //$aFields = $_GET;
    $G_PUBLISH->AddContent( 'xmlform', 'xmlform', 'cases/cases_Scheduler_Log_Detail.xml', '', $aFields, '' );
    G::RenderPage( 'publishBlank', 'blank' );

} catch (Exception $oException) {
    $token = strtotime("now");
    PMException::registerErrorLog($oException, $token);
    G::outRes( G::LoadTranslation("ID_EXCEPTION_LOG_INTERFAZ", array($token)) );
    die;
}

