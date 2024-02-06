<?php

use Illuminate\Support\Facades\Cache;
use ProcessMaker\Core\System;
use ProcessMaker\GmailOAuth\GmailOAuth;

Cache::forget('errorMessageIfNotAuthenticate');
try {
    $header = "location:" . System::getServerMainPath() . "/setup/main?s=EMAIL_SERVER";

    $validInput = empty($_GET['code']) || empty($_SESSION['gmailOAuth']) || !is_object($_SESSION['gmailOAuth']);
    if ($validInput) {
        G::header($header);
        return;
    }

    $RBAC->allows(basename(__FILE__), "code");
    $gmailOAuth = $_SESSION['gmailOAuth'];

    $googleClient = $gmailOAuth->getGoogleClient();
    $result = $googleClient->authenticate($_GET['code']);
    if (isset($result["error"])) {
        Cache::put('errorMessageIfNotAuthenticate', G::json_decode($result["error"]), 120); //laravel 8.x the time parameter is in seconds.
        G::header($header);
        return;
    }

    $gmailOAuth->setRefreshToken($googleClient->getRefreshToken());
    $gmailOAuth->saveEmailServer();
    $gmailOAuth->sendTestMailWithPHPMailerOAuth();
} catch (Exception $e) {
    /**
     * The laravel cache is volatile in each session, you can specify the duration 
     * value in minutes for each session. We use 2 minutes, enough time to retrieve 
     * the error message if there is one.
     */
    Cache::put('errorMessageIfNotAuthenticate', $e->getMessage(), 120); //laravel 8.x the time parameter is in seconds.
}

G::header($header);
return;
