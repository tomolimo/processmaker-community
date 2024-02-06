<?php

/**
 * forgotPassword.php
 */
$conf = new Configurations();
$conf->loadConfig($obj, 'ENVIRONMENT_SETTINGS', '');
if (isset($conf->aConfig["login_enableForgotPassword"]) && $conf->aConfig["login_enableForgotPassword"] == "1") {
    $G_PUBLISH = new Publisher();
    $version = explode('.', trim(file_get_contents(PATH_GULLIVER . 'VERSION')));
    $version = isset($version[0]) ? intval($version[0]) : 0;
    if ($version >= 3) {
        $G_PUBLISH->AddContent('xmlform', 'xmlform', 'login/forgotPasswordpm3', '', array(), 'retrivePassword.php');
    } else {
        $G_PUBLISH->AddContent('xmlform', 'xmlform', 'login/forgotPassword', '', array(), 'retrivePassword.php');
    }
    G::RenderPage("publish");
} else {
    G::header('Location: /errors/error403.php');
    die();
}
