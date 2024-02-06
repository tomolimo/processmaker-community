<?php

use Illuminate\Support\Facades\Cache;
use ProcessMaker\Core\System;
use ProcessMaker\Plugins\PluginRegistry;

$aFields = array();

//Validated redirect url
$aFields['URL'] = '';
if (!empty($_GET['u']) || !empty($_GET['url'])) {
    //clean url with protocols
    $flagUrl = true;
    //Most used protocols
    $protocols = ['https://', 'http://', 'ftp://', 'sftp://','smb://', 'file:', 'mailto:'];
    foreach ($protocols as $protocol) {
        if (!empty($_GET['u'])) {
            if (strpos($_GET['u'], $protocol) !== false) {
                $_GET['u'] = '';
                $flagUrl = false;
                break;
            }
        }
        if (!empty($_GET['url'])) {
            if (strpos($_GET['url'], $protocol) !== false) {
                $_GET['url'] = '';
                $flagUrl = false;
                break;
            }
        }
    }
    if ($flagUrl) {
        if (!empty($_GET['u'])) {
            $aFields['URL'] = htmlspecialchars(addslashes(stripslashes(strip_tags(trim(urldecode($_GET['u']))))));
        } elseif (!empty($_GET['url'])) {
            $aFields['URL'] = htmlspecialchars(addslashes(stripslashes(strip_tags(trim(urldecode($_GET['url']))))));
        }
        //The following validations are only for the links to an output document
        if(!empty($_GET['v']) && (strpos($aFields['URL'], '/cases/cases_ShowOutputDocument') != false)) {
            $aFields['URL'] .= "&v=" . $_GET['v'];
        }
        if(!empty($_GET['ext']) && (strpos($aFields['URL'], '/cases/cases_ShowOutputDocument') != false)) {
            $aFields['URL'] .= "&ext=" . $_GET['ext'];
        }
    }
}

if (!isset($_SESSION['G_MESSAGE'])) {
    $_SESSION['G_MESSAGE'] = '';
}

if (!isset($_SESSION['G_MESSAGE_TYPE'])) {
    $_SESSION['G_MESSAGE_TYPE'] = '';
}

$msg = $_SESSION['G_MESSAGE'];
$msgType = $_SESSION['G_MESSAGE_TYPE'];

if (!isset($_SESSION['FAILED_LOGINS'])) {
    $_SESSION['FAILED_LOGINS'] = 0;
    $_SESSION["USERNAME_PREVIOUS1"] = "";
    $_SESSION["USERNAME_PREVIOUS2"] = "";
}

$sFailedLogins = $_SESSION['FAILED_LOGINS'];
$usernamePrevious1 = $_SESSION["USERNAME_PREVIOUS1"];
$usernamePrevious2 = $_SESSION["USERNAME_PREVIOUS2"];

$pass = (isset($_SESSION['NW_PASSWORD'])) ? $_SESSION['NW_PASSWORD'] : '';
$pass1 = (isset($_SESSION['NW_PASSWORD2'])) ? $_SESSION['NW_PASSWORD2'] : '';

$aFields['LOGIN_VERIFY_MSG'] = G::loadTranslation('LOGIN_VERIFY_MSG');
//$aFields['LOGIN_VERIFY_MSG'] = Bootstrap::loadTranslation('LOGIN_VERIFY_MSG');

if (isset($_SESSION['USER_LOGGED'])) {
    require_once 'classes/model/LoginLog.php';
    //close the session, if the current session_id was used in PM.
    $oCriteria = new Criteria('workflow');

    $oCriteria->add(LoginLogPeer::LOG_SID, session_id());
    $oCriteria->add(LoginLogPeer::USR_UID, isset($_SESSION['USER_LOGGED']) ? $_SESSION['USER_LOGGED'] : '-');
    $oCriteria->add(LoginLogPeer::LOG_STATUS, 'ACTIVE');
    $oCriteria->add(LoginLogPeer::LOG_END_DATE, null, Criteria::ISNULL);

    $oDataset = LoginLogPeer::doSelectRS($oCriteria);

    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();

    $aRow = $oDataset->getRow();

    if ($aRow) {
        if ($aRow['LOG_STATUS'] != 'CLOSED' && $aRow['LOG_END_DATE'] == null) {
            $weblog = new LoginLog();
            $endDate = date('Y-m-d H:i:s');
            $aLog = array();
            $aLog['LOG_ID'] = $aRow['LOG_ID'];
            $aLog['LOG_UID'] = $aRow['LOG_UID'];
            $aLog['LOG_STATUS'] = 'CLOSED';
            $aLog['LOG_IP'] = $aRow['LOG_IP'];
            $aLog['LOG_SID'] = session_id();
            $aLog['LOG_INIT_DATE'] = $aRow['LOG_INIT_DATE'];
            $aLog['LOG_END_DATE'] = $endDate;
            $aLog['LOG_CLIENT_HOSTNAME'] = $aRow['LOG_CLIENT_HOSTNAME'];
            $aLog['USR_UID'] = $aRow['USR_UID'];

            $weblog->update($aLog);
        }
    }
} else {
    // Execute SSO trigger
    $pluginRegistry = PluginRegistry::loadSingleton();
    if (defined('PM_SINGLE_SIGN_ON')) {
        if ($pluginRegistry->existsTrigger(PM_SINGLE_SIGN_ON)) {
            if ($pluginRegistry->executeTriggers(PM_SINGLE_SIGN_ON, null)) {
                // Start new session
                @session_destroy();
                session_start();
                session_regenerate_id();

                // Authenticate
                require_once 'authentication.php';

                die();
            }
        }
    }
}
//end log

//start new session
@session_destroy();
session_start();
session_regenerate_id();

$cookieOptions = Bootstrap::buildCookieOptions(['expires' => time() + (24 * 60 * 60), 'httponly' => true]);
setcookie(session_name(), session_id(), $cookieOptions);

$cookieOptions = Bootstrap::buildCookieOptions(['expires' => time() + (24 * 60 * 60), 'path' => '/sys' . config('system.workspace'), 'httponly' => true]);
setcookie('workspaceSkin', SYS_SKIN, $cookieOptions);

if (strlen($msg) > 0) {
    $_SESSION['G_MESSAGE'] = $msg;
}
if (strlen($msgType) > 0) {
    $_SESSION['G_MESSAGE_TYPE'] = $msgType;
}

$_SESSION['FAILED_LOGINS'] = $sFailedLogins;
$_SESSION["USERNAME_PREVIOUS1"] = $usernamePrevious1;
$_SESSION["USERNAME_PREVIOUS2"] = $usernamePrevious2;

$_SESSION['NW_PASSWORD'] = $pass;
$_SESSION['NW_PASSWORD2'] = $pass1;


//translation
//$Translations = G::getModel("Translation");
//require_once "classes/model/Translation.php";
$Translations = new Translation();
$translationsTable = $Translations->getTranslationEnvironments();

$availableLangArray = array();
$availableLangArray [] = array('LANG_ID' => 'char', 'LANG_NAME' => 'char' );
foreach ($translationsTable as $locale) {
    $row['LANG_ID'] = $locale['LOCALE'];

    if ($locale['COUNTRY'] != '.') {
        $row['LANG_NAME'] = $locale['LANGUAGE'] . ' (' . (ucwords(strtolower($locale['COUNTRY']))) . ')';
    } else {
        $row['LANG_NAME'] = $locale['LANGUAGE'];
    }

    $availableLangArray [] = $row;
}

global $_DBArray;
$_DBArray ['langOptions'] = $availableLangArray;

$oConf = new Configurations();
$oConf->loadConfig($obj, 'ENVIRONMENT_SETTINGS', '');

if (isset($oConf->aConfig["login_defaultLanguage"]) && $oConf->aConfig["login_defaultLanguage"] != "") {
    $aFields["USER_LANG"] = $oConf->aConfig["login_defaultLanguage"];
} else {
    $myUrl = explode("/", $_SERVER["REQUEST_URI"]);

    $aFields["USER_LANG"] = (isset($myUrl[2]) && trim($myUrl[2]) != "")? trim($myUrl[2]) : SYS_LANG;
}

$G_PUBLISH = new Publisher();
$version = explode('.', trim(file_get_contents(PATH_GULLIVER . 'VERSION')));
$version = isset($version[0]) ? intval($version[0]) : 0;
Cache::put("FAILED_LOGINS{$usernamePrevious2}", $sFailedLogins, 1800); //this value will be lost after 30 minutes
if ($version >= 3) {
    $G_PUBLISH->AddContent('xmlform', 'xmlform', 'login/loginpm3', '', $aFields, SYS_URI . 'login/authentication.php');
} else {
    $G_PUBLISH->AddContent('xmlform', 'xmlform', 'login/login', '', $aFields, SYS_URI . 'login/authentication.php');
}

//get the serverconf singleton, and check if we can send the heartbeat
$oServerConf = ServerConf::getSingleton();
$partnerFlag = (defined('PARTNER_FLAG')) ? PARTNER_FLAG : false;
if (!$partnerFlag) {
    $sflag = $oServerConf->getHeartbeatProperty('HB_OPTION', 'HEART_BEAT_CONF');
    $sflag = (trim($sflag) != '') ? $sflag : '1';

    //get date of next beat
    $nextBeatDate = $oServerConf->getHeartbeatProperty('HB_NEXT_BEAT_DATE', 'HEART_BEAT_CONF');

    //if flag to send heartbeat is enabled, and it is time to send heartbeat, sent it using asynchronous beat.
    if (($sflag == "1") && ((strtotime("now") > $nextBeatDate) || is_null($nextBeatDate))) {
        $oHeadPublisher = headPublisher::getSingleton();
        //To do: we need to change to ExtJs
        $oHeadPublisher->addScriptCode('var flagHeartBeat = 1;');
    } else {
        $oHeadPublisher->addScriptCode('var flagHeartBeat = 0;');
    }
} else {
    $oHeadPublisher->addScriptCode('var flagHeartBeat = 0;');
}

//check if we show the panel with the getting started info

require_once 'classes/model/Configuration.php';
$oConfiguration = new Configuration();
$oCriteria = new Criteria('workflow');
$oCriteria->add(ConfigurationPeer::CFG_UID, 'getStarted');
$oCriteria->add(ConfigurationPeer::OBJ_UID, '');
$oCriteria->add(ConfigurationPeer::CFG_VALUE, '1');
$oCriteria->add(ConfigurationPeer::PRO_UID, '');
$oCriteria->add(ConfigurationPeer::USR_UID, '');
$oCriteria->add(ConfigurationPeer::APP_UID, '');
$flagGettingStarted =  ConfigurationPeer::doCount($oCriteria);
if ($flagGettingStarted == 0) {
    $oHeadPublisher->addScriptCode('var flagGettingStarted = 1;');
} else {
    $oHeadPublisher->addScriptCode('var flagGettingStarted = 0;');
}

$dummy = '';

$oConf->loadConfig($dummy, 'ENVIRONMENT_SETTINGS', '');
$flagForgotPassword = isset($oConf->aConfig['login_enableForgotPassword'])
                      ? $oConf->aConfig['login_enableForgotPassword']
                      : 'off';

$cookieOptions = Bootstrap::buildCookieOptions(['expires' => time() + (24 * 60 * 60), 'path' => SYS_URI]);
setcookie('PM-Warning', trim(G::LoadTranslation('ID_BLOCKER_MSG'), '*'), $cookieOptions);

$configS = System::getSystemConfiguration('', '', config("system.workspace"));
$activeSession = isset($configS['session_block']) ? !(int)$configS['session_block'] : true;
$cookieOptions = Bootstrap::buildCookieOptions(['expires' => time() + (24 * 60 * 60)]);
if ($activeSession) {
    setcookie('PM-TabPrimary', 101010010, $cookieOptions);
} else {
    setcookie('PM-TabPrimary', uniqid(), $cookieOptions);
}

$oHeadPublisher->addScriptCode("var flagForgotPassword = '$flagForgotPassword';");
$oHeadPublisher->addScriptFile('/jscore/src/PM.js');
$oHeadPublisher->addScriptFile('/jscore/src/Sessions.js');
$oHeadPublisher->addScriptFile('/jscore/src/Register.js');

G::RenderPage('publish');
