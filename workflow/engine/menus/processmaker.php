<?php
/**
 * processmaker.php
 *
 * ProcessMaker main menu
 */

global $G_TMP_MENU;
global $RBAC;

// HOME MODULE
if ($RBAC->userCanAccess('PM_CASES') == 1) {
    $G_TMP_MENU->AddIdRawOption('CASES', 'cases/main', G::LoadTranslation('ID_HOME'), '', '', '', 'x-pm-home');
}

// DESIGNER MODULE
if ($RBAC->userCanAccess('PM_FACTORY') == 1) {
    $G_TMP_MENU->AddIdRawOption('PROCESSES', 'processes/main', G::LoadTranslation('ID_DESIGNER'), '', '', '', 'x-pm-designer');
}

// DASHBOARD MODULE
if ($RBAC->userCanAccess('PM_DASHBOARD') == 1) {
    $G_TMP_MENU->AddIdRawOption('DASHBOARD', 'dashboard/main', G::LoadTranslation('ID_DASHBOARD'), '', '', '', 'x-pm-dashboard');
}


// ADMIN MODULE
if ($RBAC->userCanAccess('PM_SETUP') == 1 || $RBAC->userCanAccess('PM_USERS') == 1) {
    $G_TMP_MENU->AddIdRawOption('SETUP', 'setup/main', G::LoadTranslation('ID_SETUP'), '', '', '', 'x-pm-setup');
}


// PLUGINS MENUS
if (file_exists(PATH_CORE . 'menus/plugin.php')) {
    require_once(PATH_CORE . 'menus/plugin.php');
}
