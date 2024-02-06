<?php

if (!empty($_POST['form'])) {
    if (!empty($_POST['form']['buttonContinue'])) {
        $_SESSION['__WEBENTRYCONTINUE__'] = true;
        if (!empty($_SESSION['USER_LOGGED'])) {
            $_SESSION['__WEBENTRYCONTINUE_USER_LOGGED__'] = $_SESSION['USER_LOGGED'];
        }
    }
    if (!empty($_POST['form']['buttonLogout'])) {
        $_SESSION = [];
    }
    G::header('Location: ' . $_SERVER['HTTP_REFERER']);
}