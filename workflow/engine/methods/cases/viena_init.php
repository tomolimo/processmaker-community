<?php

use ProcessMaker\Core\System;
use ProcessMaker\Model\Application;
use ProcessMaker\Model\Delegation;
use ProcessMaker\Model\User;

$conf = new Configurations();

$keyMem = "USER_PREFERENCES" . $_SESSION["USER_LOGGED"];
$memcache = PMmemcached::getSingleton(config("system.workspace"));

$openCaseIE = false;

if (($arrayConfig = $memcache->get($keyMem)) === false) {
    $conf->loadConfig($x, "USER_PREFERENCES", "", "", $_SESSION["USER_LOGGED"], "");
    $arrayConfig = $conf->aConfig;
    $memcache->set($keyMem, $arrayConfig, PMmemcached::ONE_HOUR);
}

$confDefaultOption = "";
if (isset($arrayConfig["DEFAULT_CASES_MENU"])) {
    //this user has a configuration record
    $confDefaultOption = $arrayConfig["DEFAULT_CASES_MENU"];

    global $G_TMP_MENU;

    $oMenu = new Menu();
    $oMenu->load("cases");
    $defaultOption = "";

    foreach ($oMenu->Id as $i => $id) {
        if ($id == $confDefaultOption) {
            $defaultOption = $oMenu->Options[$i];
            break;
        }
    }

    $defaultOption = ($defaultOption != "") ? $defaultOption : "casesListExtJs";
} else {
    $defaultOption = "casesListExtJs";
    $confDefaultOption = "CASES_INBOX";
}

if (isset($_SESSION['__OPEN_APPLICATION_UID__'])) {
    $openAppUid = $_SESSION['__OPEN_APPLICATION_UID__'];
    unset($_SESSION['__OPEN_APPLICATION_UID__']);
    $case = new \ProcessMaker\BusinessModel\Cases();

    $userLogged = $_SESSION['USER_LOGGED'];
    $confDefaultOption = 'CASES_SEARCH';
    $action = 'search';
    $participation = $case->getStatusInfo($openAppUid, 0, $userLogged);
    $arrayDelIndex = [];
    $filter = '';

    if (!empty($participation)) {
        /** If the user does have participation */
        $arrayDefaultOption = [
            'TO_DO' => ['CASES_INBOX', 'todo'],
            'DRAFT' => ['CASES_DRAFT', 'draft'],
            'CANCELLED' => ['CASES_SENT', 'sent'],
            'COMPLETED' => ['CASES_SENT', 'sent'],
            'PARTICIPATED' => ['CASES_SENT', 'mycases'],
            'UNASSIGNED' => ['CASES_SELFSERVICE', 'unassigned'],
            'PAUSED' => ['CASES_PAUSED', 'paused']
        ];

        $confDefaultOption = $arrayDefaultOption[$participation['APP_STATUS']][0];
        $action = $arrayDefaultOption[$participation['APP_STATUS']][1];
        $arrayDelIndex = $participation['DEL_INDEX'];
        $hasParticipation = Delegation::participation($openAppUid, $userLogged);
        // The Participated status needs to define the filter: InProgress or Completed
        if ($participation['APP_STATUS'] === 'PARTICIPATED') {
            // If the user has some participation in the case is important define the current status of the case
            if ($hasParticipation) {
                $caseInfo = Application::getCase($openAppUid);
                if ($caseInfo['APP_STATUS'] === Application::STATUS_DRAFT_NAME || $caseInfo['APP_STATUS'] === Application::STATUS_TODO_NAME) {
                    $filter = 'inProgress';
                }
                if ($caseInfo['APP_STATUS'] === Application::STATUS_COMPLETED_NAME || $caseInfo['APP_STATUS'] === Application::STATUS_CANCELED_NAME) {
                    $filter = 'completed';
                }
            }
        }
    } else {
        /** If the user does not have participation */
        $action = 'jump';
        $caseInformation = $case->getStatusInfo($openAppUid);
        //We will check if is supervisor
        $supervisor = new \ProcessMaker\BusinessModel\ProcessSupervisor();
        $isSupervisor = $supervisor->isUserProcessSupervisor($caseInformation['PRO_UID'], $_SESSION['USER_LOGGED']);
        if ($isSupervisor) {
            $arrayDelIndex = $caseInformation['DEL_INDEX'];
        } else {
            $_SESSION['PROCESS'] = $caseInformation['PRO_UID'];
            $_GET['APP_UID'] = $openAppUid;
            $_SESSION['ACTION'] = $action;
            $_SESSION['APPLICATION'] = $openAppUid;
            $_SESSION['INDEX'] = $caseInformation['DEL_INDEX'][0];
            require_once(PATH_METHODS . 'cases' . PATH_SEP . 'cases_Resume.php');
            exit();
        }
    }
    $appNumber = Application::getCaseNumber($openAppUid);
    if (count($arrayDelIndex) === 1) {
        //We will to open the case: one thread
        $openCaseIE = true;
        $defaultOption = '../cases/open?APP_UID=' . $openAppUid . '&DEL_INDEX=' . $arrayDelIndex[0] . '&action=' . $action . '&openApplicationUid=' . $appNumber . '&filter='. $filter;
    } else {
        //We will to show the list: more than one thread
        $defaultOption = '../cases/casesListExtJs?action=' . $action . '&openApplicationUid=' . $appNumber . '&filter='. $filter;
    }
} else {
    if (isset($_GET['id'])) {
        $defaultOption = '../cases/open?APP_UID=' . $_GET['id'] . '&DEL_INDEX=' . $_GET['i'];

        if (isset($_GET['a'])) {
            $defaultOption .= '&action=' . $_GET['a'];
        }
    }
}

global $translation;

$pmDynaform = new PmDynaform();

$oHeadPublisher = headPublisher::getSingleton();
$oHeadPublisher->assign('window.config', []);
$oHeadPublisher->assign('window.config.defaultOption', $defaultOption);
$oHeadPublisher->assign('window.config._nodeId', isset($confDefaultOption) ? $confDefaultOption : 'PM_USERS');
$oHeadPublisher->assign('window.config.SYS_CREDENTIALS', base64_encode(G::json_encode($pmDynaform->getCredentials())));
$oHeadPublisher->assign('window.config.SYS_SERVER_API', System::getHttpServerHostnameRequestsFrontEnd());
$oHeadPublisher->assign('window.config.SYS_SERVER_AJAX', System::getServerProtocolHost());
$oHeadPublisher->assign('window.config.SYS_WORKSPACE', config('system.workspace'));
$oHeadPublisher->assign('window.config.SYS_URI', SYS_URI);
$oHeadPublisher->assign('window.config.SYS_LANG', SYS_LANG);
$oHeadPublisher->assign('window.config.TRANSLATIONS', $translation);
$oHeadPublisher->assign('window.config.FORMATS', $conf->getFormats());
$oHeadPublisher->assign('window.config.userId', User::getId($_SESSION['USER_LOGGED']));
$oHeadPublisher->assign('window.config.userConfig', [
    'usr_uid' => $_SESSION['USER_LOGGED']
]);

G::RenderPage('publish', 'viena');