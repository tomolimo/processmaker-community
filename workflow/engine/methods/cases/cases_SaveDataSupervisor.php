<?php

use ProcessMaker\Plugins\PluginRegistry;
use ProcessMaker\BusinessModel\Cases as BusinessModelCases;

$dynaForm = DynaformPeer::retrieveByPK($_GET["UID"]);

$flagDynaFormNewVersion = !is_null($dynaForm) && $dynaForm->getDynVersion() == 2;

//If no variables are submitted and the $_POST variable is empty
if (!isset($_POST['form'])) {
    $_POST['form'] = array();
}

if ($flagDynaFormNewVersion) {
    $dataForm = $_POST["form"];
}

$oForm = new Form( $_SESSION['PROCESS'] . '/' . $_GET['UID'], PATH_DYNAFORM );
$oForm->validatePost();

//load the variables
$oCase = new Cases();
$Fields = $oCase->loadCase( $_SESSION['APPLICATION'] );

if ($flagDynaFormNewVersion) {
    $Fields["APP_DATA"] = array_merge($Fields["APP_DATA"], $dataForm);
}

$Fields['APP_DATA'] = array_merge( $Fields['APP_DATA'], $_POST['form'] );

//save data
$aData = array ();
$aData['APP_NUMBER'] = $Fields['APP_NUMBER'];
$aData['APP_PROC_STATUS'] = $Fields['APP_PROC_STATUS'];
$aData['APP_DATA'] = $Fields['APP_DATA'];
$aData['DEL_INDEX'] = $_SESSION['INDEX'];
$aData['TAS_UID'] = $_SESSION['TASK'];
$aData['CURRENT_DYNAFORM'] = $_GET['UID'];
$aData['PRO_UID'] = $Fields['PRO_UID'];
$aData['USER_UID'] = $_SESSION['USER_LOGGED'];
$aData['APP_STATUS'] = $Fields['APP_STATUS'];

//$aData = $oCase->loadCase( $_SESSION['APPLICATION'] );
$oCase->updateCase( $_SESSION['APPLICATION'], $aData );
G::SendTemporalMessage( 'ID_SAVED_SUCCESSFULLY', 'info' );

//Save files
if (isset( $_FILES["form"]["name"] ) && count( $_FILES["form"]["name"] ) > 0) {
	$arrayField = array ();
	$arrayFileName = array ();
	$arrayFileTmpName = array ();
	$arrayFileError = array ();
	$i = 0;

	foreach ($_FILES["form"]["name"] as $fieldIndex => $fieldValue) {
		if (is_array( $fieldValue )) {
			foreach ($fieldValue as $index => $value) {
				if (is_array( $value )) {
					foreach ($value as $grdFieldIndex => $grdFieldValue) {
						$arrayField[$i]["grdName"] = $fieldIndex;
						$arrayField[$i]["grdFieldName"] = $grdFieldIndex;
						$arrayField[$i]["index"] = $index;

						$arrayFileName[$i] = $_FILES["form"]["name"][$fieldIndex][$index][$grdFieldIndex];
						$arrayFileTmpName[$i] = $_FILES["form"]["tmp_name"][$fieldIndex][$index][$grdFieldIndex];
						$arrayFileError[$i] = $_FILES["form"]["error"][$fieldIndex][$index][$grdFieldIndex];
						$i = $i + 1;
					}
				}
			}
		} else {
			$arrayField[$i] = $fieldIndex;

			$arrayFileName[$i] = $_FILES["form"]["name"][$fieldIndex];
			$arrayFileTmpName[$i] = $_FILES["form"]["tmp_name"][$fieldIndex];
			$arrayFileError[$i] = $_FILES["form"]["error"][$fieldIndex];
			$i = $i + 1;
		}
	}
	if (count( $arrayField ) > 0) {
		for ($i = 0; $i <= count( $arrayField ) - 1; $i ++) {
			if ($arrayFileError[$i] == 0) {
				$indocUid = null;
				$fieldName = null;
				$fileSizeByField = 0;

				if (is_array( $arrayField[$i] )) {
					if (isset( $_POST["INPUTS"][$arrayField[$i]["grdName"]][$arrayField[$i]["grdFieldName"]] ) && ! empty( $_POST["INPUTS"][$arrayField[$i]["grdName"]][$arrayField[$i]["grdFieldName"]] )) {
						$indocUid = $_POST["INPUTS"][$arrayField[$i]["grdName"]][$arrayField[$i]["grdFieldName"]];
					}

					$fieldName = $arrayField[$i]["grdName"] . "_" . $arrayField[$i]["index"] . "_" . $arrayField[$i]["grdFieldName"];

					if (isset($_FILES["form"]["size"][$arrayField[$i]["grdName"]][$arrayField[$i]["index"]][$arrayField[$i]["grdFieldName"]])) {
						$fileSizeByField = $_FILES["form"]["size"][$arrayField[$i]["grdName"]][$arrayField[$i]["index"]][$arrayField[$i]["grdFieldName"]];
					}
				} else {
					if (isset( $_POST["INPUTS"][$arrayField[$i]] ) && ! empty( $_POST["INPUTS"][$arrayField[$i]] )) {
						$indocUid = $_POST["INPUTS"][$arrayField[$i]];
					}

					$fieldName = $arrayField[$i];

					if (isset($_FILES["form"]["size"][$fieldName])) {
						$fileSizeByField = $_FILES["form"]["size"][$fieldName];
					}
				}

				if ($indocUid != null) {

					$oInputDocument = new InputDocument();
					$aID = $oInputDocument->load( $indocUid );

					//Get the Custom Folder ID (create if necessary)
					$oFolder = new AppFolder();

					//***Validating the file allowed extensions***
					$res = G::verifyInputDocExtension($aID['INP_DOC_TYPE_FILE'], $arrayFileName[$i], $arrayFileTmpName[$i]);
					if($res->status == 0){
						$message = $res->message;
						G::SendMessageText( $message, "ERROR" );
						$backUrlObj = explode( "sys" . config("system.workspace"), $_SERVER['HTTP_REFERER'] );
						G::header( "location: " . "/sys" . config("system.workspace") . $backUrlObj[1] );
						die();
					}

					//--- Validate Filesize of $_FILE
					$inpDocMaxFilesize = $aID["INP_DOC_MAX_FILESIZE"];
					$inpDocMaxFilesizeUnit = $aID["INP_DOC_MAX_FILESIZE_UNIT"];

					$inpDocMaxFilesize = $inpDocMaxFilesize * (($inpDocMaxFilesizeUnit == "MB")? 1024 *1024 : 1024); //Bytes

					if ($inpDocMaxFilesize > 0 && $fileSizeByField > 0) {
						if ($fileSizeByField > $inpDocMaxFilesize) {
							G::SendMessageText(G::LoadTranslation("ID_SIZE_VERY_LARGE_PERMITTED"), "ERROR");
							$arrayAux1 = explode("sys" . config("system.workspace"), $_SERVER["HTTP_REFERER"]);
							G::header("location: /sys" . config("system.workspace") . $arrayAux1[1]);
							exit(0);
						}
					}

					$aFields = array ("APP_UID" => $_SESSION["APPLICATION"],"DEL_INDEX" => $_SESSION["INDEX"],"USR_UID" => $_SESSION["USER_LOGGED"],"DOC_UID" => $indocUid,"APP_DOC_TYPE" => "INPUT","APP_DOC_CREATE_DATE" => date( "Y-m-d H:i:s" ),"APP_DOC_COMMENT" => "","APP_DOC_TITLE" => "","APP_DOC_FILENAME" => $arrayFileName[$i],"FOLDER_UID" => $oFolder->createFromPath( $aID["INP_DOC_DESTINATION_PATH"] ),"APP_DOC_TAGS" => $oFolder->parseTags( $aID["INP_DOC_TAGS"] ),"APP_DOC_FIELDNAME" => $fieldName);
				} else {
					$aFields = array ("APP_UID" => $_SESSION["APPLICATION"],"DEL_INDEX" => $_SESSION["INDEX"],"USR_UID" => $_SESSION["USER_LOGGED"],"DOC_UID" => - 1,"APP_DOC_TYPE" => "ATTACHED","APP_DOC_CREATE_DATE" => date( "Y-m-d H:i:s" ),"APP_DOC_COMMENT" => "","APP_DOC_TITLE" => "","APP_DOC_FILENAME" => $arrayFileName[$i],"APP_DOC_FIELDNAME" => $fieldName);
				}

				$oAppDocument = new AppDocument();
				$oAppDocument->create( $aFields );

				$iDocVersion = $oAppDocument->getDocVersion();
				$sAppDocUid = $oAppDocument->getAppDocUid();
				$aInfo = pathinfo( $oAppDocument->getAppDocFilename() );
				$sExtension = ((isset( $aInfo["extension"] )) ? $aInfo["extension"] : "");
				$pathUID = G::getPathFromUID($_SESSION["APPLICATION"]);
				$sPathName = PATH_DOCUMENT . $pathUID . PATH_SEP;
				$sFileName = $sAppDocUid . "_" . $iDocVersion . "." . $sExtension;
				G::uploadFile( $arrayFileTmpName[$i], $sPathName, $sFileName );

				//Plugin Hook PM_UPLOAD_DOCUMENT for upload document
				$oPluginRegistry = PluginRegistry::loadSingleton();

				if ($oPluginRegistry->existsTrigger( PM_UPLOAD_DOCUMENT ) && class_exists( "uploadDocumentData" )) {
					$triggerDetail = $oPluginRegistry->getTriggerInfo( PM_UPLOAD_DOCUMENT );
					$documentData = new uploadDocumentData( $_SESSION["APPLICATION"], $_SESSION["USER_LOGGED"], $sPathName . $sFileName, $aFields["APP_DOC_FILENAME"], $sAppDocUid, $iDocVersion );
					$uploadReturn = $oPluginRegistry->executeTriggers( PM_UPLOAD_DOCUMENT, $documentData );

					if ($uploadReturn) {
						$aFields["APP_DOC_PLUGIN"] = $triggerDetail->getNamespace();

						if (! isset( $aFields["APP_DOC_UID"] )) {
							$aFields["APP_DOC_UID"] = $sAppDocUid;
						}

						if (! isset( $aFields["DOC_VERSION"] )) {
							$aFields["DOC_VERSION"] = $iDocVersion;
						}

						$oAppDocument->update( $aFields );

						unlink( $sPathName . $sFileName );
					}
				}
			}
		}
	}
}
//Define the STEP_POSITION
$ex = isset($_GET['ex']) ? $_GET['ex'] : 0;
//go to the next step
$nextSteps = $oCase->getNextSupervisorStep($_SESSION['PROCESS'], $_SESSION['STEP_POSITION']);
$url = '';
$steps = (new BusinessModelCases())->getAllUrlStepsToRevise($_SESSION['APPLICATION'], $_SESSION['INDEX']);
$n = count($steps);
foreach ($steps as $key => $step) {
    if ($step['uid'] === $nextSteps['UID'] && $key + 1 < $n) {
        $nextUrl = $steps[$key + 1]['url'];
        $url = $nextUrl;
        break;
    }
}
if (empty($url)) {
    die('<script type="text/javascript">'
            . 'if(window.parent && window.parent.parent){window.parent.parent.postMessage("redirect=MyCases","*");}'
            . '</script>');
}
G::header('Location:' . $url);
die();