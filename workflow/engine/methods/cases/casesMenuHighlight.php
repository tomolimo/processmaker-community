<?php

use Illuminate\Support\Facades\DB;
use ProcessMaker\Model\Delegation;

// Get the self service query for the current user
$query = Delegation::getSelfServiceQuery($_SESSION['USER_LOGGED']);

// Mutate query and execute
if (!is_string($query)) {
    $query->limit(1);
    $items = $query->get();
    $atLeastOne = $items->count() > 0;
} else {
    $query .= " LIMIT 1";
    $items = DB::select($query);
    $atLeastOne = !empty($items);
}

// Initializing the response variable
$response = [];

// The scope for the first version of this feature is only for unassigned list, so, this value is currently fixed
$response[] = ['item' => 'CASES_SELFSERVICE', 'highlight' => $atLeastOne];

// Print the response in JSON format
header('Content-Type: application/json');
echo json_encode($response);
