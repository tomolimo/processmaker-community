<?php

use Illuminate\Support\Facades\Cache;
use ProcessMaker\Core\System;
use ProcessMaker\Office365OAuth\Office365OAuth;

Cache::forget('errorMessageIfNotAuthenticate');
try {
    $header = "location:" . System::getServerMainPath() . "/setup/main?s=EMAIL_SERVER";

    $validInput = empty($_GET['code']) || empty($_SESSION['office365OAuth']) || !is_object($_SESSION['office365OAuth']);
    if ($validInput) {
        G::header($header);
        return;
    }

    $RBAC->allows(basename(__FILE__), "code");
    $office365OAuth = $_SESSION['office365OAuth'];

    $office365Client = $office365OAuth->getOffice365Client();

    $accessToken = $office365Client->getAccessToken('authorization_code', [
        'code' => $_GET['code'],
        'scope' => Office365OAuth::SMTP_SCOPE
    ]);

    $office365OAuth->setRefreshToken($accessToken->getRefreshToken());
    $office365OAuth->saveEmailServer();

    $office365OAuth->sendTestMailWithPHPMailerOAuth('Stevenmaguire\OAuth2\Client\Provider\Microsoft');
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
