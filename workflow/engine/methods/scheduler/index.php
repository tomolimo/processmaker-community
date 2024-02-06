<?php
try {
    global $G_PUBLISH;
    $G_PUBLISH = new Publisher();
    $headPublisher = headPublisher::getSingleton();
    $category = (isset($_GET["category"]))? $_GET["category"] : null;
    /* Render page */

    $pmDynaform = new PmDynaform([]);
    if (!empty($_SESSION['USER_LOGGED'])) {
        $arrayTimeZoneId = DateTimeZone::listIdentifiers();
        $fields["timezoneArray"] = G::json_encode($arrayTimeZoneId);
    }

    $fields["server"] = System::getHttpServerHostnameRequestsFrontEnd();
    $fields["credentials"] = G::json_encode($pmDynaform->getCredentials());
    $fields["category"] = $category;
    $fields["lang"] = SYS_LANG;
    $fields["workspace"] = config("system.workspace");
    if (!empty(G::browserCacheFilesGetUid())) {
        $fields["translation"] = "/js/ext/translation." . SYS_LANG . "." . G::browserCacheFilesGetUid() . ".js";  
    } else {
        $fields["translation"] = "/js/ext/translation." . SYS_LANG . ".js";  
    }
    $G_PUBLISH->addContent('smarty' , 'scheduler/index.html' , '', '' , $fields); //Adding a HTML file .html
    $G_PUBLISH->addContent('smarty' , PATH_HOME . 'public_html/lib/taskscheduler/index.html'); //Adding a HTML file .html
    G::RenderPage("publish" , "raw");
} catch (Exception $e) {
    $message = [];
    $message['MESSAGE'] = $e->getMessage();
    $G_PUBLISH = new Publisher();
    $G_PUBLISH->AddContent('xmlform', 'xmlform', 'login/showMessage', '', $message);
    G::RenderPage('publish', 'blank');
    die();
}