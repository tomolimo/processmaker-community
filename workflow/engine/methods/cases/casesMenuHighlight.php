<?php

use Illuminate\Support\Facades\DB;
use ProcessMaker\BusinessModel\Cases\CasesList;
use ProcessMaker\Model\Delegation;

// Get the user logged
$usrUid = $_SESSION['USER_LOGGED'];
// Instance the class
$casesList = new CasesList();
$response = [];
// Get highlight for all task list
$response = $casesList->atLeastOne($usrUid);

// Print the response in JSON format
header('Content-Type: application/json');
echo json_encode($response);
