<?php

use Illuminate\Support\Facades\View;
use ProcessMaker\Core\System;
use ProcessMaker\Model\User;

global $translation;
global $RBAC;

$conf = new Configurations();

if ($RBAC->userCanAccess("PM_SETUP") != 1 || $RBAC->userCanAccess("PM_SETUP_ADVANCE") != 1) {
    G::SendTemporalMessage("ID_USER_HAVENT_RIGHTS_PAGE", "error", "labels");
    exit(0);
}

$availableFields = array();

$oHeadPublisher = headPublisher::getSingleton();

$oHeadPublisher->addExtJsScript('cases/casesListSetup', false); //adding a javascript file .js
$oHeadPublisher->addContent('cases/casesListSetup'); //adding a html file  .html.
$oHeadPublisher->assignNumber("pageSize", 20); //sending the page size
$oHeadPublisher->assignNumber("availableFields", G::json_encode($availableFields));

$userCanAccess = 1;
$pmDynaform = new PmDynaform();

$oHeadPublisher->assign('window.config', []);
$oHeadPublisher->assign('window.config.SYS_CREDENTIALS', base64_encode(G::json_encode($pmDynaform->getCredentials())));
$oHeadPublisher->assign('window.config.SYS_SERVER_API', System::getHttpServerHostnameRequestsFrontEnd());
$oHeadPublisher->assign('window.config.SYS_SERVER_AJAX', System::getServerProtocolHost());
$oHeadPublisher->assign('window.config.SYS_WORKSPACE', config('system.workspace'));
$oHeadPublisher->assign('window.config.SYS_URI', SYS_URI);
$oHeadPublisher->assign('window.config.SYS_LANG', SYS_LANG);
$oHeadPublisher->assign('window.config.TRANSLATIONS', $translation);
$oHeadPublisher->assign('window.config.FORMATS', $conf->getFormats());
$oHeadPublisher->assign('window.config.userId', User::getId($_SESSION['USER_LOGGED']));

echo View::make('Views::admin.settings.customCasesList', compact('userCanAccess', 'oHeadPublisher'))->render();