<?php

use ProcessMaker\Core\System;

$sPRO_UID = $oData->PRO_UID;
$sTASKS = $oData->TASKS;
$sDYNAFORM = $oData->DYNAFORM;

$endpoint = System::getServerMainPath() . '/services/wsdl2';
@$client = new SoapClient($endpoint);

$oTask = new Task();
$TaskFields = $oTask->kgetassigType($sPRO_UID, $sTASKS);

if ($TaskFields['TAS_ASSIGN_TYPE'] == 'BALANCED') {
    echo 1;
} else {
    echo 0;
}
