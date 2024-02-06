<?php

global $G_PUBLISH;
$G_PUBLISH = new Publisher();
try {
    $lang = defined("SYS_LANG") ? SYS_LANG : "en";

    $html = file_get_contents(PATH_HTML . "lib/userPersonalInformation/index.html");
    $html = str_replace("translation.en.js", "translation.{$lang}.js", $html);
    echo $html;
} catch (Exception $e) {
    $message = [
        'MESSAGE' => $e->getMessage()
    ];
    $G_PUBLISH->AddContent('xmlform', 'xmlform', 'login/showMessage', '', $message);
    G::RenderPage('publish', 'blank');
}