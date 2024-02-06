<?php
/**
 * cases_ShowOutputDocument.php
 *
 * Download documents related to the output document
 *
 * @link https://wiki.processmaker.com/3.2/Cases/Documents#Downloading_Files
 * @link https://wiki.processmaker.com/3.3/Cases/Information#Generated_Documents
 */

use ProcessMaker\Plugins\PluginRegistry;

if (isset($_REQUEST['actionAjax']) && $_REQUEST['actionAjax'] == "verifySession") {
    if (!isset($_SESSION['USER_LOGGED'])) {
        if ((isset($_POST['request'])) && ($_POST['request'] == true)) {
            $response = new stdclass();
            $response->message = G::LoadTranslation('ID_LOGIN_AGAIN');
            $response->lostSession = true;
            print G::json_encode($response);
            die();
        } else {
            G::SendMessageText(G::LoadTranslation('ID_LOGIN_TO_SEE_OUTPUTDOCS'), "WARNING");
            G::header("location: " . "/");
            die();
        }
    } else {
        $response = new stdclass();
        print G::json_encode($response);
        die();
    }
}

require_once("classes/model/AppDocumentPeer.php");
require_once("classes/model/OutputDocumentPeer.php");

$oAppDocument = new AppDocument();
$oAppDocument->Fields = $oAppDocument->load($_GET['a'], (isset($_GET['v'])) ? $_GET['v'] : null);

$sAppDocUid = $oAppDocument->getAppDocUid();
$sDocUid = $oAppDocument->Fields['DOC_UID'];

$oOutputDocument = new OutputDocument();
$oOutputDocument->Fields = $oOutputDocument->getByUid($sDocUid);
$download = $oOutputDocument->Fields['OUT_DOC_OPEN_TYPE'];

//Check if the user can be download the Output Document
if ($RBAC->userCanAccess('PM_FOLDERS_ALL') != 1 && defined('DISABLE_DOWNLOAD_DOCUMENTS_SESSION_VALIDATION') && DISABLE_DOWNLOAD_DOCUMENTS_SESSION_VALIDATION == 0) {
    if (!$oAppDocument->canDownloadOutput(
        $oAppDocument->Fields['USR_UID'],
        $_SESSION['USER_LOGGED'],
        $oOutputDocument->Fields['PRO_UID'],
        $oAppDocument->Fields['APP_UID'],
        $sAppDocUid
    )
    ) {
        G::header('Location: /errors/error403.php?url=' . urlencode($_SERVER['REQUEST_URI']));
        die();
    }
}

$docFileName = fixContentDispositionFilename($oAppDocument->getAppDocFilename());
$info = pathinfo($docFileName);

if (!isset($_GET['ext'])) {
    $ext = (!empty($info['extension'])) ? $info['extension']: 'pdf';
} else {
    if ($_GET['ext'] != '') {
        $ext = $_GET['ext'];
    } else {
        $ext = (!empty($info['extension'])) ? $info['extension']: 'pdf';
    }
}
$ver = (isset($_GET['v']) && $_GET['v'] != '') ? '_' . $_GET['v'] : '';

if (!$ver) { //This code is in the case the outputdocument won't be versioned
    $ver = '_1';
}

$realPath = PATH_DOCUMENT . G::getPathFromUID($oAppDocument->Fields['APP_UID']) . '/outdocs/' . $sAppDocUid . $ver . '.' . $ext;
$realPath1 = PATH_DOCUMENT . G::getPathFromUID($oAppDocument->Fields['APP_UID']) . '/outdocs/' . $info['basename'] . $ver . '.' . $ext;
$realPath2 = PATH_DOCUMENT . G::getPathFromUID($oAppDocument->Fields['APP_UID']) . '/outdocs/' . $info['basename'] . '.' . $ext;

$sw_file_exists = false;
if (file_exists($realPath)) {
    $sw_file_exists = true;
} elseif (file_exists($realPath1)) {
    $sw_file_exists = true;
    $realPath = $realPath1;
} elseif (file_exists($realPath2)) {
    $sw_file_exists = true;
    $realPath = $realPath2;
}

if (!$sw_file_exists) {
    $oPluginRegistry = PluginRegistry::loadSingleton();
    if ($oPluginRegistry->existsTrigger(PM_UPLOAD_DOCUMENT)) {
        $error_message = G::LoadTranslation('ID_ERROR_FILE_NOT_EXIST', SYS_LANG, array('filename' => $info['basename'] . $ver . '.' . $ext)) . ' ' . G::LoadTranslation('ID_CONTACT_ADMIN');
    } else {
        $error_message = "'" . $info['basename'] . $ver . '.' . $ext . "' " . G::LoadTranslation('ID_ERROR_STREAMING_FILE');
    }

    if ((isset($_POST['request'])) && ($_POST['request'] == true)) {
        $res['success'] = 'failure';
        $res['message'] = $error_message;
        print G::json_encode($res);
    } else {
        G::SendMessageText($error_message, "ERROR");
        $backUrlObj = explode("sys" . config("system.workspace"), $_SERVER['HTTP_REFERER']);
        G::header("location: " . "/sys" . config("system.workspace") . $backUrlObj[1]);
        die();
    }
} else {
    if ((isset($_POST['request'])) && ($_POST['request'] == true)) {
        $res['success'] = 'success';
        $res['message'] = $info['basename'] . $ver . '.' . $ext;
        print G::json_encode($res);
    } else {
        $nameFile = $info['basename'] . $ver . '.' . $ext;
        $licensedFeatures = PMLicensedFeatures::getSingleton();
        $downloadStatus = false;
        if (!$downloadStatus) {
            G::streamFile($realPath, $download, $nameFile); //download
        }
    }
}
