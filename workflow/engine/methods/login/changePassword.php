<?php

use ProcessMaker\BusinessModel\User;

$user = new User();
$currentUser = $user->changePassword($_SESSION['USER_LOGGED'], $_POST['form']['USR_PASSWORD'], isset($_POST['form']['USER_LANG']) ? $_POST['form']['USER_LANG'] : "");
G::header('Location: ' . $currentUser["__REDIRECT_PATH__"]);
