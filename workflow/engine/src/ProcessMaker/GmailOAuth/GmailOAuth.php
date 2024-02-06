<?php

namespace ProcessMaker\GmailOAuth;

use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use ProcessMaker\EmailOAuth\EmailBase;

class GmailOAuth
{

    use EmailBase;

    /**
     * Constructor of the class.
     */
    public function __construct()
    {
        $this->setServer("smtp.gmail.com");
        $this->setPort(587);
    }

    /**
     * Get a Google_Client object, this may vary depending on the service provider.
     * @return Google_Client
     */
    public function getGoogleClient(): Google_Client
    {
        $googleClient = new Google_Client();
        $googleClient->setClientId($this->clientID);
        $googleClient->setClientSecret($this->clientSecret);
        $googleClient->setRedirectUri($this->redirectURI);
        $googleClient->setAccessType('offline');
        $googleClient->setApprovalPrompt('force');
        $googleClient->addScope(Google_Service_Gmail::MAIL_GOOGLE_COM);
        return $googleClient;
    }

    /**
     * This sends a test email with Google_Service_Gmail_Message object, as long 
     * as the test flag is activated.
     * @return Google_Service_Gmail_Message
     */
    public function sendTestEmailWithGoogleServiceGmail(): Google_Service_Gmail_Message
    {
        $googleServiceGmailMessage = new Google_Service_Gmail_Message();
        if (!filter_var($this->fromAccount, FILTER_VALIDATE_EMAIL)) {
            return $googleServiceGmailMessage;
        }
        if (!filter_var($this->mailTo, FILTER_VALIDATE_EMAIL)) {
            return $googleServiceGmailMessage;
        }
        if ($this->sendTestMail === 0) {
            return $googleServiceGmailMessage;
        }

        $googleClient = $this->getGoogleClient();
        $googleClient->refreshToken($this->getRefreshToken());
        if ($googleClient->isAccessTokenExpired()) {
            $newAccessToken = $googleClient->getAccessToken();
            $googleClient->setAccessToken($newAccessToken);
        }

        $raw = $this->getRawMessage();
        $googleServiceGmailMessage->setRaw($raw);

        $service = new Google_Service_Gmail($googleClient);
        $result = $service->users_messages->send("me", $googleServiceGmailMessage);
        return $result;
    }
}
