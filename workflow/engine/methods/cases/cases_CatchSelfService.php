<?php

use ProcessMaker\Util\DateTime;

switch ($RBAC->userCanAccess('PM_CASES')) {
    case -2:
        G::SendTemporalMessage('ID_USER_HAVENT_RIGHTS_SYSTEM', 'error', 'labels');
        G::header('location: ../login/login');
        die();
        break;
    case -1:
        G::SendTemporalMessage('ID_USER_HAVENT_RIGHTS_PAGE', 'error', 'labels');
        G::header('location: ../login/login');
        die();
        break;
}

/** Render page */
require_once 'classes/model/Process.php';
require_once 'classes/model/Task.php';

//Get information about the case
$case = new Cases();
$fieldsCase = $case->loadCase($_SESSION['APPLICATION'], $_SESSION['INDEX']);

//Get the user logged
$userLogged = isset($RBAC->aUserInfo['USER_INFO']['USR_UID']) ? $RBAC->aUserInfo['USER_INFO']['USR_UID'] : '';

//Check the authorization
$objCase = new \ProcessMaker\BusinessModel\Cases();
$userCanAccess = $objCase->userAuthorization(
    $userLogged,
    $fieldsCase['PRO_UID'],
    $fieldsCase['APP_UID'],
    ['PM_ALLCASES'],
    ['SUMMARY_FORM' => 'VIEW']
);

$objProc = new Process();
$fieldsProcess = $objProc->load($fieldsCase['PRO_UID']);
$fieldsCase['PRO_TITLE'] = $fieldsProcess['PRO_TITLE'];

if (
    isset($fieldsProcess['PRO_DYNAFORMS']['PROCESS']) &&
    !empty($fieldsProcess['PRO_DYNAFORMS']['PROCESS']) &&
    $userCanAccess['objectPermissions']['SUMMARY_FORM'] &&
    $objProc->isBpmnProcess($fieldsCase['PRO_UID'])
) {
    /**We will to show the custom summary form only for BPMN process*/
    $_REQUEST['APP_UID'] = $fieldsCase['APP_UID'];
    $_REQUEST['DEL_INDEX'] = $fieldsCase['DEL_INDEX'];
    $_REQUEST['DYN_UID'] = $fieldsProcess['PRO_DYNAFORMS']['PROCESS'];
    require_once(PATH_METHODS . 'cases' . PATH_SEP . 'summary.php');
    exit();
} else {
    /**We will to show the default claim case form*/
    $objTask = new Task();
    $fieldsTask = $objTask->load($fieldsCase['TAS_UID']);
    $fieldsCase['TAS_TITLE'] = $fieldsTask['TAS_TITLE'];
    $fieldsCase['STATUS'] .= ' ( ' . G::LoadTranslation('ID_UNASSIGNED') . ' )';

    //Now getting information about the PREVIOUS task. If is the first task then no previous, use 1
    $appDelegation = new AppDelegation();
    $appDelegation->Load(
        $fieldsCase['APP_UID'],
        ($fieldsCase['DEL_PREVIOUS'] == 0 ? $fieldsCase['DEL_PREVIOUS'] = 1 : $fieldsCase['DEL_PREVIOUS'])
    );
    $fieldsDelegation = $appDelegation->toArray(BasePeer::TYPE_FIELDNAME);

    try {
        $userInfo = new Users();
        $userInfo->load($fieldsDelegation['USR_UID']);
        $fieldsCase['PREVIOUS_USER'] = $userInfo->getUsrFirstname() . ' ' . $userInfo->getUsrLastname();
    } catch (Exception $error) {
        $fieldsCase['PREVIOUS_USER'] = G::LoadTranslation('ID_NO_PREVIOUS_USR_UID');
    }

    //To enable information (dynaforms, steps) before claim a case
    $_SESSION['bNoShowSteps'] = true;
    $G_MAIN_MENU = 'processmaker';
    $G_SUB_MENU = 'caseOptions';
    $G_ID_MENU_SELECTED = 'CASES';
    $G_ID_SUB_MENU_SELECTED = '_';
    $headPublisher = headPublisher::getSingleton();
    $headPublisher->addScriptCode("
        if (typeof parent != 'undefined') {
            if (parent.showCaseNavigatorPanel) {
                parent.showCaseNavigatorPanel('{$fieldsCase['APP_STATUS']}');
            }
        }
    ");
    $headPublisher->addScriptCode('
        var Cse = {};
        Cse.panels = {};
        var leimnud = new maborak();
        leimnud.make();
        leimnud.Package.Load("rpc,drag,drop,panel,app,validator,fx,dom,abbr",{Instance:leimnud,Type:"module"});
        leimnud.Package.Load("cases",{Type:"file",Absolute:true,Path:"/jscore/cases/core/cases.js"});
        leimnud.Package.Load("cases_Step",{Type:"file",Absolute:true,Path:"/jscore/cases/core/cases_Step.js"});
        leimnud.Package.Load("processmap",{Type:"file",Absolute:true,Path:"/jscore/processmap/core/processmap.js"});
        leimnud.exec(leimnud.fix.memoryLeak);
    ');
    $headPublisher = headPublisher::getSingleton();
    $headPublisher->addScriptFile('/jscore/cases/core/cases_Step.js');

    $fieldsCase['isIE'] = Bootstrap::isIE();

    $G_PUBLISH = new Publisher();
    $fieldsCase = DateTime::convertUtcToTimeZone($fieldsCase);
    $G_PUBLISH->AddContent('xmlform', 'xmlform', 'cases/cases_CatchSelfService.xml', '', $fieldsCase, 'cases_CatchExecute');
    G::RenderPage('publish', 'blank');
}
