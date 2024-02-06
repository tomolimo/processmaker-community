<?php

use ProcessMaker\Core\System;

$sWS_USER = trim( $_REQUEST['USERNAME'] );
$sWS_PASS = trim( $_REQUEST['PASSWORD'] );

$streamContext = [];
if (G::is_https()) {
    $streamContext = ['stream_context' => stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]])];
}

$endpoint = System::getServerMainPath() . '/services/wsdl2';
$client = new SoapClient($endpoint, $streamContext);

$user = $sWS_USER;
$pass = $sWS_PASS;

$params = array ('userid' => $user,'password' => $pass);
$result = $client->__SoapCall( 'login', array ($params) );

if ($result->status_code == 0) {
    $oCriteria = new Criteria( 'workflow' );
    $oCriteria->addSelectColumn( 'USR_UID' );
    $oCriteria->add( UsersPeer::USR_USERNAME, $sWS_USER );
    $resultSet = UsersPeer::doSelectRS( $oCriteria );
    $resultSet->next();
    $user_id = $resultSet->getRow();
    $result->message = $user_id[0];

    $caseInstance = new Cases();
    if (! $caseInstance->canStartCase( $result->message, $_REQUEST['PRO_UID'] )) {
        $result->status_code = - 1000;
        $result->message = G::LoadTranslation( 'ID_USER_CASES_NOT_START' );
    }
}

die( G::json_encode( $result ) );

