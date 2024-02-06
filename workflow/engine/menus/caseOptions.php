<?php
/**
  CaseOptions define the menus that will show when a case is open
 *
 * @see Ajax::getCaseMenu()
 * @see cases/cases_CatchSelfService.php
 * @see cases/Cases_Resume.php
 *
 * @link https://wiki.processmaker.com/3.2/Cases/Running_Cases
 */
global $G_TMP_MENU;
global $sStatus;
global $RBAC;

$viewSteps = true;
$statusSendAndUnassigned = false;
$listName = $_SESSION['actionCaseOptions'];
//caseOptions
switch ($listName) {
    case 'todo':
    case 'draft':
        if (isset($_SESSION['bNoShowSteps'])) {
            unset($_SESSION['bNoShowSteps']);
        }
        break;
    case 'sent':
    case 'unassigned':
        $statusSendAndUnassigned = true;
        break;
    case 'paused':
        $viewSteps = false;
        break;
    case 'to_revise':
        $access = $RBAC->requirePermissions('PM_REASSIGNCASE', 'PM_SUPERVISOR');
        if ($access) {
            if (isset($_SESSION['bNoShowSteps'])) {
                unset($_SESSION['bNoShowSteps']);
            }
        }
        break;
    case 'to_reassign':
        $access = $RBAC->requirePermissions('PM_REASSIGNCASE', 'PM_SUPERVISOR');
        if ($access) {
            $aData = AppDelegation::getCurrentUsers($_SESSION['APPLICATION'], $_SESSION['INDEX']);
            if (isset($aData) && !in_array($_SESSION['USER_LOGGED'], $aData)) {
                $viewSteps = false;
            }
        }
        break;
    default:
        $aData = AppDelegation::getCurrentUsers($_SESSION['APPLICATION'], $_SESSION['INDEX']);
        unset($_SESSION['bNoShowSteps']);
        if (isset($aData) && !in_array($_SESSION['USER_LOGGED'], $aData)) {
            $viewSteps = false;
        }
        break;
}

unset($_SESSION['actionCaseOptions']);

if ((($sStatus === 'DRAFT') || ($sStatus === 'TO_DO')) && !$statusSendAndUnassigned) {
    //Menu: Steps
    if ($viewSteps === true) {
        $G_TMP_MENU->AddIdOption('STEPS', G::LoadTranslation('ID_STEPS'), 'javascript:showSteps();', 'absolute');
    }
    //Menu: Information
    $G_TMP_MENU->AddIdOption('INFO', G::LoadTranslation('ID_INFORMATION'), 'javascript:showInformation();', 'absolute');
    //Menu: Actions
    $G_TMP_MENU->AddIdOption('ACTIONS', G::LoadTranslation('ID_ACTIONS'), 'javascript:showActions();', 'absolute');
} else {
    //Menu: Information
    $G_TMP_MENU->AddIdOption('INFO', G::LoadTranslation('ID_INFORMATION'), 'javascript:showInformation();', 'absolute');
}
//Menu: Cases Notes
$G_TMP_MENU->AddIdOption('NOTES', G::LoadTranslation('ID_NOTES'), 'javascript:showNotes();', 'absolute');
//Menu: Return to advanced search button
if ($listName === 'search'){
    $G_TMP_MENU->AddIdOption('RETURN_ADVANCED_SEARCH', G::LoadTranslation('ID_RETURN_ADVANCED_SEARCH'), 'javascript:showReturnAdvancedSearch();', 'absolute');
}
