<?php

$conf = new Configurations();

$oHeadPublisher = headPublisher::getSingleton();
$oHeadPublisher->addExtJsScript("cases/main", false); //Adding a javascript file .js
$oHeadPublisher->addContent("cases/main"); //Adding a html file  .html.

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

    $confDefaultOption = 'CASES_SEARCH';
    $action = 'search';
    $participation = $case->getStatusInfo($openAppUid, 0, $_SESSION['USER_LOGGED']);
    $arrayDelIndex = [];

    if (!empty($participation)) {
        /** If the user does have participation */
        $arrayDefaultOption = [
            'TO_DO' => ['CASES_INBOX', 'todo'],
            'DRAFT' => ['CASES_DRAFT', 'draft'],
            'CANCELLED' => ['CASES_SENT', 'sent'],
            'COMPLETED' => ['CASES_SENT', 'sent'],
            'PARTICIPATED' => ['CASES_SENT', 'sent'],
            'UNASSIGNED' => ['CASES_SELFSERVICE', 'unassigned'],
            'PAUSED' => ['CASES_PAUSED', 'paused']
        ];

        $confDefaultOption = $arrayDefaultOption[$participation['APP_STATUS']][0];
        $action = $arrayDefaultOption[$participation['APP_STATUS']][1];
        $arrayDelIndex = $participation['DEL_INDEX'];
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

    if (count($arrayDelIndex) === 1) {
        //We will to open the case: one thread
        $openCaseIE = true;
        $defaultOption = '../cases/open?APP_UID=' . $openAppUid . '&DEL_INDEX=' . $arrayDelIndex[0] . '&action=' . $action;
    } else {
        //We will to show the list: more than one thread
        $defaultOption = '../cases/casesListExtJs?action=' . $action . '&openApplicationUid=' . $openAppUid;
    }
} else {
    if (isset($_GET['id'])) {
        $defaultOption = '../cases/open?APP_UID=' . $_GET['id'] . '&DEL_INDEX=' . $_GET['i'];

        if (isset($_GET['a'])) {
            $defaultOption .= '&action=' . $_GET['a'];
        }
    }
}

$oServerConf = ServerConf::getSingleton();
if ($oServerConf->isRtl(SYS_LANG)) {
    $regionTreePanel = 'east';
    $regionDebug = 'west';
} else {
    $regionTreePanel = 'west';
    $regionDebug = 'east';
}

$urlProxy = 'casesMenuLoader?action=getAllCounters&r=';

$oHeadPublisher->assign('regionTreePanel', $regionTreePanel);
$oHeadPublisher->assign('regionDebug', $regionDebug);
$oHeadPublisher->assign('openCaseIE', $openCaseIE);
$oHeadPublisher->assign("defaultOption", $defaultOption); //User menu permissions
$oHeadPublisher->assign('urlProxy', $urlProxy); //sending the urlProxy to make
$oHeadPublisher->assign("_nodeId", isset($confDefaultOption) ? $confDefaultOption : "PM_USERS"); //User menu permissions
$oHeadPublisher->assign("FORMATS", $conf->getFormats());


$_SESSION["current_ux"] = "NORMAL";

G::RenderPage("publish", "extJs");

