<?php

global $RBAC;

if ($RBAC->userCanAccess('PM_SETUP_ADVANCE') != 1) {
    G::SendTemporalMessage('ID_USER_HAVENT_RIGHTS_PAGE', 'error', 'labels');
    G::header('location: ../login/login');
    return;
}

$G_MAIN_MENU = 'processmaker';
$G_SUB_MENU = 'users';
$G_ID_MENU_SELECTED = 'USERS';
$G_ID_SUB_MENU_SELECTED = 'AUTH_SOURCES';

$fields = ['AUTH_SOURCE_PROVIDER' => $_REQUEST['AUTH_SOURCE_PROVIDER']];

$G_PUBLISH = new Publisher();
if (file_exists(PATH_XMLFORM . 'ldapAdvanced/' . $fields['AUTH_SOURCE_PROVIDER'] . 'Edit.xml')) {
    $pluginEnabled = 1;
    if ($pluginEnabled == 1) {
        //The attributes the users

        $data = executeQuery("DESCRIBE USERS");
        $fieldSet = ["USR_ID", "USR_UID", "USR_USERNAME", "USR_PASSWORD", "USR_CREATE_DATE", "USR_UPDATE_DATE", "USR_COUNTRY", "USR_CITY", "USR_LOCATION", "DEP_UID", "USR_RESUME", "USR_ROLE", "USR_REPORTS_TO", "USR_REPLACED_BY", "USR_UX"];
        $attributes = null;

        foreach ($data as $value) {
            if (!(in_array($value["Field"], $fieldSet))) {
                $attributes = $attributes . $value["Field"] . "|";
            }
        }
        $fields["AUTH_SOURCE_ATTRIBUTE_IDS"] = $attributes;
        if (file_exists(PATH_XMLFORM . 'ldapAdvanced/' . $fields['AUTH_SOURCE_PROVIDER'] . 'Flag')) {
            $oHeadPublisher = headPublisher::getSingleton();

            $oHeadPublisher->assign("Fields", $fields);
            $oHeadPublisher->addExtJsScript(PATH_TPL . 'ldapAdvanced/library', false, true);
            $oHeadPublisher->addExtJsScript(PATH_TPL . 'ldapAdvanced/ldapAdvancedForm', false, true);
            $oHeadPublisher->addExtJsScript(PATH_TPL . 'ldapAdvanced/ldapAdvancedList', false, true);
            G::RenderPage('publish', 'extJs');
            return;
        }
        $G_PUBLISH->AddContent("xmlform", "xmlform", 'ldapAdvanced/' . $fields['AUTH_SOURCE_PROVIDER'] . 'Edit', '', $fields, '../authSources/authSources_Save');
    } else {
        $G_PUBLISH->AddContent('xmlform', 'xmlform', 'login/showMessage', '', ['MESSAGE' => G::LoadTranslation('ID_AUTH_SOURCE_MISSING')]);
    }
} else {
    if (file_exists(PATH_XMLFORM . 'authSources/' . $fields['AUTH_SOURCE_PROVIDER'] . 'Edit.xml')) {
        $G_PUBLISH->AddContent('xmlform', 'xmlform', 'authSources/' . $fields['AUTH_SOURCE_PROVIDER'] . 'Edit', '', $fields, '../authSources/authSources_Save');
    } else {
        $G_PUBLISH->AddContent('xmlform', 'xmlform', 'login/showMessage', '', ['MESSAGE' => 'File: ' . $fields['AUTH_SOURCE_PROVIDER'] . 'Edit.xml' . ' not exists.']);
    }
}

G::RenderPage("publish", "blank");
