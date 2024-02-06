<?php

namespace ProcessMaker\GmailOAuth;

use AppMessage;
use Bootstrap;
use G;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use PHPMailerOAuth;
use ProcessMaker\BusinessModel\EmailServer;
use ProcessMaker\Core\System;
use TemplatePower;
use WsBase;

class GmailOAuth
{
    private $emailServerUid;
    private $emailEngine;
    private $clientID;
    private $clientSecret;
    private $fromAccount;
    private $senderEmail;
    private $senderName;
    private $sendTestMail;
    private $mailTo;
    private $setDefaultConfiguration;
    private $redirectURI;
    private $refreshToken;

    /**
     * Set $emailServerUid property.
     * @param string $emailServerUid
     * @return void
     */
    public function setEmailServerUid($emailServerUid): void
    {
        $this->emailServerUid = $emailServerUid;
    }

    /**
     * Set $clientID property.
     * @param string $clientID
     * @return void
     */
    public function setClientID($clientID): void
    {
        $this->clientID = $clientID;
    }

    /**
     * Set $clientSecret property.
     * @param string $clientSecret
     * @return void
     */
    public function setClientSecret($clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * Set $redirectURI property.
     * @param string $redirectURI
     * @return void
     */
    public function setRedirectURI($redirectURI): void
    {
        $this->redirectURI = $redirectURI;
    }

    /**
     * Set $emailEngine property.
     * @param string $emailEngine
     * @return void
     */
    public function setEmailEngine($emailEngine): void
    {
        $this->emailEngine = $emailEngine;
    }

    /**
     * Set $fromAccount property.
     * @param string $fromAccount
     * @return void
     */
    public function setFromAccount($fromAccount): void
    {
        $this->fromAccount = $fromAccount;
    }

    /**
     * Set $senderEmail property.
     * @param string $senderEmail
     * @return void
     */
    public function setSenderEmail($senderEmail): void
    {
        $this->senderEmail = $senderEmail;
    }

    /**
     * Set $senderName property.
     * @param string $senderName
     * @return void
     */
    public function setSenderName($senderName): void
    {
        $this->senderName = $senderName;
    }

    /**
     * Set $sendTestMail property.
     * @param string $sendTestMail
     * @return void
     */
    public function setSendTestMail($sendTestMail): void
    {
        $this->sendTestMail = $sendTestMail;
    }

    /**
     * Set $mailTo property.
     * @param string $mailTo
     * @return void
     */
    public function setMailTo($mailTo): void
    {
        $this->mailTo = $mailTo;
    }

    /**
     * Set $setDefaultConfiguration property.
     * @param string $setDefaultConfiguration
     * @return void
     */
    public function setSetDefaultConfiguration($setDefaultConfiguration): void
    {
        $this->setDefaultConfiguration = $setDefaultConfiguration;
    }

    /**
     * Set $refreshToken property.
     * @param string $refreshToken
     * @return void
     */
    public function setRefreshToken($refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * Get $emailServerUid property.
     * @return string
     */
    public function getEmailServerUid()
    {
        return $this->emailServerUid;
    }

    /**
     * Get $clientID property.
     * @return string
     */
    public function getClientID()
    {
        return $this->clientID;
    }

    /**
     * Get $clientSecret property.
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * Get $redirectURI property.
     * @return string
     */
    public function getRedirectURI()
    {
        return $this->redirectURI;
    }

    /**
     * Get $emailEngine property.
     * @return string
     */
    public function getEmailEngine()
    {
        return $this->emailEngine;
    }

    /**
     * Get $fromAccount property.
     * @return string
     */
    public function getFromAccount()
    {
        return $this->fromAccount;
    }

    /**
     * Get $senderEmail property.
     * @return string
     */
    public function getSenderEmail()
    {
        return $this->senderEmail;
    }

    /**
     * Get $senderName property.
     * @return string
     */
    public function getSenderName()
    {
        return $this->senderName;
    }

    /**
     * Get $sendTestMail property.
     * @return string
     */
    public function getSendTestMail()
    {
        return $this->sendTestMail;
    }

    /**
     * Get $mailTo property.
     * @return string
     */
    public function getMailTo()
    {
        return $this->mailTo;
    }

    /**
     * Get $defaultConfiguration property.
     * @return string
     */
    public function getSetDefaultConfiguration()
    {
        return $this->setDefaultConfiguration;
    }

    /**
     * Get $refreshToken property.
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
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
     * Save the data in the EmailServer table, and return the stored fields.
     * @return array
     */
    public function saveEmailServer(): array
    {
        $result = [];
        $data = [
            "MESS_ENGINE" => $this->emailEngine,
            "OAUTH_CLIENT_ID" => $this->clientID,
            "OAUTH_CLIENT_SECRET" => $this->clientSecret,
            "OAUTH_REFRESH_TOKEN" => $this->refreshToken,
            "MESS_ACCOUNT" => $this->fromAccount,
            "MESS_FROM_MAIL" => $this->senderEmail,
            "MESS_FROM_NAME" => $this->senderName,
            "MESS_TRY_SEND_INMEDIATLY" => $this->sendTestMail,
            "MAIL_TO" => $this->mailTo,
            "MESS_DEFAULT" => $this->setDefaultConfiguration,
            "MESS_RAUTH" => 1,
            "SMTPSECURE" => "No",
            "MESS_PASSWORD" => "",
            "MESS_SERVER" => "smtp.gmail.com",
            "MESS_PORT" => "",
            "MESS_PASSWORD" => "",
            "MESS_INCOMING_SERVER" => "",
            "MESS_INCOMING_PORT" => ""
        ];
        $emailServer = new EmailServer();
        if (empty($this->emailServerUid)) {
            $result = $emailServer->create($data);
        } else {
            $result = $emailServer->update($this->emailServerUid, $data);
        }
        return $result;
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

    /**
     * Get message body.
     * @return string
     */
    public function getMessageBody(): string
    {
        $templateTower = new TemplatePower(PATH_TPL . "admin" . PATH_SEP . "email.tpl");
        $templateTower->prepare();
        $templateTower->assign("server", System::getServerProtocol() . System::getServerHost());
        $templateTower->assign("date", date("H:i:s"));
        $templateTower->assign("ver", System::getVersion());
        $templateTower->assign("engine", G::LoadTranslation("ID_MESS_ENGINE_TYPE_4"));
        $templateTower->assign("msg", G::LoadTranslation("ID_MESS_TEST_BODY"));
        $outputContent = $templateTower->getOutputContent();
        return $outputContent;
    }

    /**
     * Get a plain text of the test message.
     * @return string
     */
    public function getRawMessage(): string
    {
        $outputContent = $this->getMessageBody();

        $strRawMessage = ""
                . "From: Email <{$this->fromAccount}> \r\n"
                . "To: <{$this->mailTo}>\r\n"
                . "Subject: =?utf-8?B?" . base64_encode(G::LoadTranslation("ID_MESS_TEST_SUBJECT")) . "?=\r\n"
                . "MIME-Version: 1.0\r\n"
                . "Content-Type: text/html; charset=utf-8\r\n"
                . "Content-Transfer-Encoding: quoted-printable\r\n\r\n"
                . "{$outputContent}\r\n";

        $raw = rtrim(strtr(base64_encode($strRawMessage), '+/', '-_'), '=');
        return $raw;
    }

    /**
     * This sends a test email with PHPMailerOAuth object, as long 
     * as the test flag is activated.
     * @return PHPMailerOAuth
     */
    public function sendTestMailWithPHPMailerOAuth(): PHPMailerOAuth
    {
        $phpMailerOAuth = new PHPMailerOAuth();
        if (!filter_var($this->fromAccount, FILTER_VALIDATE_EMAIL)) {
            return $phpMailerOAuth;
        }
        if (!filter_var($this->mailTo, FILTER_VALIDATE_EMAIL)) {
            return $phpMailerOAuth;
        }
        if ($this->sendTestMail === 0) {
            return $phpMailerOAuth;
        }
        $senderEmail = $this->senderEmail;
        if (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
            $senderEmail = $this->fromAccount;
        }
        if (empty($this->senderName)) {
            $this->senderName = "";
        }

        $phpMailerOAuth->isHTML(true);
        $phpMailerOAuth->isSMTP();
        $phpMailerOAuth->Host = 'smtp.gmail.com';
        $phpMailerOAuth->SMTPAuth = true;
        $phpMailerOAuth->AuthType = 'XOAUTH2';
        $phpMailerOAuth->oauthUserEmail = $this->fromAccount;
        $phpMailerOAuth->oauthClientId = $this->clientID;
        $phpMailerOAuth->oauthClientSecret = $this->clientSecret;
        $phpMailerOAuth->oauthRefreshToken = $this->refreshToken;
        $phpMailerOAuth->SetFrom($senderEmail, $this->senderName);
        $phpMailerOAuth->Subject = G::LoadTranslation("ID_MESS_TEST_SUBJECT");
        $phpMailerOAuth->Body = utf8_encode($this->getMessageBody());
        $phpMailerOAuth->AddAddress($this->mailTo);
        $status = $phpMailerOAuth->Send();
        $this->saveIntoStandardLogs($status ? "sent" : "pending");
        $this->saveIntoAppMessage($status ? "sent" : "pending");
        return $phpMailerOAuth;
    }

    /**
     * Register into APP_MESSAGE table.
     * @param string $status
     */
    public function saveIntoAppMessage(string $status = "")
    {
        $appMsgUid = G::generateUniqueID();
        $spool = new AppMessage();
        $spool->setAppMsgUid($appMsgUid);
        $spool->setMsgUid("");
        $spool->setAppUid("");
        $spool->setDelIndex(0);
        $spool->setAppMsgType(WsBase::MESSAGE_TYPE_TEST_EMAIL);
        $spool->setAppMsgTypeId(isset(AppMessage::$app_msg_type_values[WsBase::MESSAGE_TYPE_TEST_EMAIL]) ? AppMessage::$app_msg_type_values[WsBase::MESSAGE_TYPE_TEST_EMAIL] : 0);
        $spool->setAppMsgSubject(G::LoadTranslation("ID_MESS_TEST_SUBJECT"));
        $spool->setAppMsgFrom($this->fromAccount);
        $spool->setAppMsgTo($this->mailTo);
        $spool->setAppMsgBody(utf8_encode($this->getMessageBody()));
        $spool->setAppMsgDate(date('Y-m-d H:i:s'));
        $spool->setAppMsgCc("");
        $spool->setAppMsgBcc("");
        $spool->setappMsgAttach(serialize([""]));
        $spool->setAppMsgTemplate("");
        $spool->setAppMsgStatus($status);
        $spool->setAppMsgStatusId(AppMessage::$app_msg_status_values[$status] ? AppMessage::$app_msg_status_values[$status] : 0);
        $spool->setAppMsgSendDate(date('Y-m-d H:i:s'));
        $spool->setAppMsgShowMessage(1);
        $spool->setAppMsgError("");
        $spool->setAppNumber(0);
        $spool->setTasId(0);
        $spool->setProId(0);
        $spool->save();
    }

    /**
     * Register into standard logs.
     * @param string $status
     */
    public function saveIntoStandardLogs(string $status = "")
    {
        $channel = "Test Email Servers Configuration";
        $severity = 200; //INFO
        $message = "Email Server test has been sent";
        $context = [
            "emailServerUid" => $this->emailServerUid,
            "emailEngine" => $this->emailEngine,
            "from" => $this->fromAccount,
            "senderAccount" => $this->mailTo,
            "senderEmail" => $this->senderEmail,
            "senderName" => $this->senderName,
            "status" => $status
        ];
        $workspace = config("system.workspace");
        Bootstrap::registerMonolog($channel, $severity, $message, $context, $workspace);
    }
}
