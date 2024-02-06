<?php
/**
 * triggers_Save.php
 *
 * If the feature is enable and the code_scanner_scope was enable with the argument trigger, will check the code
 * Review when a trigger is save
 */

if (($RBAC_Response = $RBAC->userCanAccess( "PM_FACTORY" )) != 1) {
    return $RBAC_Response;
}
require_once ('classes/model/Triggers.php');
require_once ('classes/model/Content.php');

if (isset( $_POST['function'] )) {
    $sfunction = $_POST['function']; //for old processmap
} elseif (isset( $_POST['functions'] )) {
    $sfunction = $_POST['functions']; //for extjs
}

if (isset( $sfunction ) && $sfunction == 'lookforNameTrigger') {
    $snameTrigger = urldecode( $_POST['NAMETRIGGER'] );
    $sPRO_UID = urldecode( $_POST['proUid'] );

    $oTrigger = new \ProcessMaker\BusinessModel\Trigger();
    echo $oTrigger->verifyNameTrigger($sPRO_UID, $snameTrigger);

} else {

    $response = array();

    try {
        $oTrigger = new Triggers();

        $oProcessMap = new ProcessMap( new DBConnection() );
        if (isset( $_POST['form'] )) {
            $value = $_POST['form'];
        } else {
            $value = $_POST;
        }


        $swCreate = true;
        if ($value['TRI_UID'] != '') {
            $oTrigger->load( $value['TRI_UID'] );
        } else {
            $oTrigger->create( $value );
            $value['TRI_UID'] = $oTrigger->getTriUid();
            $swCreate = false;
        }
        $oTrigger->update( $value );
        if($swCreate){
            //Add Audit Log
            $fields = $oTrigger->load( $value['TRI_UID'] );
            $description = "Trigger Name: ".$fields['TRI_TITLE'].", Trigger Uid: ".$value['TRI_UID'];
            if (isset ( $fields['TRI_DESCRIPTION'] )) {
               $description .= ", Description: ".$fields['TRI_DESCRIPTION'];
            }
            if (isset($value["TRI_WEBBOT"])) {
              $description .= ", [EDIT CODE]";
            }
            G::auditLog("UpdateTrigger", $description);
        }

        //if (! isset( $_POST['mode'] )) {
        //    $oProcessMap->triggersList( $value['PRO_UID'] );
        //}

        $response["success"] = true;
        $response["msg"] = G::LoadTranslation("ID_TRIGGERS_SAVED");
    } catch (Exception $e) {
        $response["success"] = false;
        $response["msg"] = $e->getMessage();
    }

    echo G::json_encode($response);
}

