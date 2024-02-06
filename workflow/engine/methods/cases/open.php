<?php
/**
 * open.php
 *
 * @see cases/casesStartPage_Ajax.php
 * @see cases/cases_CatchExecute.php
 * @see cases/main_init.php
 *
 * @see dataReportingTools/public_html/js/reportViewer.js
 * @see EnterpriseSearch/dynaform_view.js
 *
 * @link https://wiki.processmaker.com/3.2/Cases/Cases#Search_Criteria
 * @link https://wiki.processmaker.com/3.2/Cases/Cases#Inbox
 * @link https://wiki.processmaker.com/3.2/Cases/Cases#New_Case
 */

use ProcessMaker\BusinessModel\Cases as BmCases;
use ProcessMaker\BusinessModel\ProcessSupervisor;

$tBarGmail = false;
if (isset($_GET['gmail']) && $_GET['gmail'] == 1) {
    $_SESSION['gmail'] = 1;
    $tBarGmail = true;
}

//Check if we have the information for open the case
if (!isset($_GET['APP_UID']) && !isset($_GET['APP_NUMBER']) && !isset($_GET['DEL_INDEX'])) {
    throw new Exception(G::LoadTranslation('ID_APPLICATION_OR_INDEX_MISSING'));
}
//Get the APP_UID related to APP_NUMBER
if (!isset($_GET['APP_UID']) && isset($_GET['APP_NUMBER'])) {
    $caseInstance = new Cases();
    $appUid = $caseInstance->getApplicationUIDByNumber(htmlspecialchars($_GET['APP_NUMBER']));
    if (is_null($appUid)) {
        throw new Exception(G::LoadTranslation('ID_CASE_DOES_NOT_EXISTS'));
    }
} else {
    $appUid = htmlspecialchars($_GET['APP_UID']);
}
//If we don't have the DEL_INDEX we get the current delIndex for example data reporting tool and jump to
if (!isset($_GET['DEL_INDEX'])) {
    $caseInstance = new Cases();
    $delIndex = $caseInstance->getCurrentDelegation($appUid, $_SESSION['USER_LOGGED']);
    if (is_null($delIndex)) {
        throw new Exception(G::LoadTranslation('ID_CASE_IS_CURRENTLY_WITH_ANOTHER_USER'));
    }
    $_GET['DEL_INDEX'] = $delIndex;
} else {
    $delIndex = htmlspecialchars($_GET['DEL_INDEX']);
}

$tasUid = (isset($_GET['TAS_UID'])) ? $tasUid = htmlspecialchars($_GET['TAS_UID']) : '';

$caseInstance = new Cases();
$conf = new Configurations();
$headPublisher = headPublisher::getSingleton();

$urlToRedirectAfterPause = 'casesListExtJs';

$headPublisher->assign('urlToRedirectAfterPause', $urlToRedirectAfterPause);
$headPublisher->addExtJsScript('app/main', true);
$headPublisher->addExtJsScript('cases/open', true);
$headPublisher->assign('FORMATS', $conf->getFormats());
$uri = '';
foreach ($_GET as $k => $v) {
    $uri .= ($uri == '') ? "$k=$v" : "&$k=$v";
}

/**
 * @todo, the action over the case from Open Case, Case Link and jump to needs to work similar, we need to have a PRD
 */

$case = $caseInstance->loadCase($appUid, $delIndex);
$canClaimCase = false;
$caseCanBeReview = false;
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'todo': //Inbox
        case 'draft': //Draft
        case 'sent': //Participated
        case 'unassigned': //Unassigned
        case 'paused': //Paused
        case 'search': //Advanced search
            //For add the validation in the others list we need to a have a PRD, because is change of the functionality
            break;
        case 'to_reassign': //Reassign
            //From reassign: Review if the user can be claim the case
            if ($caseInstance->isSelfService($_SESSION['USER_LOGGED'], $case['TAS_UID'], $appUid)) {
                $canClaimCase = true;
            }
            break;
        case 'to_revise': //Review
            $proSupervisor = new ProcessSupervisor();
            $caseCanBeReview = $proSupervisor->reviewCaseStatusForSupervisor($appUid, $delIndex);
            break;
        case 'jump': //Jump To action
            //From Review: Review if the user is supervisor
            if (isset($_GET['actionFromList']) && ($_GET['actionFromList'] === 'to_revise')) {
                $proSupervisor = new ProcessSupervisor();
                $caseCanBeReview = $proSupervisor->reviewCaseStatusForSupervisor($appUid, $delIndex);
            }
            //From Unassigned: Review if the user can be claim the case
            if ($caseInstance->isSelfService($_SESSION['USER_LOGGED'], $case['TAS_UID'], $appUid)) {
                $canClaimCase = true;
            }
            //From Paused: Get the last index OPEN or CLOSED (by Paused cases)
            $bmCases = new BmCases();
            $delIndex = $bmCases->getOneLastThread($appUid, true);
            $case = $caseInstance->loadCase($appUid, $delIndex, $_GET['action']);
            break;
    }
}

/**
 * Review if the user can be open the case from Review list
 * @link https://wiki.processmaker.com/3.2/Cases/Process_Supervisor#Review
 */
if (!$caseCanBeReview) {
    //The supervisor can not edit the information
    $script = 'cases_Open?';
} else {
    //The supervisor can edit the information, the case are in TO_DO
    $script = 'cases_OpenToRevise?APP_UID=' . $appUid . '&DEL_INDEX=' . $delIndex . '&TAS_UID=' . $tasUid;
    $headPublisher->assign('treeToReviseTitle', G::loadtranslation('ID_STEP_LIST'));
    $casesPanelUrl = 'casesToReviseTreeContent?APP_UID=' . $appUid . '&DEL_INDEX=' . $delIndex;
    $headPublisher->assign('casesPanelUrl', $casesPanelUrl); //translations
    echo "<div id='toReviseTree'></div>";
}

$process = new Process();
$fields = $process->load($case['PRO_UID']);
$isBpmn = $fields['PRO_BPMN'] === 1 ? true : false;

/**
 * Review if the user can be open summary form
 * @link https://wiki.processmaker.com/3.2/Case_Summary#Viewing_the_Custom_Dynaform_when_Opening_a_Case
 */
$showCustomForm = false;


$step = new Step();
$step = $step->loadByProcessTaskPosition($case['PRO_UID'], $case['TAS_UID'], 1);
$headPublisher->assign('uri', $script . $uri);
$headPublisher->assign('_APP_NUM', '#: ' . $case['APP_NUMBER']);
$headPublisher->assign('_PROJECT_TYPE', $isBpmn ? 'bpmn' : 'classic');
$headPublisher->assign('_PRO_UID', $case['PRO_UID']);
$headPublisher->assign('_APP_UID', $appUid);
$headPublisher->assign('_ENV_CURRENT_DATE', $conf->getSystemDate(date('Y-m-d')));
$headPublisher->assign('_ENV_CURRENT_DATE_NO_FORMAT', date('Y-m-d-h-i-A'));
$headPublisher->assign('idfirstform', is_null($step) ? '-1' : $step->getStepUidObj());
$headPublisher->assign('appStatus', $case['APP_STATUS']);
$headPublisher->assign('tbarGmail', $tBarGmail);
$headPublisher->assign('showCustomForm', $showCustomForm);
$headPublisher->assign('canClaimCase', $canClaimCase);

if (!isset($_SESSION['APPLICATION']) || !isset($_SESSION['TASK']) || !isset($_SESSION['INDEX'])) {
    $_SESSION['PROCESS'] = $case['PRO_UID'];
    $_SESSION['APPLICATION'] = $case['APP_UID'];
    $_SESSION['TASK'] = $case['TAS_UID'];
    $_SESSION['INDEX'] = $case['DEL_INDEX'];
}
$_SESSION['actionCaseOptions'] = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
G::RenderPage('publish', 'extJs');
