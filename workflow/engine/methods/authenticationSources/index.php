<?php

use ProcessMaker\Exception\RBACException;

// Include global object RBAC
global $RBAC;

// Check if the current user have the correct permissions to access to this resource, if not throws a RBAC Exception with code 403
if ($RBAC->userCanAccess('PM_USERS') !== 1 || $RBAC->userCanAccess('PM_SETUP_USERS_AUTHENTICATION_SOURCES') !== 1) {
    throw new RBACException('ID_ACCESS_DENIED', 403);
}

global $G_PUBLISH;
$G_PUBLISH = new Publisher();
try {
    $conf = new Configurations();
    $pageSize = $conf->getEnvSetting('casesListRowNumber');
    $pageSize = empty($pageSize) ? 25 : $pageSize;
    $lang = defined("SYS_LANG") ? SYS_LANG : "en";

    $html = file_get_contents(PATH_HTML . "lib/authenticationSources/index.html");
    $html = str_replace("var pageSize=10;", "var pageSize={$pageSize};", $html);
    $html = str_replace("translation.en.js", "translation.{$lang}.js", $html);
    echo $html;
} catch (Exception $e) {
    $message = [
        'MESSAGE' => $e->getMessage()
    ];
    $G_PUBLISH->AddContent('xmlform', 'xmlform', 'login/showMessage', '', $message);
    G::RenderPage('publish', 'blank');
}