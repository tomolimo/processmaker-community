<?php
/**
 * Download documents related to the cases notes
 */

use ProcessMaker\BusinessModel\Cases;

if (empty($_SESSION['USER_LOGGED'])) {
    G::SendMessageText(G::LoadTranslation('ID_LOGIN_TO_SEE_OUTPUTDOCS'), "WARNING");
    G::header('Location: /errors/error403.php?url=' . urlencode($_SERVER['REQUEST_URI']));
    die();
}

$appDocument = new AppDocument();

if (empty($_GET['a'])) {
    G::header('Location: /errors/error403.php');
    die();
}

if (empty($_GET['v'])) {
    //Load last version of the document
    $docVersion = $appDocument->getLastAppDocVersion($_GET['a']);
} else {
    $docVersion = $_GET['v'];
}

$appDocument->fields = $appDocument->load($_GET['a'], $docVersion);

//Check if the document is a case note document
if ($appDocument->fields['APP_DOC_TYPE'] != 'CASE_NOTE') {
    G::header('Location: /errors/error403.php');
    die();
}

//Check if the user can be download the input Document
//Send the parameter v = Version
//Send the parameter a = Case UID
if ($RBAC->userCanAccess('PM_FOLDERS_ALL') != 1 && defined('DISABLE_DOWNLOAD_DOCUMENTS_SESSION_VALIDATION') && DISABLE_DOWNLOAD_DOCUMENTS_SESSION_VALIDATION == 0) {
    if (!$appDocument->canDownloadInput($_SESSION['USER_LOGGED'], $_GET['a'], $docVersion)) {
        G::header('Location: /errors/error403.php');
        die();
    }
}

//Create the Cases object
$case = new Cases();
//Get the case information to get the processUid
$processUid = $case->getCaseInfo($appDocument->fields['APP_UID'], $_SESSION['USER_LOGGED'])->pro_uid;
//Get the user authorization
$userCanAccess = $case->userAuthorization(
    $_SESSION['USER_LOGGED'],
    $processUid,
    $appDocument->fields['APP_UID'],
    ['PM_ALLCASES'],
    ['CASES_NOTES' => 'VIEW']
);

//Check if the user has the Case Notes permissions
if ($userCanAccess['objectPermissions']['CASES_NOTES'] != 1) {
    G::header('Location: /errors/error403.php');
    die();
}

$appDocUid = $appDocument->getAppDocUid();
$docVersionInformation = $appDocument->getDocVersion();
$info = pathinfo($appDocument->getAppDocFilename());
$ext = (isset($info['extension']) ? $info['extension'] : '');

$download = true;

//Get the document path
$appUid = G::getPathFromUID($appDocument->fields['APP_UID']);
$file = G::getPathFromFileUID($appDocument->fields['APP_UID'], $appDocUid);

$realPath = PATH_DOCUMENT . $appUid . '/' . $file[0] . $file[1] . '_' . $docVersionInformation . '.' . $ext;
$realPath1 = PATH_DOCUMENT . $appUid . '/' . $file[0] . $file[1] . '.' . $ext;
$sw_file_exists = false;
if (file_exists($realPath)) {
    $sw_file_exists = true;
} elseif (file_exists($realPath1)) {
    $sw_file_exists = true;
    $realPath = $realPath1;
}

if (!$sw_file_exists) {
    $error_message = G::LoadTranslation('ID_ERROR_STREAMING_FILE');
    G::SendMessageText($error_message, "ERROR");
    G::header('Location: ' . $_SERVER['HTTP_REFERER']);
    die();
} else {
    $nameFile = $appDocument->fields['APP_DOC_FILENAME'];
    G::streamFile($realPath, $download, $nameFile); //download
}
