<?php

use ProcessMaker\BusinessModel\EmailServer;
use ProcessMaker\Core\System;
use ProcessMaker\Services\Api\Project\Variable;

class ActionsByEmailCoreClass extends PMPlugin
{
    const BODY_REPLY_LF = '%0D%0A';
    private $abeRequest = [];
    private $appNumber = null;
    private $appUid = null;
    private $caseProperties = [];
    private $delimiter = ',';
    private $emailFrom = null;
    private $emailTo = null;
    private $index = null;
    private $prefix = ['@@', '@#', '@=', '@%', '@?', '@$'];
    private $previousUser = null;
    private $replyTo = null;
    private $servicePath = '/services/ActionsByEmail';
    private $subject = null;
    private $task = null;
    private $taskAbeProperties = [];
    private $user = null;
    private $workspace = null;

    public function __construct()
    {
        $this->defineWorkspace();
        $this->defineServicePath();
    }

    /**
     * Set the action by email request
     *
     * @param array $v
     */
    public function setAbeRequest($v)
    {
        $this->abeRequest = $v;
    }

    /**
     * Get the action by email request
     *
     * @return array
     */
    public function getAbeRequest()
    {
        return $this->abeRequest;
    }

    /**
     * Set the specific task property
     *
     * @param array $item
     *
     * @return void
     */
    public function addItemAbeRequest(array $item)
    {
        $this->abeRequest = array_merge($this->abeRequest, $item);
    }

    /**
     * Get the specific task property
     *
     * @param string $key
     *
     * @return string
     */
    public function getItemAbeRequest($key)
    {
        if (array_key_exists($key, $this->getAbeRequest())) {
            return $this->abeRequest[$key];
        } else {
            return [];
        }
    }

    /**
     * Set the application uid
     *
     * @param string $v
     */
    public function setAppUid($v)
    {
        $this->appUid = $v;
    }

    /**
     * Get the application uid
     *
     * @return string
     */
    public function getAppUid()
    {
        return $this->appUid;
    }

    /**
     * Set the case number
     *
     * @param integer $v
     */
    public function setAppNumber($v)
    {
        $this->appNumber = $v;
    }

    /**
     * Get the case number
     *
     * @return integer
     */
    public function getAppNumber()
    {
        return $this->appNumber;
    }

    /**
     * Set the case properties
     *
     * @param array $v
     */
    public function setCaseProperties(array $v)
    {
        $this->caseProperties = $v;
    }

    /**
     * Get the case properties
     *
     * @return array
     */
    public function getCaseProperties()
    {
        return $this->caseProperties;
    }

    /**
     * Get the specific case property
     *
     * @param string $key
     *
     * @return array
     */
    public function getCasePropertiesKey($key)
    {
        if (array_key_exists($key, $this->getCaseProperties())) {
            return $this->caseProperties[$key];
        } else {
            return [];
        }
    }

    /**
     * Set the email from
     *
     * @param string $v
     */
    public function setEmailFrom($v)
    {
        $this->emailFrom = $v;
    }

    /**
     * Get the email from
     *
     * @return string
     */
    public function getEmailFrom()
    {
        return $this->emailFrom;
    }

    /**
     * Set the email to
     *
     * @param string $v
     */
    public function setEmailTo($v)
    {
        $this->emailTo = $v;

    }

    /**
     * Get the email to
     *
     * @return string
     */
    public function getEmailTo()
    {
        return $this->emailTo;
    }

    /**
     * Set the index
     *
     * @param integer $v
     */
    public function setIndex($v)
    {
        $this->index = $v;
    }

    /**
     * Get the index
     *
     * @return integer
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Set prefix
     *
     * @param array $v
     */
    public function setPrefix(array $v)
    {
        $this->prefix = $v;
    }

    /**
     * Get prefix
     *
     * @return array
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set the previous user
     *
     * @param string $v
     */
    public function setPreviousUser($v)
    {
        $this->previousUser = $v;
    }

    /**
     * Get the previous user
     *
     * @return string
     */
    public function getPreviousUser()
    {
        return $this->previousUser;
    }

    /**
     * Set the reply to
     *
     * @param string $v
     */
    public function setReplyTo($v)
    {
        $this->replyTo = $v;
    }

    /**
     * Get the reply to
     *
     * @return string
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * Set the task
     *
     * @param string $v
     */
    public function setTask($v)
    {
        $this->task = $v;
    }

    /**
     * Get the task
     *
     * @return string
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Set in the task the action by email properties
     *
     * @param array $v
     */
    public function setTaskAbeProperties(array $v)
    {
        $this->taskAbeProperties = $v;
    }

    /**
     * Get the task the action by email properties
     *
     * @return array
     */
    public function getTaskAbeProperties()
    {
        return $this->taskAbeProperties;
    }

    /**
     * Add a item in the action by email properties
     *
     * @param array $item
     *
     * @return void
     */
    public function addItemAbeProperties(array $item)
    {
        $this->taskAbeProperties = array_merge($this->taskAbeProperties, $item);
    }

    /**
     * Get the specific task property
     *
     * @param string $key
     *
     * @return array
     */
    public function getItemAbeProperties($key)
    {
        if (array_key_exists($key, $this->getTaskAbeProperties())) {
            return $this->taskAbeProperties[$key];
        } else {
            return [];
        }
    }

    /**
     * Set the link
     */
    public function defineServicePath()
    {
        $this->servicePath = System::getServerMainPath() . '/services/ActionsByEmail';
    }

    /**
     * Get the link
     *
     * @return string
     */
    public function getServicePath()
    {
        return $this->servicePath;
    }

    /**
     * Set the user uid
     *
     * @param string $v
     */
    public function setUser($v)
    {
        $this->user = $v;
    }

    /**
     * Get the user uid
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the subject
     *
     * @param string $v
     */
    public function setSubject($v)
    {
        $this->subject = $v;
    }

    /**
     * Get the subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set the workspace name
     */
    public function defineWorkspace()
    {
        $this->workspace = config("system.workspace");
    }

    /**
     * Get the workspace name
     *
     * @return string
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * Get the email server definition
     *
     * @param string $emailServerKey
     *
     * @return object
     */
    private function getEmailServer($emailServerKey)
    {
        $emailServer = new EmailServer();
        $emailSetup = (!is_null(EmailServerPeer::retrieveByPK($emailServerKey))) ?
            $emailServer->getEmailServer($emailServerKey, true) :
            $emailServer->getEmailServerDefault();

        return $emailSetup;
    }

    /**
     * Define the properties in the task related the action by email configuration
     *
     * @return void
     */
    private function defineTaskAbeProperties()
    {
        $actionEmailTable = new AbeConfiguration();
        $properties = $actionEmailTable->getTaskConfiguration($this->getCasePropertiesKey('PRO_UID'), $this->getTask());
        $this->setTaskAbeProperties($properties);
    }

    /**
     * Define the email from
     *
     * @param array $emailServerSetup
     *
     * @return void
     */
    private function defineEmailFrom(array $emailServerSetup)
    {
        $from = '';
        if (!$this->getItemAbeProperties('ABE_MAILSERVER_OR_MAILCURRENT') && !empty($this->getItemAbeProperties('ABE_TYPE'))) {
            if (!empty($this->getPreviousUser())) {
                $user = new Users();
                $userDetails = $user->loadDetails($this->getPreviousUser());
                $from = ($userDetails["USR_FULLNAME"] . ' <' . $userDetails["USR_EMAIL"] . '>');
            } else {
                global $RBAC;

                if ($RBAC != null && is_array($RBAC->aUserInfo['USER_INFO'])) {
                    $currentUser = $RBAC->aUserInfo['USER_INFO'];
                    $from = ($currentUser["USR_FIRSTNAME"] . ' ' . $currentUser["USR_LASTNAME"] . ' <' . $currentUser["USR_EMAIL"] . '>');
                } else {
                    $usersPeer = UsersPeer::retrieveByPK($this->getUser());
                    if (!empty($usersPeer)) {
                        $from = ($usersPeer->getUsrFirstname() . ' ' . $usersPeer->getUsrLastname() . ' <' . $usersPeer->getUsrEmail() . '>');
                    }
                }
            }
        }
        //Define the email from
        $emailFrom = G::buildFrom($emailServerSetup, $from);
        $this->setEmailFrom($emailFrom);
    }

    /**
     * Define the email from in a public way
     *
     * @param array $setup
     * @return void
     * @see ProcessMaker\BusinessModel\ActionsByEmail::forwardMail()
     */
    public function publicDefineEmailFrom($setup)
    {
        //Call the defineEmailFrom private method
        $this->defineEmailFrom($setup);
    }

    /**
     * Define the email to
     *
     * @param string $emailField
     * @param array $caseData
     *
     * @return void
     */
    private function defineEmailTo($emailField, array $caseData)
    {
        $emailField = str_replace($this->getPrefix(), '', $emailField);
        if ($emailField != '' && isset($caseData[$emailField])) {
            $emailTo = trim($caseData[$emailField]);
        } else {
            $userInstance = new Users();
            $emailTo = $userInstance->getAllInformation($this->getUser())['mail'];
        }

        $this->setEmailTo($emailTo);
    }

    /**
     * Define the reply to
     *
     * @param string $receiverUid
     *
     * @return void
     */
    private function defineReplyTo($receiverUid)
    {
        $emailServer = $this->getEmailServer($receiverUid);
        $replyTo = $emailServer["MESS_ACCOUNT"];

        $this->setReplyTo($replyTo);
    }

    /**
     * Define the subject
     *
     * @param string $subjectField
     * @param array $caseData
     *
     * @return void
     */
    private function defineSubject($subjectField, array $caseData)
    {
        $subject = G::replaceDataField($subjectField, $caseData, 'mysql', false);
        if (empty($subject)) {
            $subject = $this->getCasePropertiesKey('APP_TITLE');
        }

        $this->setSubject($subject);
    }

    /**
     * Validate and set the fields that we need for the action by email
     *
     * @param object $data
     */
    private function validateAndSetValues($data)
    {
        try {
            if (!is_object($data)) {
                throw new Exception('The parameter $data is null.');
            }
            // Validate the tas_uid
            if (empty($data->TAS_UID)) {
                throw new Exception('The parameter $data->TAS_UID is null or empty.');
            } else {
                $this->setTask($data->TAS_UID);
            }
            // Validate the app_uid
            if (empty($data->APP_UID)) {
                throw new Exception('The parameter $data->APP_UID is null or empty.');
            } else {
                $this->setAppUid($data->APP_UID);
            }
            // Validate the del_index
            if (empty($data->DEL_INDEX)) {
                throw new Exception('The parameter $data->DEL_INDEX is null or empty.');
            } elseif ($data->DEL_INDEX === 1) {
                // Processmaker log
                $context = Bootstrap::getDefaultContextLog();
                $context['delIndex'] = $data->DEL_INDEX;
                Bootstrap::registerMonolog('ActionByEmail', 250, 'Actions by email does not work in the initial task', $context);

                return;
            } else {
                $this->setIndex($data->DEL_INDEX);
            }
            // Validate the usr_uid
            if (empty($data->USR_UID)) {
                throw new Exception('The parameter $data->USR_UID is null or empty.');
            } else {
                $this->setUser($data->USR_UID);
            }
            // Define the previous user
            if (!empty($data->PREVIOUS_USR_UID)) {
                $this->setPreviousUser($data->PREVIOUS_USR_UID);
            }
        } catch (Exception $e) {
            $token = strtotime("now");
            PMException::registerErrorLog($e, $token);
            G::outRes(G::LoadTranslation("ID_EXCEPTION_LOG_INTERFAZ", [$token]));
            die;
        }
    }

    /**
     * Send Actions By Email.
     *
     * @global object $RBAC
     * @param object $data
     * @param array $dataAbe
     * @return type
     * @throws Exception
     *
     * @see AppDelegation->createAppDelegation()
     * @link https://wiki.processmaker.com/3.3/Actions_by_Email
     */
    public function sendActionsByEmail($data, array $dataAbe)
    {
        try {
            // Validations
            self::validateAndSetValues($data);

            $emailServerSetup = $this->getEmailServer($dataAbe['ABE_EMAIL_SERVER_UID']);
            if (!empty($emailServerSetup)) {
                $cases = new Cases();
                $caseFields = $cases->loadCase($this->getAppUid(), $this->getIndex());
                $this->setCaseProperties($caseFields);
                $this->defineTaskAbeProperties();
                $caseFields['APP_DATA']['PRO_ID'] = $this->getItemAbeProperties('PRO_ID');
                $caseFields['APP_DATA']['TAS_ID'] = $this->getItemAbeProperties('TAS_ID');
                if (!empty($this->getTaskAbeProperties())) {
                    $this->defineEmailTo($this->getItemAbeProperties('ABE_EMAIL_FIELD'), $caseFields['APP_DATA']);

                    if (!empty($this->getEmailTo())) {
                        $this->defineSubject($this->getItemAbeProperties('ABE_SUBJECT_FIELD'), $caseFields['APP_DATA']);

                        $request = [
                            'ABE_REQ_UID' => '',
                            'ABE_UID' => $this->getItemAbeProperties('ABE_UID'),
                            'APP_UID' => $this->getAppUid(),
                            'DEL_INDEX' => $this->getIndex(),
                            'ABE_REQ_SENT_TO' => $this->getEmailTo(),
                            'ABE_REQ_SUBJECT' => $this->getSubject(),
                            'ABE_REQ_BODY' => '',
                            'ABE_REQ_ANSWERED' => 0,
                            'ABE_REQ_STATUS' => 'PENDING'
                        ];
                        $this->setAbeRequest($request);
                        $this->registerRequest();

                        if (!empty($this->getItemAbeProperties('ABE_TYPE'))) {
                            // Email
                            $_SESSION['CURRENT_DYN_UID'] = $this->getItemAbeProperties('DYN_UID');
                            $__ABE__ = '';

                            switch ($this->getItemAbeProperties('ABE_TYPE')) {
                                case 'CUSTOM':
                                    $__ABE__ .= $this->getCustomTemplate();
                                    break;
                                case 'RESPONSE':
                                    $this->defineReplyTo($dataAbe['ABE_EMAIL_SERVER_RECEIVER_UID']);
                                    $__ABE__ .= $this->getResponseTemplate();
                                    break;
                                case 'LINK':
                                    $__ABE__ .= $this->getServicePathTemplate();
                                    break;
                                case 'FIELD':
                                    $__ABE__ .= $this->getFieldTemplate();
                                    break;
                            }
                            $__ABE__ = preg_replace('/\<img src=\"\/js\/maborak\/core\/images\/(.+?)\>/', '', $__ABE__);
                            $__ABE__ = preg_replace('/\<input\b[^>]*\/>/', '', $__ABE__);
                            $__ABE__ = preg_replace('/<select\b[^>]*>(.*?)<\/select>/is', "", $__ABE__);
                            $__ABE__ = preg_replace('/align=\"center\"/', '', $__ABE__);
                            $__ABE__ = preg_replace('/class="tableGrid_view" /', 'class="tableGrid_view" width="100%" ',
                                $__ABE__);
                            $caseFields['APP_DATA']['__ABE__'] = $__ABE__;

                            $this->defineEmailFrom($emailServerSetup);
                            $result = $this->abeSendMessage(
                                $this->getItemAbeProperties('ABE_TEMPLATE'),
                                $caseFields['APP_DATA'],
                                $emailServerSetup
                            );
                            $request = [];
                            $request['ABE_REQ_STATUS'] = ($result->status_code == 0 ? 'SENT' : 'ERROR');

                            $request['ABE_REQ_BODY'] = empty($result->getAppMessUid()) ? '' : AppMessage::getAppMsgBodyByKey($result->getAppMessUid());
                            $this->addItemAbeRequest($request);
                            $this->registerRequest();
                        }
                    }
                } else {
                    throw new Exception('Task does not have an action by email configuration.');
                }
            }
        } catch (Exception $error) {
            throw $error;
        }
    }

    /**
     * Get the html template for email response
     *
     * @return string
     */
    private function getResponseTemplate()
    {
        $noReply = $this->getReplyTo();
        $customGrid = unserialize($this->getItemAbeProperties('ABE_CUSTOM_GRID'));
        $field = new stdClass();
        $field->label = '';
        $html = '<div style="width: 100%"></div><strong>' . $field->label . '</strong><table align="left" border="0"><tr>';
        $html .= '<td><table align="left" cellpadding="2"><tr>';
        $index = 1;
        foreach ($customGrid as $key => $value) {
            // Get the subject
            $emailSubject = $this->getSubjectByResponse($value['abe_custom_label']);
            $emailBody = $this->getBodyByResponse($value['abe_custom_value']);
            // Define the html for the actions
            $html .= '<td align="center"><a style="' . $value['abe_custom_format'] . '" ';
            $html .= 'href="mailto:' . $noReply . '?subject=' . $emailSubject . '&body=' . $emailBody . '" target="_blank" >';
            $html .= $value['abe_custom_label'];
            $html .= '</a></td>' . (($index % 5 == 0) ? '</tr><tr>' : '  ');
            $index++;
        }
        $html .= '</tr></table></div>';

        return $html;
    }

    /**
     * Get the subject for response the action by email
     *
     * @param string $fieldLabel
     *
     * @return string
     */
    private function getSubjectByResponse($fieldLabel)
    {
        $subject = G::LoadTranslation('ID_CASE') . ' ' . $this->getCasePropertiesKey('APP_TITLE');
        $subject .= $this->delimiter . ' ' . $fieldLabel;

        return urlencode($subject);
    }

    /**
     * Get the body for response the action by email
     *
     * @param string $fieldValue
     *
     * @return string
     */
    private function getBodyByResponse($fieldValue)
    {
        $abeRequest = $this->getAbeRequest();
        $bodyToCrypt = [
            'workspace' => $this->getWorkspace(),
            'appUid' => $this->getAppUid(),
            'delIndex' => $this->getIndex(),
            'fieldValue' => $fieldValue,
            'ABE_REQ_UID' => $abeRequest['ABE_REQ_UID']
        ];
        $bodyToCrypt = G::json_encode($bodyToCrypt);

        $body = str_repeat(self::BODY_REPLY_LF, 4);
        $body .= '/' . str_repeat("=", 24) . self::BODY_REPLY_LF;
        $body .= G::LoadTranslation('ID_ABE_EMAIL_RESPONSE_BODY_NOTE') . self::BODY_REPLY_LF;
        $body .= '{' . Crypt::encryptString($bodyToCrypt) . '}' . self::BODY_REPLY_LF;
        $body .= str_repeat("=", 24) . '/';
        return $body;
    }

    /**
     * Get the html template for custom actions
     * @todo we need to revise this function
     *
     * @return string
     */
    private function getCustomTemplate()
    {
        $abeRequest = $this->getAbeRequest();
        $customGrid = unserialize($this->getItemAbeProperties('ABE_CUSTOM_GRID'));
        $variableService = new Variable();
        $variables = $variableService->doGetVariables($this->getCasePropertiesKey('PRO_UID'));
        $field = new stdClass();
        $field->label = '';
        $actionField = str_replace(
            $this->getPrefix(),
            '',
            $this->getItemAbeProperties('ABE_ACTION_FIELD')
        );

        $itemDynUid = $this->getItemAbeProperties('DYN_UID');
        $obj = new PmDynaform($itemDynUid);
        $this->addItemAbeProperties(['CURRENT_DYNAFORM' => $itemDynUid]);
        $file = $obj->printPmDynaformAbe($this->getTaskAbeProperties());
        $html = $file;
        $html .= '<div style="width: 100%"></div><strong>' . $field->label . '</strong><table align="left" border="0"><tr>';
        $index = 1;
        $html .= '<td><table align="left" cellpadding="2"><tr>';
        foreach ($customGrid as $key => $value) {
            $html .= '<td align="center"><a style="' . $value['abe_custom_format'] . '" ';
            $html .= 'href="' . urldecode(urlencode($this->getServicePath())) . '?ACTION=' . G::encrypt('processABE',
                    URL_KEY, true) . '&APP_UID=';
            $html .= G::encrypt($this->getAppUid(), URL_KEY,
                    true) . '&DEL_INDEX=' . G::encrypt($this->getindex(), URL_KEY);
            $html .= '&FIELD=' . G::encrypt($actionField, URL_KEY,
                    true) . '&VALUE=' . G::encrypt($value['abe_custom_value'], URL_KEY,
                    true);
            $html .= '&ABER=' . G::encrypt($abeRequest['ABE_REQ_UID'], URL_KEY,
                    true) . '" target="_blank" >' . $value['abe_custom_label'];
            $html .= '</a></td>' . (($index % 5 == 0) ? '</tr><tr>' : '  ');
            $index++;
        }
        $html .= '</tr></table></div>';

        return $html;
    }

    /**
     * Get the html template for link to fill a form
     * @todo we need to revise this function
     *
     * @return string
     */
    private function getServicePathTemplate()
    {
        $abeRequest = $this->getAbeRequest();
        $html = '<a href="' . $this->getServicePath() . 'DataForm?APP_UID=' . G::encrypt($this->getAppUid(),
                URL_KEY, true) . '&DEL_INDEX=' . G::encrypt($this->getIndex(), URL_KEY,
                true) . '&DYN_UID=' . G::encrypt($this->getItemAbeProperties('DYN_UID'), URL_KEY,
                true) . '&ABER=' . G::encrypt($abeRequest['ABE_REQ_UID'], URL_KEY,
                true) . '" target="_blank">' . G::LoadTranslation('ID_ACTIONS_BY_EMAIL_LINK_TO_FILL_A_FORM') . '</a>';

        return $html;
    }

    /**
     * Get the html template for use a field to generate actions links
     * @todo we need to revise this function
     *
     * @return string
     */
    private function getFieldTemplate()
    {
        $abeRequest = $this->getAbeRequest();
        $variableService = new Variable();
        $variables = $variableService->doGetVariables($this->getCasePropertiesKey('PRO_UID'));
        $field = new stdClass();
        $field->label = 'Test';
        $field->type = 'dropdown';
        $field->options = [];
        $field->value = '';
        $actionField = str_replace(
            $this->getPrefix(),
            '',
            $this->getItemAbeProperties('ABE_ACTION_FIELD')
        );
        $dynUid = $this->getItemAbeProperties('DYN_UID');
        $variables = G::json_decode($this->getItemAbeProperties('DYN_CONTENT'), true);
        if (isset($variables['items'][0]['items'])) {
            $fields = $variables['items'][0]['items'];
            foreach ($fields as $key => $value) {
                foreach ($value as $var) {
                    if (isset($var['variable'])) {
                        if ($var['variable'] == $actionField) {
                            $field->label = $var['label'];
                            $field->type = $var['type'];
                            $values = $var['options'];
                            foreach ($values as $val) {
                                $field->options[$val['value']] = $val['value'];
                            }
                        }
                    }
                }
            }
        }

        $obj = new PmDynaform($dynUid);
        $this->addItemAbeProperties(['CURRENT_DYNAFORM' => $dynUid]);
        $file = $obj->printPmDynaformAbe($this->getTaskAbeProperties());
        $html = $file;
        $html .= '<strong>' . $field->label . '</strong><br /><table align="left" border="0"><tr>';
        switch ($field->type) {
            case 'dropdown':
            case 'radio':
            case 'radiogroup':
                $index = 1;
                $html .= '<br /><td><table align="left" cellpadding="2"><tr>';
                foreach ($field->options as $optValue => $optName) {
                    $html .= '<td align="center"><a style="text-decoration: none; color: #000; background-color: #E5E5E5; ';
                    $html .= 'filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#EFEFEF, endColorstr=#BCBCBC); ';
                    $html .= 'background-image: -webkit-gradient(linear, left top, left bottom, from(#EFEFEF), #BCBCBC); ';
                    $html .= 'background-image: -webkit-linear-gradient(top, #EFEFEF, #BCBCBC); ';
                    $html .= 'background-image: -moz-linear-gradient(top, #EFEFEF, #BCBCBC); background-image: -ms-linear-gradient(top, #EFEFEF, #BCBCBC); ';
                    $html .= 'background-image: -o-linear-gradient(top, #EFEFEF, #BCBCBC); border: 1px solid #AAAAAA; ';
                    $html .= 'border-radius: 4px; -moz-border-radius: 4px; -webkit-border-radius: 4px; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2); ';
                    $html .= 'font-family: Arial,serif; font-size: 9pt; font-weight: 400; line-height: 14px; margin: 2px 0; padding: 2px 7px; ';
                    $html .= 'text-decoration: none; text-transform: capitalize;" href="' . urldecode(urlencode($this->getServicePath())) . '?ACTION=' . G::encrypt('processABE',
                            URL_KEY, true) . '&APP_UID=';
                    $html .= G::encrypt($this->getAppUid(), URL_KEY,
                            true) . '&DEL_INDEX=' . G::encrypt($this->getIndex(), URL_KEY,
                            true);
                    $html .= '&FIELD=' . G::encrypt($actionField, URL_KEY,
                            true) . '&VALUE=' . G::encrypt($optValue, URL_KEY, true);
                    $html .= '&ABER=' . G::encrypt($abeRequest['ABE_REQ_UID'], URL_KEY,
                            true) . '" target="_blank" >' . $optName;
                    $html .= '</a></td>' . (($index % 5 == 0) ? '</tr><tr>' : '  ');
                    $index++;
                }

                $html .= '</tr></table></td>';
                break;
            case 'yesno':
                $html .= '<td align="center"><a href="' . $this->getServicePath() . '?ACTION=' . G::encrypt('processABE',
                        URL_KEY, true) . '&APP_UID=' . urlencode(G::encrypt($this->getAppUid(),
                        URL_KEY,
                        true)) . '&DEL_INDEX=' . urlencode(G::encrypt($this->getIndex(),
                        URL_KEY, true)) . '&FIELD=' . urlencode(G::encrypt($actionField,
                        URL_KEY, true)) . '&VALUE=' . urlencode(G::encrypt(1, URL_KEY,
                        true)) . '&ABER=' . urlencode(G::encrypt($abeRequest['ABE_REQ_UID'],
                        URL_KEY, true,
                        true)) . '" target="_blank">' . G::LoadTranslation('ID_YES_VALUE') . '</a></td>';
                $html .= '<td align="center"><a href="' . $this->getServicePath() . '?ACTION=' . G::encrypt('processABE',
                        URL_KEY, true) . '&APP_UID=' . urlencode(G::encrypt($this->getAppUid(),
                        URL_KEY,
                        true)) . '&DEL_INDEX=' . urlencode(G::encrypt($this->getIndex(),
                        URL_KEY, true)) . '&FIELD=' . urlencode(G::encrypt($actionField,
                        URL_KEY, true)) . '&VALUE=' . urlencode(G::encrypt(0, URL_KEY,
                        true)) . '&ABER=' . urlencode(G::encrypt($abeRequest['ABE_REQ_UID'],
                        URL_KEY, true,
                        true)) . '" target="_blank">' . G::LoadTranslation('ID_NO_VALUE') . '</a></td>';
                break;
            case 'checkbox':
                $html .= '<td align="center"><a href="' . $this->getServicePath() . '?ACTION=' . G::encrypt('processABE',
                        URL_KEY, true) . '&APP_UID=' . G::encrypt($this->getAppUid(), URL_KEY,
                        true) . '&DEL_INDEX=' . G::encrypt($this->getIndex(), URL_KEY,
                        true) . '&FIELD=' . G::encrypt($actionField, URL_KEY,
                        true) . '&VALUE=' . G::encrypt($field->value, URL_KEY,
                        true) . '&ABER=' . G::encrypt($abeRequest['ABE_REQ_UID'], URL_KEY,
                        true) . '" target="_blank">Check</a></td>';
                $html .= '<td align="center"><a href="' . $this->getServicePath() . '?ACTION=' . G::encrypt('processABE',
                        URL_KEY, true) . '&APP_UID=' . G::encrypt($this->getAppUid(), URL_KEY,
                        true) . '&DEL_INDEX=' . G::encrypt($this->getIndex(), URL_KEY,
                        true) . '&FIELD=' . G::encrypt($actionField, URL_KEY,
                        true) . '&VALUE=' . G::encrypt($field->value, URL_KEY,
                        true) . '&ABER=' . G::encrypt($abeRequest['ABE_REQ_UID'], URL_KEY,
                        true) . '" target="_blank">Uncheck</a></td>';
                break;
        }
        $html .= '</tr></table>';

        return $html;
    }

    /**
     * Register the request in the table ABE_REQUEST
     *
     * @return void
     * @throws Exception
     */
    private function registerRequest()
    {
        try {
            $requestInstance = new AbeRequests();
            $abeRequest['ABE_REQ_UID'] = $requestInstance->createOrUpdate($this->getAbeRequest());
            $this->setAbeRequest($abeRequest);
        } catch (Exception $error) {
            throw $error;
        }
    }

    /**
     * Send the message
     *
     * @param string $template
     * @param array $caseData
     * @param array $configEmail
     *
     * @return object
     * @throws Exception
     */
    private function abeSendMessage($template, array $caseData, array $configEmail)
    {
        try {
            $wsBaseInstance = new WsBase();
            $result = $wsBaseInstance->sendMessage(
                $this->getAppUid(),
                $this->getEmailFrom(),
                $this->getEmailTo(),
                '',
                '',
                $this->getSubject(),
                $template,
                $caseData,
                null,
                true,
                $this->getIndex(),
                $configEmail,
                0,
                WsBase::MESSAGE_TYPE_ACTIONS_BY_EMAIL
            );

            return $result;
        } catch (Exception $error) {
            throw $error;
        }
    }
}
