<?php
/**
 * spoolRun - brief send email from the spool database, and see if we have all the addresses we send to.
 *
 * @author Ian K Armstrong <ika@[REMOVE_THESE_CAPITALS]openmail.cc>
 * @copyright Copyright (c) 2007, Ian K Armstrong
 * @license http://www.opensource.org/licenses/gpl-3.0.html GNU Public License
 * @link http://www.openmail.cc
 */

use ProcessMaker\Core\System;

/**
 * @package workflow.engine.ProcessMaker
 */
class SpoolRun
{
    private $appUid;
    private $appMsgUid;
    private $warnings = []; //Array to store the warning that were throws by the class
    private $exceptionCode = []; //Array to define the Exception codes
    private $fileData;
    private $longMailEreg;
    private $mailEreg;
    private $spoolId;

    public $config;
    public $error;
    public $status;


    /**
     * Class constructor - iniatilize default values
     *
     * @param none
     * @return none
     */
    public function __construct()
    {
        $this->config = array();
        $this->fileData = array();
        $this->spoolId = '';
        $this->status = 'pending';
        $this->error = '';

        $this->exceptionCode['FATAL'] = 1;
        $this->exceptionCode['WARNING'] = 2;
        $this->exceptionCode['NOTICE'] = 3;

        $this->longMailEreg = "/(.*)(<([\w\-\+\.']+@[\w\-_\.]+\.\w{2,5})+>)/";
        $this->mailEreg = "/^([\w\-_\+\.']+@[\w\-_\.]+\.\w{2,5}+)$/";
    }

    /**
     * Get the appUid
     *
     * @return string
     */
    public function getAppUid()
    {
        return $this->appUid;
    }

    /**
     * Set the appUid
     *
     * @param string $v
     */
    public function setAppUid($v)
    {
        $this->appUid = $v;
    }

    /**
     * Get the appMsgUid
     *
     * @return string
     */
    public function getAppMsgUid()
    {
        return $this->appMsgUid;
    }

    /**
     * Set the appMsgUid
     *
     * @param string $v
     */
    public function setAppMsgUid($v)
    {
        $this->appMsgUid = $v;
    }

    /**
     * Set the $spoolId
     *
     * @param string
     */
    public function setSpoolId($v)
    {
        $this->spoolId = $v;
    }

    /**
     * Get the $spoolId
     *
     * @return string
     */
    public function getSpoolId()
    {
        return $this->spoolId;
    }

    /**
     * Get the fileData property
     *
     * @return array
     */
    public function getFileData()
    {
        return $this->fileData;
    }

    /**
     * get all files into spool in a list
     *
     * @param none
     * @return none
     */
    public function getSpoolFilesList()
    {
        $sql = "SELECT * FROM APP_MESSAGE WHERE APP_MSG_STATUS ='pending'";

        $con = Propel::getConnection("workflow");
        $stmt = $con->prepareStatement($sql);
        $rs = $stmt->executeQuery();

        while ($rs->next()) {
            $this->spoolId = $rs->getString('APP_MSG_UID');
            $this->fileData['subject'] = $rs->getString('APP_MSG_SUBJECT');
            $this->fileData['from'] = $rs->getString('APP_MSG_FROM');
            $this->fileData['to'] = $rs->getString('APP_MSG_TO');
            $this->fileData['body'] = $rs->getString('APP_MSG_BODY');
            $this->fileData['date'] = $rs->getString('APP_MSG_DATE');
            $this->fileData['cc'] = $rs->getString('APP_MSG_CC');
            $this->fileData['bcc'] = $rs->getString('APP_MSG_BCC');
            $this->fileData['template'] = $rs->getString('APP_MSG_TEMPLATE');
            $this->fileData['attachments'] = array(); //$rs->getString('APP_MSG_ATTACH');
            $this->fileData['error'] = $rs->getString('APP_MSG_ERROR');
            if ($this->config['MESS_ENGINE'] == 'OPENMAIL') {
                if ($this->config['MESS_SERVER'] != '') {
                    if (($sAux = @gethostbyaddr($this->config['MESS_SERVER']))) {
                        $this->fileData['domain'] = $sAux;
                    } else {
                        $this->fileData['domain'] = $this->config['MESS_SERVER'];
                    }
                } else {
                    $this->fileData['domain'] = gethostbyaddr('127.0.0.1');
                }
            }
            $this->sendMail();
        }
    }

    /**
     * create a msg record for spool
     *
     * @param array $aData
     * @return none
     */
    public function create($aData)
    {
        if (is_array($aData['app_msg_attach'])) {
            $attachment = $aData['app_msg_attach'];
        } else {
            $attachment = @unserialize($aData['app_msg_attach']);
            if ($attachment === false) {
                $attachment = explode(',', $aData['app_msg_attach']);
            }
        }
        $aData['app_msg_attach'] = serialize($attachment);
        $aData['app_msg_show_message'] = (isset($aData['app_msg_show_message'])) ? $aData['app_msg_show_message'] : 1;
        $aData["app_msg_error"] = (isset($aData["app_msg_error"])) ? $aData["app_msg_error"] : '';
        $sUID = $this->dbInsert($aData);

        $aData['app_msg_date'] = isset($aData['app_msg_date']) ? $aData['app_msg_date'] : '';

        if (isset($aData['app_msg_status'])) {
            $this->status = strtolower($aData['app_msg_status']);
        }

        $aData["contentTypeIsHtml"] = (isset($aData["contentTypeIsHtml"])) ? $aData["contentTypeIsHtml"] : true;

        $this->setData($sUID, $aData["app_msg_subject"], $aData["app_msg_from"], $aData["app_msg_to"], $aData["app_msg_body"], $aData["app_msg_date"], $aData["app_msg_cc"], $aData["app_msg_bcc"], $aData["app_msg_template"], $aData["app_msg_attach"], $aData["contentTypeIsHtml"], $aData["app_msg_error"]);
    }

    /**
     * set configuration
     *
     * @param array $aConfig
     * @return none
     */
    public function setConfig($aConfig)
    {
        // Processing password
        $passwd = isset($aConfig['MESS_PASSWORD']) ? $aConfig['MESS_PASSWORD'] : '';
        $passwdDec = G::decrypt($passwd, 'EMAILENCRYPT');
        $auxPass = explode('hash:', $passwdDec);
        if (count($auxPass) > 1) {
            if (count($auxPass) == 2) {
                $passwd = $auxPass[1];
            } else {
                array_shift($auxPass);
                $passwd = implode('', $auxPass);
            }
        }
        $aConfig['MESS_PASSWORD'] = $passwd;

        // Validating authorization flag
        if (!isset($aConfig['SMTPAuth'])) {
            if (isset($aConfig['MESS_RAUTH'])) {
                if ($aConfig['MESS_RAUTH'] == false || (is_string($aConfig['MESS_RAUTH']) && $aConfig['MESS_RAUTH'] == 'false')) {
                    $aConfig['MESS_RAUTH'] = 0;
                } else {
                    $aConfig['MESS_RAUTH'] = 1;
                }
            } else {
                $aConfig['MESS_RAUTH'] = 0;
            }
            $aConfig['SMTPAuth'] = $aConfig['MESS_RAUTH'];
        }

        // Validating for old configurations
        if (!isset($aConfig['MESS_FROM_NAME'])) {
            $aConfig['MESS_FROM_NAME'] = '';
        }
        if (!isset($aConfig['MESS_FROM_MAIL'])) {
            $aConfig['MESS_FROM_MAIL'] = '';
        }

        $this->config = $aConfig;
    }

    /**
     * Set email parameters
     *
     * @param string $appMsgUid
     * @param string $subject
     * @param string $from
     * @param string $to
     * @param string $body
     * @param string $date
     * @param string $cc
     * @param string $bcc
     * @param string $template
     * @param array $attachments
     * @param bool $contentTypeIsHtml
     * @param string $error
     *
     * @see SpoolRun->create()
     * @see SpoolRun->resendEmails()
     */
    public function setData($appMsgUid, $subject, $from, $to, $body, $date = '', $cc = '', $bcc = '', $template = '', $attachments = [],
        $contentTypeIsHtml = true, $error = '')
    {
        // Fill "fileData" property
        $this->spoolId = $appMsgUid;
        $this->fileData['subject'] = $subject;
        $this->fileData['from'] = $from;
        $this->fileData['to'] = $to;
        $this->fileData['body'] = $body;
        $this->fileData['date'] = (!empty($date) ? $date : date('Y-m-d H:i:s'));
        $this->fileData['cc'] = $cc;
        $this->fileData['bcc'] = $bcc;
        $this->fileData['template'] = $template;
        $this->fileData['attachments'] = $attachments;
        $this->fileData["contentTypeIsHtml"] = $contentTypeIsHtml;
        $this->fileData["error"] = $error;

        // Initialize some values used internally
        $this->fileData['envelope_to'] = [];
        $this->fileData['envelope_cc'] = [];
        $this->fileData['envelope_bcc'] = [];

        // Domain validation when the email engine is "OpenMail"
        if (array_key_exists('MESS_ENGINE', $this->config)) {
            if ($this->config['MESS_ENGINE'] === 'OPENMAIL') {
                if (!empty($this->config['MESS_SERVER'])) {
                    if (($domain = @gethostbyaddr($this->config['MESS_SERVER']))) {
                        $this->fileData['domain'] = $domain;
                    } else {
                        $this->fileData['domain'] = $this->config['MESS_SERVER'];
                    }
                } else {
                    $this->fileData['domain'] = gethostbyaddr('127.0.0.1');
                }
            }
        }
    }

    /**
     * send mail
     *
     * @param none
     * @return boolean true or exception
     */
    public function sendMail()
    {
        try {
            $this->handleFrom();
            $this->handleEnvelopeTo();
            $this->handleMail();
            $this->updateSpoolStatus();
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * update the status to spool
     *
     * @param none
     * @return none
     */
    private function updateSpoolStatus()
    {
        $oAppMessage = AppMessagePeer::retrieveByPK($this->spoolId);
        if (is_array($this->fileData['attachments'])) {
            $attachment = implode(",", $this->fileData['attachments']);
            $oAppMessage->setappMsgAttach($attachment);
        }
        $oAppMessage->setAppMsgStatus($this->status);
        $oAppMessage->setAppMsgStatusId(isset(AppMessage::$app_msg_status_values[$this->status]) ? AppMessage::$app_msg_status_values[$this->status] : 0);
        $oAppMessage->setAppMsgSendDate(date('Y-m-d H:i:s'));
        $oAppMessage->save();
    }

    /**
     * Update the error
     *
     * @param string $msgError
     *
     * @return void
     *
     * @see SpoolRun::handleMail()
     */
    private function updateSpoolError($msgError)
    {
        $appMessage = AppMessagePeer::retrieveByPK($this->spoolId);
        $appMessage->setAppMsgError($msgError);
        $appMessage->setAppMsgSendDate(date('Y-m-d H:i:s'));
        $appMessage->save();

        $context = Bootstrap::getDefaultContextLog();
        $context["action"] = "Send email";
        $context["appMsgUid"] = $this->getAppMsgUid();
        $context["appUid"] = $this->getAppUid();
        Bootstrap::registerMonolog("SendEmail", 400, $msgError, $context);
    }

    /**
     * handle the email that was set in "TO" parameter
     *
     * @param none
     * @return boolean true or exception
     */
    private function handleFrom()
    {
        $eregA = "/^'.*@.*$/";

        if (strpos($this->fileData['from'], '<') !== false) {
            //to validate complex email address i.e. Erik A. O <erik@colosa.com>
            $ereg = (preg_match($eregA, $this->fileData["from"])) ? $this->longMailEreg : "/^(.*)(<(.*)>)$/";
            preg_match($ereg, $this->fileData["from"], $matches);

            if (isset($matches[1]) && $matches[1] != '') {
                //drop the " characters if they exist
                $this->fileData['from_name'] = trim(str_replace('"', '', $matches[1]));
            } else {
                //if the from name was not set
                $this->fileData['from_name'] = '';
            }

            if (!isset($matches[3])) {
                throw new Exception('Invalid email address in FROM parameter (' . $this->fileData['from'] . ')', $this->exceptionCode['WARNING']);
            }

            $this->fileData['from_email'] = trim($matches[3]);
        } else {
            //to validate simple email address i.e. erik@colosa.com
            $ereg = (preg_match($eregA, $this->fileData["from"])) ? $this->mailEreg : "/^(.*)$/";
            preg_match($ereg, $this->fileData["from"], $matches);

            if (!isset($matches[0])) {
                throw new Exception('Invalid email address in FROM parameter (' . $this->fileData['from'] . ')', $this->exceptionCode['WARNING']);
            }

            $this->fileData['from_name'] = '';
            $this->fileData['from_email'] = $matches[0];
        }

        // Set reply to
        preg_match($this->longMailEreg, $this->fileData['from_name'], $matches);
        if (isset($matches[3])) {
            $this->fileData['reply_to'] = $matches[3];
            $this->fileData['reply_to_name'] = isset($matches[1]) ? $matches[1] : $this->fileData['from_name'];
        } else {
            preg_match($this->mailEreg, $this->fileData['from_name'], $matches);
            if (isset($matches[1])) {
                $this->fileData['reply_to'] = $matches[1];
                $this->fileData['reply_to_name'] = '';
            } else {
                $this->fileData['reply_to'] = '';
                $this->fileData['reply_to_name'] = '';
            }
        }

    }

    /**
     * Handle all recipients to compose the mail
     *
     * @return void
     *
     * @see SpoolRun::sendMail()
     */
    private function handleEnvelopeTo()
    {
        $hold = [];
        $holdcc = [];
        $holdbcc = [];
        $text = trim($this->fileData['to']);

        $textcc = '';
        $textbcc = '';
        if (isset($this->fileData['cc']) && trim($this->fileData['cc']) != '') {
            $textcc = trim($this->fileData['cc']);
        }

        if (isset($this->fileData['bcc']) && trim($this->fileData['bcc']) != '') {
            $textbcc = trim($this->fileData['bcc']);
        }

        if (false !== (strpos($text, ','))) {
            $hold = explode(',', $text);

            foreach ($hold as $val) {
                if (strlen($val) > 0) {
                    $this->fileData['envelope_to'][] = "$val";
                }
            }

        } elseif ($text != '') {
            $this->fileData['envelope_to'][] = "$text";
        } else {
            $this->fileData['envelope_to'] = [];
        }

        if (empty($this->fileData['envelope_to'])){
            $this->updateSpoolError('Invalid address: ' . $text);
        }

        //CC
        if (false !== (strpos($textcc, ','))) {
            $holdcc = explode(',', $textcc);

            foreach ($holdcc as $valcc) {
                if (strlen($valcc) > 0) {
                    $this->fileData['envelope_cc'][] = "$valcc";
                }
            }
        } elseif ($textcc != '') {
            $this->fileData['envelope_cc'][] = "$textcc";
        } else {
            $this->fileData['envelope_cc'] = [];
        }

        //BCC
        if (false !== (strpos($textbcc, ','))) {
            $holdbcc = explode(',', $textbcc);

            foreach ($holdbcc as $valbcc) {
                if (strlen($valbcc) > 0) {
                    $this->fileData['envelope_bcc'][] = "$valbcc";
                }
            }
        } elseif ($textbcc != '') {
            $this->fileData['envelope_bcc'][] = "$textbcc";
        } else {
            $this->fileData['envelope_bcc'] = [];
        }

    }

    /**
     * Handle and compose the email content and parameters
     *
     * @return void
     *
     * @throws Exception
     *
     * @see SpoolRun::sendMail()
     */
    private function handleMail()
    {
        if (count($this->fileData['envelope_to']) > 0) {
            if (array_key_exists('MESS_ENGINE', $this->config)) {
                switch ($this->config['MESS_ENGINE']) {
                    case 'MAIL':
                    case 'PHPMAILER':
                    case 'IMAP':
                        switch ($this->config['MESS_ENGINE']) {
                            case 'MAIL':
                                $phpMailer = new PHPMailer();
                                $phpMailer->Mailer = 'mail';
                                break;
                            case 'IMAP':
                            case 'PHPMAILER':
                                $phpMailer = new PHPMailer(true);
                                $phpMailer->Mailer = 'smtp';
                                break;
                        }

                        $phpMailer->SMTPAuth = (isset($this->config['SMTPAuth']) ? $this->config['SMTPAuth'] : '');

                        switch ($this->config['MESS_ENGINE']) {
                            case 'MAIL':
                                break;
                            case 'IMAP':
                            case 'PHPMAILER':
                                //Posible Options for SMTPSecure are: "", "ssl" or "tls"
                                if (isset($this->config['SMTPSecure']) && preg_match('/^(ssl|tls)$/', $this->config['SMTPSecure'])) {
                                    $phpMailer->SMTPSecure = $this->config['SMTPSecure'];
                                }
                                break;
                        }

                        try {
                            $systemConfiguration = System::getSystemConfiguration();
                            $phpMailer->Timeout = is_numeric($systemConfiguration['smtp_timeout']) ? $systemConfiguration['smtp_timeout'] : 20;
                            $phpMailer->CharSet = "UTF-8";
                            $phpMailer->Encoding = "8bit";
                            $phpMailer->Host = $this->config['MESS_SERVER'];
                            $phpMailer->Port = $this->config['MESS_PORT'];
                            $phpMailer->Username = $this->config['MESS_ACCOUNT'];
                            $phpMailer->Password = $this->config['MESS_PASSWORD'];

                            //From
                            $phpMailer->SetFrom($this->fileData['from_email'], utf8_decode($this->fileData['from_name']));
                            //Reply to
                            if (isset($this->fileData['reply_to'])) {
                                if ($this->fileData['reply_to'] != '') {
                                    $phpMailer->AddReplyTo($this->fileData['reply_to'], $this->fileData['reply_to_name']);
                                }
                            }
                            //Subject
                            $msSubject = $this->fileData['subject'];
                            if (!(mb_detect_encoding($msSubject, "UTF-8") == "UTF-8")) {
                                $msSubject = utf8_encode($msSubject);
                            }
                            $phpMailer->Subject = $msSubject;
                            //Body
                            $msBody = $this->fileData['body'];
                            if (!(mb_detect_encoding($msBody, "UTF-8") == "UTF-8")) {
                                $msBody = utf8_encode($msBody);
                            }
                            $phpMailer->Body = $msBody;
                            //Attachments
                            $attachment = @unserialize($this->fileData['attachments']);
                            if ($attachment === false) {
                                $attachment = $this->fileData['attachments'];
                            }
                            if (is_array($attachment)) {
                                foreach ($attachment as $key => $fileAttach) {
                                    if (file_exists($fileAttach)) {
                                        $phpMailer->AddAttachment($fileAttach, is_int($key) ? '' : $key);
                                    }
                                }
                            }
                            //To
                            foreach ($this->fileData['envelope_to'] as $email) {
                                if (strpos($email, '<') !== false) {
                                    preg_match($this->longMailEreg, $email, $matches);
                                    $toAddress = '';
                                    if (!empty($matches[3])) {
                                        $toAddress = trim($matches[3]);
                                    }
                                    $toName = '';
                                    if (!empty($matches[1])) {
                                        $toName = trim($matches[1]);
                                    }
                                    if (!empty($toAddress)) {
                                        $phpMailer->AddAddress($toAddress, $toName);
                                    } else {
                                        throw new Exception('Invalid address: ' . $email);
                                    }
                                } else {
                                    $phpMailer->AddAddress($email);
                                }
                            }
                            //CC
                            foreach ($this->fileData['envelope_cc'] as $email) {
                                if (strpos($email, '<') !== false) {
                                    preg_match($this->longMailEreg, $email, $matches);
                                    $ccAddress = '';
                                    if (!empty($matches[3])) {
                                        $ccAddress = trim($matches[3]);
                                    }
                                    $ccName = '';
                                    if (!empty($matches[1])) {
                                        $ccName = trim($matches[1]);
                                    }
                                    if (!empty($ccAddress)) {
                                        $phpMailer->AddCC($ccAddress, $ccName);
                                    } else {
                                        throw new Exception('Invalid address: ' . $email);
                                    }
                                } else {
                                    $phpMailer->AddCC($email);
                                }
                            }
                            //BCC
                            foreach ($this->fileData['envelope_bcc'] as $email) {
                                if (strpos($email, '<') !== false) {
                                    preg_match($this->longMailEreg, $email, $matches);
                                    $bccAddress = '';
                                    if (!empty($matches[3])) {
                                        $bccAddress = trim($matches[3]);
                                    }
                                    $bccName = '';
                                    if (!empty($matches[1])) {
                                        $bccName = trim($matches[1]);
                                    }
                                    if (!empty($bccAddress)) {
                                        $phpMailer->AddBCC($bccAddress, $bccName);
                                    } else {
                                        throw new Exception('Invalid address: ' . $email);
                                    }
                                } else {
                                    $phpMailer->AddBCC($email);
                                }
                            }
                            //IsHtml
                            $phpMailer->IsHTML($this->fileData["contentTypeIsHtml"]);

                            if ($this->config['MESS_ENGINE'] == 'MAIL') {
                                $phpMailer->WordWrap = 300;
                            }

                            if ($phpMailer->Send()) {
                                $this->error = '';
                                $this->status = 'sent';
                            } else {
                                $this->error = $phpMailer->ErrorInfo;
                                $this->status = 'failed';
                                $this->updateSpoolError($this->error);
                            }
                        } catch (Exception $error) {
                            $this->updateSpoolError($error->getMessage());
                        }
                        break;
                    case 'OPENMAIL':
                        $pack = new package($this->fileData);
                        $header = $pack->returnHeader();
                        $body = $pack->returnBody();
                        $send = new smtp();
                        $send->setServer($this->config['MESS_SERVER']);
                        $send->setPort($this->config['MESS_PORT']);
                        $send->setUsername($this->config['MESS_ACCOUNT']);

                        $passwd = $this->config['MESS_PASSWORD'];
                        $passwdDec = G::decrypt($passwd, 'EMAILENCRYPT');
                        $auxPass = explode('hash:', $passwdDec);

                        if (count($auxPass) > 1) {
                            if (count($auxPass) == 2) {
                                $passwd = $auxPass[1];
                            } else {
                                array_shift($auxPass);
                                $passwd = implode('', $auxPass);
                            }
                        }

                        $this->config['MESS_PASSWORD'] = $passwd;
                        $send->setPassword($this->config['MESS_PASSWORD']);
                        $send->setReturnPath($this->fileData['from_email']);
                        $send->setHeaders($header);
                        $send->setBody($body);
                        $send->setEnvelopeTo($this->fileData['envelope_to']);
                        if ($send->sendMessage()) {
                            $this->error = '';
                            $this->status = 'sent';
                        } else {
                            $this->error = implode(', ', $send->returnErrors());
                            $this->status = 'failed';
                        }
                        break;
                }
            }
        }
    }

    /**
     * Try to resend the emails from spool
     *
     * @param string $dateResend
     * @param integer $cron
     *
     * @return none or exception
     */
    public function resendEmails($dateResend = null, $cron = 0)
    {
        $configuration = System::getEmailConfiguration();

        if (!isset($configuration["MESS_ENABLED"])) {
            $configuration["MESS_ENABLED"] = '0';
        }

        if ($configuration["MESS_ENABLED"] == "1") {
            require_once("classes/model/AppMessage.php");

            $this->setConfig($configuration);

            $criteria = new Criteria("workflow");
            $criteria->add(AppMessagePeer::APP_MSG_STATUS_ID, AppMessage::MESSAGE_STATUS_PENDING, Criteria::EQUAL);

            if ($dateResend != null) {
                $criteria->add(AppMessagePeer::APP_MSG_DATE, $dateResend, Criteria::GREATER_EQUAL);
            }

            $rsCriteria = AppMessagePeer::doSelectRS($criteria);
            $rsCriteria->setFetchmode(ResultSet::FETCHMODE_ASSOC);

            while ($rsCriteria->next()) {
                if ($cron == 1) {
                    $arrayCron = unserialize(trim(@file_get_contents(PATH_DATA . "cron")));
                    $arrayCron["processcTimeStart"] = time();
                    @file_put_contents(PATH_DATA . "cron", serialize($arrayCron));
                }

                $row = $rsCriteria->getRow();

                try {
                    $from = G::buildFrom($configuration, $row["APP_MSG_FROM"]);

                    $this->setData(
                        $row["APP_MSG_UID"],
                        $row["APP_MSG_SUBJECT"],
                        $from,
                        $row["APP_MSG_TO"],
                        $row["APP_MSG_BODY"],
                        date("Y-m-d H:i:s"),
                        $row["APP_MSG_CC"],
                        $row["APP_MSG_BCC"],
                        $row["APP_MSG_TEMPLATE"],
                        $row["APP_MSG_ATTACH"]
                    );

                    $this->sendMail();
                } catch (Exception $e) {
                    $strAux = "Spool::resendEmails(): Using " . $configuration["MESS_ENGINE"] . " for APP_MGS_UID=" . $row["APP_MSG_UID"] . " -> With message: " . $e->getMessage();

                    if ($e->getCode() == $this->exceptionCode["WARNING"]) {
                        array_push($this->warnings, $strAux);
                        continue;
                    } else {
                        error_log('<400> ' . $strAux);
                        continue;
                    }
                }
            }
        }
    }

    /**
     * gets all warnings
     *
     * @param none
     * @return string $this->aWarnings
     */
    public function getWarnings()
    {
        if (sizeof($this->warnings) != 0) {
            return $this->warnings;
        }

        return false;
    }

    /**
     * Insert the record in the AppMessage
     *
     * @param array $dbSpool
     *
     * @return string
     *
     * @see SpoolRun::create()
     */
    public function dbInsert($dbSpool)
    {
        $appMsgUid = G::generateUniqueID();
        //Set some values for generate the log
        $this->setAppMsgUid($appMsgUid);
        $this->setAppUid($dbSpool['app_uid']);
        //Set values for register the record
        $spool = new AppMessage();
        $spool->setAppMsgUid($appMsgUid);
        $spool->setMsgUid($dbSpool['msg_uid']);
        $spool->setAppUid($dbSpool['app_uid']);
        $spool->setDelIndex($dbSpool['del_index']);
        $spool->setAppMsgType($dbSpool['app_msg_type']);
        $spool->setAppMsgTypeId(isset(AppMessage::$app_msg_type_values[$dbSpool['app_msg_type']]) ? AppMessage::$app_msg_type_values[$dbSpool['app_msg_type']] : 0);
        $spool->setAppMsgSubject($dbSpool['app_msg_subject']);
        $spool->setAppMsgFrom($dbSpool['app_msg_from']);
        $spool->setAppMsgTo($dbSpool['app_msg_to']);
        $spool->setAppMsgBody($dbSpool['app_msg_body']);
        $spool->setAppMsgDate(date('Y-m-d H:i:s'));
        $spool->setAppMsgCc($dbSpool['app_msg_cc']);
        $spool->setAppMsgBcc($dbSpool['app_msg_bcc']);
        $spool->setappMsgAttach($dbSpool['app_msg_attach']);
        $spool->setAppMsgTemplate($dbSpool['app_msg_template']);
        $spool->setAppMsgStatus($dbSpool['app_msg_status']);
        $spool->setAppMsgStatusId(isset(AppMessage::$app_msg_status_values[$dbSpool['app_msg_status']]) ? AppMessage::$app_msg_status_values[$dbSpool['app_msg_status']] : 0);
        $spool->setAppMsgSendDate(date('Y-m-d H:i:s'));
        $spool->setAppMsgShowMessage($dbSpool['app_msg_show_message']);
        $spool->setAppMsgError($dbSpool['app_msg_error']);

        $appDelegation = new AppDelegation();
        if (empty($dbSpool['app_number'])) {
            $delegationIds = $appDelegation->getColumnIds($dbSpool['app_uid'], $dbSpool['del_index']);
            if (is_array($delegationIds) && count($delegationIds) > 0) {
                $delegationIds = array_change_key_case($delegationIds);
                $appNumber = $delegationIds['app_number'];
            } else {
                //The notification is not related to case
                $appNumber = 0;
            }
        } else {
            $appNumber = $dbSpool['app_number'];
        }

        if (empty($dbSpool['tas_id'])) {
            $tasId = isset($delegationIds['tas_id']) ? $delegationIds['tas_id'] : 0;
        } else {
            $tasId = $dbSpool['tas_id'];
        }

        if (empty($dbSpool['pro_id'])) {
            $proId = isset($delegationIds['pro_id']) ? $delegationIds['pro_id'] : $appDelegation->getProcessId($appNumber);
        } else {
            $proId = $dbSpool['pro_id'];
        }

        $spool->setAppNumber($appNumber);
        $spool->setTasId($tasId);
        $spool->setProId($proId);

        if (!$spool->validate()) {
            $errors = $spool->getValidationFailures();
            $this->status = 'error';

            foreach ($errors as $key => $value) {
                echo "Validation error - " . $value->getMessage($key) . "\n";
            }
        } else {
            //echo "Saving - validation ok\n";
            $this->status = 'success';
            $spool->save();
        }

        return $appMsgUid;
    }

    /**
     * Run the private method "handleEnvelopeTo", this method was created in order to use in the unit tests
     */
    public function runHandleEnvelopeTo()
    {
        $this->handleEnvelopeTo();
    }
}
