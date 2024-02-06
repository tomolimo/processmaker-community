<?php
/**
 * @deprecated This file is not used anymore, it will be removed in future versions of PM
 */

if (! isset( $_REQUEST['action'] )) {
    $res['success'] = 'failure';
    $res['message'] = 'You may request an action';
    print G::json_encode( $res );
    die();
}
if (! function_exists( $_REQUEST['action'] ) || !G::isUserFunction($_REQUEST['action'])) {
    $res['success'] = 'failure';
    $res['message'] = 'The requested action does not exist';
    header( "Content-Type: application/json" );
    print G::json_encode( $res );
    die();
}

$functionName = $_REQUEST['action'];
$functionParams = isset( $_REQUEST['params'] ) ? $_REQUEST['params'] : array ();

$functionName( $functionParams );

/**
 * Get the default menu
*/
function getExtJSParams ()
{
    $validParams = [
        'callback' => '',
        'dir' => 'DESC',
        'sort' => '',
        'start' => 0,
        'limit' => 25,
        'filter' => '',
        'search' => '',
        'action' => '',
        'xaction' => '',
        'data' => '',
        'status' => '',
        'query' => '',
        'fields' => ''
    ];
    $result = [];
    foreach ($validParams as $param => $default) {
        $result[$param] = ($request[$param] ?? isset($request[$param])) ? $request[$param] : $default;
    }
    return $result;
}

function sendJsonResultGeneric ($response, $callback)
{
    header( "Content-Type: application/json" );
    $finalResponse = G::json_encode( $response );
    if ($callback != '') {
        print $callback . "($finalResponse);";
    } else {
        print $finalResponse;
    }
}

function getNotesList ()
{
    extract( getExtJSParams() );
    if ((isset( $_REQUEST['appUid'] )) && (trim( $_REQUEST['appUid'] ) != "")) {
        $appUid = $_REQUEST['appUid'];
    } else {
        $appUid = $_SESSION['APPLICATION'];
    }
    $usrUid = (isset( $_SESSION['USER_LOGGED'] )) ? $_SESSION['USER_LOGGED'] : "";
    $appNotes = new AppNotes();
    $response = $appNotes->getNotesList( $appUid, '', $start, $limit );

    sendJsonResultGeneric( $response['array'], $callback );
}

function postNote ()
{
    extract( getExtJSParams() );
    if ((isset( $_REQUEST['appUid'] )) && (trim( $_REQUEST['appUid'] ) != "")) {
        $appUid = $_REQUEST['appUid'];
    } else {
        $appUid = $_SESSION['APPLICATION'];
    }
    $usrUid = (isset( $_SESSION['USER_LOGGED'] )) ? $_SESSION['USER_LOGGED'] : "";

    $noteContent = addslashes( $_POST['noteText'] );

    $appNotes = new AppNotes();
    $response = $appNotes->postNewNote( $appUid, $usrUid, $noteContent );

    sendJsonResultGeneric( $response, $callback );
}

