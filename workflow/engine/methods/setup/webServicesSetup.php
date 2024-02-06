<?php

use ProcessMaker\Core\System;

if (($RBAC_Response = $RBAC->userCanAccess( "PM_FACTORY" )) != 1)
    return $RBAC_Response;

$dbc = new DBConnection();
$ses = new DBSession( $dbc );

if (! isset( $_SESSION['END_POINT'] )) {
    $aFields['WS_HOST'] = System::getServerHost();
    $aFields['WS_WORKSPACE'] = config("system.workspace");
} else {
    if (strpos( $_SESSION['END_POINT'], 'https' ) !== false) {
        preg_match( '@^(?:https://)?([^/]+)@i', $_SESSION['END_POINT'], $coincidencias );
    } else {
        preg_match( '@^(?:http://)?([^/]+)@i', $_SESSION['END_POINT'], $coincidencias );
    }
    $aAux = explode( ':', $coincidencias[1] );
    $aFields['WS_HOST'] = $aAux[0];
    $aFields['WS_PORT'] = (isset( $aAux[1] ) ? $aAux[1] : '');
    $aAux = explode( $aAux[0] . (isset( $aAux[1] ) ? ':' . $aAux[1] : ''), $_SESSION['END_POINT'] );
    $aAux = explode( '/', $aAux[1] );
    $aFields['WS_WORKSPACE'] = substr( $aAux[1], 3 );
}

$rows[] = array ('uid' => 'char','name' => 'char','age' => 'integer','balance' => 'float'
);
$rows[] = array ('uid' => 'http','name' => 'http'
);
$rows[] = array ('uid' => 'https','name' => 'https'
);

$_DBArray['protocol'] = $rows;
$_SESSION['_DBArray'] = $_DBArray;

$G_PUBLISH = new Publisher();
$G_PUBLISH->AddContent( 'xmlform', 'xmlform', 'setup/webServicesSetup', '', $aFields, 'webServicesSetupSave' );

G::RenderPage( "publish", "raw" );

