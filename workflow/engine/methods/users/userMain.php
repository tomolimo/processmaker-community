<?php

$headPublisher = headPublisher::getSingleton();
$headPublisher->addContent("users" . PATH_SEP . "userMain"); //Adding a html file .html
$headPublisher->addExtJsScript("users" . PATH_SEP . "userMain", true); //Adding a javascript file .js
$headPublisher->assign("CREATE_CLIENT", (isset($_GET["create_app"])) ? 1 : 0);
$headPublisher->assign("userInterface", (isset($_REQUEST["userInterface"]) ? $_REQUEST["userInterface"] : ""));
G::RenderPage("publish", "extJs");
