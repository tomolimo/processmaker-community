<?php

namespace ProcessMaker\BusinessModel;

use AbeConfiguration;
use AbeConfigurationPeer;
use AbeRequests;
use AbeRequestsPeer;
use AbeResponsesPeer;
use ActionsByEmailCoreClass;
use AppDelegation;
use AppDelegationPeer;
use AppMessage;
use Criteria;
use DynaformPeer;
use EmailServerPeer;
use Exception;
use G;
use PmDynaform;
use PMLicensedFeatures;
use ProcessMaker\Core\System;
use ProcessMaker\Model\AbeConfiguration as AbeConfigurationModel;
use ProcessMaker\Model\EmailServerModel;
use ProcessMaker\Model\Task;
use ProcessMaker\Plugins\PluginRegistry;
use Publisher;
use ResultSet;
use SpoolRun;
use stdClass;
use Users as ClassUsers;
use WsBase;

/**
 * Description of ActionsByEmailService
 *
 */
class ActionsByEmail
{

    public function saveConfiguration($params)
    {
        if (PMLicensedFeatures
                ::getSingleton()
                ->verifyfeature('zLhSk5TeEQrNFI2RXFEVktyUGpnczV1WEJNWVp6cjYxbTU3R29mVXVZNWhZQT0=')) {
            $feature = $params['ActionsByEmail'];
            switch ($feature['type']) {
                case 'configuration':
                    require_once 'classes/model/AbeConfiguration.php';
                    $abeConfigurationInstance = new AbeConfiguration();

                    if (isset($feature['fields']['ABE_CASE_NOTE_IN_RESPONSE'])) {
                        $arrayAux = json_decode($feature['fields']['ABE_CASE_NOTE_IN_RESPONSE']);

                        $feature['fields']['ABE_CASE_NOTE_IN_RESPONSE'] = (int)((!empty($arrayAux))? array_shift($arrayAux) : 0);
                    }

                    if (isset($feature['fields']['ABE_FORCE_LOGIN'])) {
                        $arrayAux = json_decode($feature['fields']['ABE_FORCE_LOGIN']);

                        $feature['fields']['ABE_FORCE_LOGIN'] = (int)((!empty($arrayAux))? array_shift($arrayAux) : 0);
                    }

                    $abeConfigurationInstance->createOrUpdate($feature['fields']);
                    break;
                default:
                    break;
            }
        }
    }

    public function loadConfiguration($params)
    {
        if ($params['type'] != 'activity'
            || !PMLicensedFeatures
                ::getSingleton()
                ->verifyfeature('zLhSk5TeEQrNFI2RXFEVktyUGpnczV1WEJNWVp6cjYxbTU3R29mVXVZNWhZQT0='))
        {
            return false;
        }
        require_once 'classes/model/AbeConfiguration.php';

        $criteria = new Criteria();
        $criteria->add(AbeConfigurationPeer::PRO_UID, $params['PRO_UID']);
        $criteria->add(AbeConfigurationPeer::TAS_UID, $params['TAS_UID']);
        $result = AbeConfigurationPeer::doSelectRS($criteria);
        $result->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $result->next();
        $configuration = array();
        if ($configuration = $result->getRow()) {
            $configuration['ABE_UID'] = $configuration['ABE_UID'];
            $configuration['ABE_TYPE'] = $configuration['ABE_TYPE'];
            $configuration['DYN_UID'] = $configuration['DYN_UID'];
            $configuration['ABE_TEMPLATE'] = $configuration['ABE_TEMPLATE'];
            $configuration['ABE_SUBJECT_FIELD'] = $configuration['ABE_SUBJECT_FIELD'];
            $configuration['ABE_EMAIL_FIELD'] = $configuration['ABE_EMAIL_FIELD'];
            $configuration['ABE_ACTION_FIELD'] = $configuration['ABE_ACTION_FIELD'];
            $configuration['ABE_MAILSERVER_OR_MAILCURRENT'] = $configuration['ABE_MAILSERVER_OR_MAILCURRENT'];
            $configuration['ABE_CASE_NOTE_IN_RESPONSE'] = $configuration['ABE_CASE_NOTE_IN_RESPONSE'] ? '["1"]' : '[]';
            $configuration['ABE_FORCE_LOGIN'] = ($configuration['ABE_FORCE_LOGIN'])? '["1"]' : '[]';
            $configuration['ABE_CUSTOM_GRID'] = unserialize($configuration['ABE_CUSTOM_GRID']);
        }
        $configuration['feature'] = 'ActionsByEmail';
        $configuration['prefix'] = 'abe';
        $configuration['PRO_UID'] = $params['PRO_UID'];
        $configuration['TAS_UID'] = $params['TAS_UID'];
        $configuration['SYS_LANG'] = SYS_LANG;
        return $configuration;
    }

    public function editTemplate(array $arrayData)
    {
        //Action Validations
        if (!isset($arrayData['TEMPLATE'])) {
            $arrayData['TEMPLATE'] = '';
        }

        if ($arrayData['TEMPLATE'] == '') {
            throw new Exception(G::LoadTranslation('ID_TEMPLATE_PARAMETER_EMPTY'));
        }

        $data = array(
            'CONTENT' => file_get_contents(
                PATH_DATA_MAILTEMPLATES . $arrayData['PRO_UID'] . PATH_SEP . $arrayData['TEMPLATE']
            ),
            'TEMPLATE' => $arrayData['TEMPLATE'],
        );

        global $G_PUBLISH;

        $G_PUBLISH = new Publisher();
        $G_PUBLISH->AddContent('xmlform', 'xmlform', 'actionsByEmail/actionsByEmail_FileEdit', '', $data);

        G::RenderPage('publish', 'raw');
        die();
    }

    public function updateTemplate(array $arrayData)
    {
        //Action Validations
        if (!isset($arrayData['TEMPLATE'])) {
            $arrayData['TEMPLATE'] = '';
        }

        if (!isset($arrayData['CONTENT'])) {
            $arrayData['CONTENT'] = '';
        }

        if ($arrayData['TEMPLATE'] == '') {
            throw new Exception(G::LoadTranslation('ID_TEMPLATE_PARAMETER_EMPTY'));
        }

        $templateFile = fopen(PATH_DATA_MAILTEMPLATES . $arrayData['PRO_UID'] . PATH_SEP . $arrayData['TEMPLATE'], 'w');
        $content = stripslashes($arrayData['CONTENT']);
        $content = str_replace('@amp@', '&', $content);
        $content = base64_decode($content);

        fwrite($templateFile, $content);
        fclose($templateFile);
    }

    public function loadFields(array $arrayData)
    {
        if (!isset($arrayData['DYN_UID'])) {
            $arrayData['DYN_UID'] = '';
        }

        if (!isset($arrayData['PRO_UID'])) {
            $arrayData['PRO_UID'] = '';
        }

        $response = new stdClass();
        $response->emailFields = [];
        $response->actionFields = [];

        if ($arrayData['PRO_UID'] != '' && $arrayData['DYN_UID']) {
            $dynaform = new Form($arrayData['PRO_UID'] . PATH_SEP . $arrayData['DYN_UID'], PATH_DYNAFORM, SYS_LANG, false);

            foreach ($dynaform->fields as $fieldName => $data) {
                switch ($data->type) {
                    case 'text':
                    case 'suggest':
                    case 'hidden':
                    case 'textarea':
                        $response->emailFields[] = array('value' => $data->name, 'label' => $data->label . ' (@@' . $data->name . ')');
                        break;
                    case 'dropdown':
                    case 'radiogroup':
                    case 'yesno':
                    case 'checkbox':
                        $response->actionFields[] = array('value' => $data->name, 'label' => $data->label . ' (@@' . $data->name . ')');
                        break;
                }
            }
        }

        //Return
        return $response;
    }

    public function saveConfiguration2(array $arrayData)
    {
        if (!isset($arrayData['ABE_UID'])) {
            $arrayData['ABE_UID'] = '';
        }

        if (!isset($arrayData['PRO_UID'])) {
            $arrayData['PRO_UID'] = '';
        }

        if (!isset($arrayData['TAS_UID'])) {
            $arrayData['TAS_UID'] = '';
        }

        if (!isset($arrayData['ABE_TYPE'])) {
            $arrayData['ABE_TYPE'] = '';
        }

        if (!isset($arrayData['ABE_TEMPLATE'])) {
            $arrayData['ABE_TEMPLATE'] = '';
        }

        if (!isset($arrayData['DYN_UID'])) {
            $arrayData['DYN_UID'] = '';
        }

        if (!isset($arrayData['ABE_EMAIL_FIELD'])) {
            $arrayData['ABE_EMAIL_FIELD'] = '';
        }

        if (!isset($arrayData['ABE_ACTION_FIELD'])) {
            $arrayData['ABE_ACTION_FIELD'] = '';
        }

        if (!isset($arrayData['ABE_CASE_NOTE_IN_RESPONSE'])) {
            $arrayData['ABE_CASE_NOTE_IN_RESPONSE'] = 0;
        }

        if ($arrayData['PRO_UID'] == '') {
            throw new Exception(G::LoadTranslation('ID_PRO_UID_PARAMETER_IS_EMPTY'));
        }

        if ($arrayData['TAS_UID'] == '') {
            throw new Exception(G::LoadTranslation('ID_TAS_UID_PARAMETER_IS_EMPTY'));
        }

        $abeConfigurationInstance = new AbeConfiguration();

        $response = new stdClass();
        if ($arrayData['ABE_TYPE'] != '') {
            if ($arrayData['DYN_UID'] == '') {
                throw new Exception(G::LoadTranslation('ID_DYN_UID_PARAMETER_IS_EMPTY'));
            }

            try {
                $response->ABE_UID = $abeConfigurationInstance->createOrUpdate($arrayData);
            } catch (Exception $error) {
                throw $error;
            }
        } else {
            try {
                $abeConfigurationInstance->deleteByTasUid($arrayData['TAS_UID']);
                $response->ABE_UID = '';
            } catch (Exception $error) {
                throw $error;
            }
        }

        //Return
        return $response;
    }

    /**
     * Get the information for the log.
     *
     * @param array $arrayData
     * @return array
     *
     * @see ProcessMaker\Services\Api\ActionsByEmail->loadActionByEmail()
     * @see workflow/engine/methods/actionsByEmail/actionsByEmailAjax.php
     * @link https://wiki.processmaker.com/3.3/Actions_by_Email
     */
    public function loadActionByEmail(array $arrayData)
    {
        //Get the total
        $criteria = new Criteria();
        $criteria->addSelectColumn('COUNT(*)');
        $criteria->addJoin(AbeConfigurationPeer::ABE_UID, AbeRequestsPeer::ABE_UID);
        $criteria->addJoin(AppDelegationPeer::APP_UID, AbeRequestsPeer::APP_UID);
        $criteria->addJoin(AppDelegationPeer::DEL_INDEX, AbeRequestsPeer::DEL_INDEX);
        $result = AbeConfigurationPeer::doSelectRS($criteria);
        $result->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $result->next();
        $totalCount = $result->getRow();
        $totalCount = $totalCount['COUNT(*)'];

        $criteria = new Criteria();
        $criteria->addSelectColumn(AbeConfigurationPeer::ABE_UID);
        $criteria->addSelectColumn(AbeConfigurationPeer::PRO_UID);
        $criteria->addSelectColumn(AbeConfigurationPeer::TAS_UID);
        $criteria->addSelectColumn(AbeConfigurationPeer::ABE_UPDATE_DATE);
        $criteria->addSelectColumn(AbeConfigurationPeer::ABE_TEMPLATE);
        $criteria->addSelectColumn(AbeConfigurationPeer::ABE_ACTION_FIELD);
        $criteria->addSelectColumn(AbeConfigurationPeer::DYN_UID);
        $criteria->addSelectColumn(AbeConfigurationPeer::ABE_MAILSERVER_OR_MAILCURRENT);
        $criteria->addSelectColumn(AbeConfigurationPeer::ABE_EMAIL_SERVER_UID);
        $criteria->addSelectColumn(AbeRequestsPeer::ABE_REQ_UID);
        $criteria->addSelectColumn(AbeRequestsPeer::APP_UID);
        $criteria->addSelectColumn(AbeRequestsPeer::DEL_INDEX);
        $criteria->addSelectColumn(AbeRequestsPeer::ABE_REQ_SENT_TO);
        $criteria->addSelectColumn(AbeRequestsPeer::ABE_REQ_STATUS);
        $criteria->addSelectColumn(AbeRequestsPeer::ABE_REQ_SUBJECT);
        $criteria->addSelectColumn(AbeRequestsPeer::ABE_REQ_ANSWERED);
        $criteria->addSelectColumn(AbeRequestsPeer::ABE_REQ_BODY);
        $criteria->addSelectColumn(AbeRequestsPeer::ABE_REQ_DATE);
        $criteria->addSelectColumn(AppDelegationPeer::APP_NUMBER);
        $criteria->addSelectColumn(AppDelegationPeer::DEL_PREVIOUS);
        $criteria->addSelectColumn(AppDelegationPeer::USR_UID);
        $criteria->addJoin(AbeConfigurationPeer::ABE_UID, AbeRequestsPeer::ABE_UID);
        $criteria->addJoin(AppDelegationPeer::APP_UID, AbeRequestsPeer::APP_UID);
        $criteria->addJoin(AppDelegationPeer::DEL_INDEX, AbeRequestsPeer::DEL_INDEX);
        $criteria->addDescendingOrderByColumn(AbeRequestsPeer::ABE_REQ_DATE);
        $criteria->setLimit($arrayData['limit']);
        $criteria->setOffset($arrayData['start']);
        $result = AbeConfigurationPeer::doSelectRS($criteria);
        $result->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $data = [];
        $index = 0;
        while ($result->next()) {
            $row = $result->getRow();
            $row['ABE_REQ_STATUS'] = G::LoadTranslation('ID_MAIL_STATUS_' . $row['ABE_REQ_STATUS']);
            $data[] = $row;
            //Get the response
            $abe = new AbeRequests();
            $dataRes = $abe->load($data[$index]['ABE_REQ_UID']);
            $data[$index]['ABE_RES_UID'] = !empty($dataRes['ABE_RES_UID']) ? $dataRes['ABE_RES_UID'] : '';
            $data[$index]['ABE_RES_CLIENT_IP'] = !empty($dataRes['ABE_RES_CLIENT_IP']) ? $dataRes['ABE_RES_CLIENT_IP'] : '';
            $data[$index]['ABE_RES_DATA'] = !empty($dataRes['ABE_RES_DATA']) ? $dataRes['ABE_RES_DATA'] : '';
            $data[$index]['ABE_RES_STATUS'] = !empty($dataRes['ABE_RES_STATUS']) ? $dataRes['ABE_RES_STATUS'] : '';
            $data[$index]['ABE_RES_MESSAGE'] = !empty($dataRes['ABE_RES_UID']) ? $dataRes['ABE_RES_MESSAGE'] : '';

            //Get the previous user
            $appDelegation = new AppDelegation();
            $usrUid = $appDelegation->getUserAssignedInThread($data[$index]['APP_UID'], $data[$index]['DEL_PREVIOUS']);
            //This value can be empty when the previous task is: 'Script Task', 'Timer Event' or other without user.
            if (!empty($usrUid)) {
                $users = new ClassUsers();
                $dataRes = $users->load($usrUid);
                if (!empty($dataRes)) {
                    $data[$index]['USER'] = $dataRes['USR_FIRSTNAME'] . ' ' . $dataRes['USR_LASTNAME'];
                } else {
                    $data[$index]['USER'] = '';
                }
            } else {
                $data[$index]['USER'] = '';
                if ($data[$index]['ABE_MAILSERVER_OR_MAILCURRENT'] == 1) {
                    $emailServer = new EmailServer();
                    if (!empty($data[$index]['ABE_EMAIL_SERVER_UID'])) {
                        $dataEmailServer = $emailServer->getEmailServer($data[$index]['ABE_EMAIL_SERVER_UID']);
                    } else {
                        $emailServerModel = new EmailServerModel();
                        $emailServerDefault = $emailServerModel->getEmailServerDefault();
                        if (isset($emailServerDefault['MESS_UID'])) {
                            $dataEmailServer = $emailServer->getEmailServer($emailServerDefault['MESS_UID']);
                        }
                    }
                    $data[$index]['USER'] = isset($dataEmailServer['MESS_FROM_NAME']) ? $dataEmailServer['MESS_FROM_NAME'] : '';
                }
                if ($data[$index]['ABE_MAILSERVER_OR_MAILCURRENT'] == 0) {
                    $delegation = new AppDelegation();
                    $previousTask = $delegation->getPreviousDelegationValidTask($data[$index]['APP_UID'], $data[$index]['DEL_INDEX']);
                    if (in_array($previousTask['TAS_TYPE'], Task::DUMMY_TASKS) || in_array($previousTask['TAS_TYPE'], Task::$typesRunAutomatically)) {
                        $res = Task::getTask($previousTask['TAS_ID']);
                        $data[$index]['USER'] = $res->TAS_TITLE . ' (' . $previousTask['TAS_TYPE'] . ')';
                    }
                }
            }

            $data[$index]['ABE_REQ_ANSWERED'] = ($data[$index]['ABE_REQ_ANSWERED'] == 1) ? G::LoadTranslation('ID_YES') : G::LoadTranslation('ID_NO');
            $index++;
        }

        $response = [];
        $response['totalCount'] = $totalCount;
        $response['data'] = $data;

        //Return
        return $response;
    }

    /**
     * Forward the Mail
     *
     * @param array $arrayData
     *
     * @return string $message
     * @throws Exception
     *
     * @see workflow/engine/methods/actionsByEmail/actionsByEmailAjax.php
     * @see \ProcessMaker\Services\Api\ActionsByEmail::forwardMail()
     */
    public function forwardMail(array $arrayData)
    {
        if (!isset($arrayData['REQ_UID'])) {
            $arrayData['REQ_UID'] = '';
        }
        $dataRes = AbeConfigurationModel::getAbeRequest($arrayData['REQ_UID']);
        if (!empty($dataRes)) {
            if (is_null($dataRes['DEL_FINISH_DATE'])) {
                $emailServer = new EmailServerModel();
                $criteria = $emailServer->getEmailServer($dataRes['ABE_EMAIL_SERVER_UID']);
                $setup = !empty($criteria) ? $criteria : $emailServer->getEmailServerDefault();
                $setup['SMTPSecure'] = $setup['SMTPSECURE'];
                unset($setup['SMTPSECURE']);
                $spool = new SpoolRun();
                $spool->setConfig($setup);
                $abeCore = new ActionsByEmailCoreClass();
                $abeCore->setTaskAbeProperties([
                    'ABE_MAILSERVER_OR_MAILCURRENT' => $dataRes['ABE_MAILSERVER_OR_MAILCURRENT'],
                    'ABE_TYPE' => $dataRes['ABE_TYPE']
                ]);
                $abeCore->publicDefineEmailFrom($setup);
                $messageArray = AppMessage::buildMessageRow(
                    '',
                    $dataRes['APP_UID'],
                    $dataRes['DEL_INDEX'],
                    WsBase::MESSAGE_TYPE_ACTIONS_BY_EMAIL,
                    $dataRes['ABE_REQ_SUBJECT'],
                    $abeCore->getEmailFrom(),
                    $dataRes['ABE_REQ_SENT_TO'],
                    $dataRes['ABE_REQ_BODY'],
                    '',
                    '',
                    '',
                    '',
                    'pending',
                    1,
                    '',
                    true,
                    isset($dataRes['APP_NUMBER']) ? $dataRes['APP_NUMBER'] : 0,
                    $dataRes['PRO_ID'],
                    $dataRes['TAS_ID']
                );

                $spool->create($messageArray);

                if ($spool->sendMail()) {
                    $dataRes['ABE_REQ_STATUS'] = 'SENT';
                    $message = G::LoadTranslation('ID_EMAIL_RESENT_TO') . ': ' . $dataRes['ABE_REQ_SENT_TO'];
                } else {
                    $dataRes['ABE_REQ_STATUS'] = 'ERROR';
                    $message = G::LoadTranslation('ID_THERE_PROBLEM_SENDING_EMAIL') . ': ' . $dataRes['ABE_REQ_SENT_TO'] . ', ' . G::LoadTranslation('ID_PLEASE_TRY_LATER');
                }

                try {
                    $abeRequestsInstance = new AbeRequests();
                    $abeRequestsInstance->createOrUpdate($dataRes);
                } catch (Exception $error) {
                    throw $error;
                }
            } else {
                $message = G::LoadTranslation('ID_UNABLE_TO_SEND_EMAIL');
            }
        } else {
            $message = G::LoadTranslation('ID_UNEXPECTED_ERROR_OCCURRED_PLEASE');
        }

        //Return
        return $message;
    }

    /**
     * Get the decision from Actions By Email and check if is Bpmn Process.
     * @param array $arrayData
     *
     * @return string $message
     *
     * @see workflow/engine/methods/actionsByEmail/actionsByEmailAjax.php
     * @see ProcessMaker\Services\Api\ActionsByEmail->viewForm()
     * @link https://wiki.processmaker.com/3.3/Actions_by_Email#Actions_by_Email_Log
     */
    public function viewForm(array $arrayData)
    {
        //coment
        if (!isset($arrayData['REQ_UID'])) {
            $arrayData['REQ_UID'] = '';
        }

        $criteria = new Criteria();
        $criteria->addSelectColumn(AbeConfigurationPeer::ABE_UID);
        $criteria->addSelectColumn(AbeConfigurationPeer::PRO_UID);
        $criteria->addSelectColumn(AbeConfigurationPeer::TAS_UID);
        $criteria->addSelectColumn(AbeConfigurationPeer::DYN_UID);
        $criteria->addSelectColumn(AbeConfigurationPeer::ABE_ACTION_FIELD);
        $criteria->addSelectColumn(AbeConfigurationPeer::ABE_TYPE);

        $criteria->addSelectColumn(AbeRequestsPeer::ABE_REQ_UID);
        $criteria->addSelectColumn(AbeRequestsPeer::APP_UID);
        $criteria->addSelectColumn(AbeRequestsPeer::DEL_INDEX);

        $criteria->addSelectColumn(AbeResponsesPeer::ABE_RES_UID);
        $criteria->addSelectColumn(AbeResponsesPeer::ABE_RES_DATA);

        $criteria->add(AbeRequestsPeer::ABE_REQ_UID, $arrayData['REQ_UID']);
        $criteria->addJoin(AbeRequestsPeer::ABE_UID, AbeConfigurationPeer::ABE_UID);
        $criteria->addJoin(AbeResponsesPeer::ABE_REQ_UID, AbeRequestsPeer::ABE_REQ_UID);
        $resultRes = AbeRequestsPeer::doSelectRS($criteria);
        $resultRes->setFetchmode(ResultSet::FETCHMODE_ASSOC);

        $resultRes->next();
        $dataRes = [];
        $message = G::LoadTranslation('ID_USER_NOT_RESPONDED_REQUEST');
        if ($dataRes = $resultRes->getRow()) {
            $_SESSION['CURRENT_DYN_UID'] = trim($dataRes['DYN_UID']);

            $process = new \Process();
            $isBpmn = $process->isBpmnProcess($dataRes['PRO_UID']);
            if ($isBpmn) {
                if ($dataRes['ABE_TYPE'] === 'FIELD') {
                    $message = $this->viewFormBpmn($dataRes);
                } else {
                    $message = G::LoadTranslation('ID_CASE_RESPONSE_NOT_AVAILABLE');
                }
            } else {
                $message = $this->viewFormClassic($dataRes); //to do, review this function
            }
        }

        return $message;
    }

    /**
     * Get the decision from Actions By Email by Classic dynaform
     * @param array $dataRes
     *
     * @return string $message
     */
    public function viewFormClassic(array $dataRes)
    {
        $dynaform = new \Form($dataRes['PRO_UID'] . PATH_SEP . trim($dataRes['DYN_UID']), PATH_DYNAFORM, SYS_LANG, false);
        $dynaform->mode = 'view';

        if ($dataRes['ABE_RES_DATA'] != '') {
            $value = unserialize($dataRes['ABE_RES_DATA']);

            if (is_array($value)) {
                $dynaform->values = $value;

                foreach ($dynaform->fields as $fieldName => $field) {
                    if ($field->type == 'submit') {
                        unset($dynaform->fields[$fieldName]);
                    }
                }

                $message = $dynaform->render(PATH_CORE . 'templates/xmlform.html', $scriptCode);
            } else {
                $response = $dynaform->render(PATH_CORE . 'templates/xmlform.html', $scriptCode);

                $field = $dynaform->fields[$dataRes['ABE_ACTION_FIELD']];
                $message = '<b>Type:   </b>' . $field->type . '<br>';

                switch ($field->type) {
                    case 'dropdown':
                    case 'radiogroup':
                        $message .=$field->label . ' - ';
                        $message .= $field->options[$value];
                        break;
                    case 'yesno':
                        $message .= '<b>' . $field->label . ' </b>- ';
                        $message .= ($value == 1) ? G::loadTranslation('ID_YES') : G::loadTranslation('ID_NO');
                        break;
                    case 'checkbox':
                        $message .= '<b>' . $field->label . '</b> - ';
                        $message .= ($value == 'On') ? G::loadTranslation('ID_CHECK') : G::loadTranslation('ID_UNCHECK');
                        break;
                }
            }
        }

        //Return
        return $message;
    }

    /**
     * Get the decision from Actions By Email by BPMN dynaform.
     *
     * @param array $dataRes
     * @return string
     *
     * @see ActionsByEmail->viewForm()
     * @link https://wiki.processmaker.com/3.3/Actions_by_Email
     */
    public function viewFormBpmn(array $dataRes)
    {
        $_SESSION['CURRENT_DYN_UID'] = trim($dataRes['DYN_UID']);
        $configuration['DYN_UID'] = trim($dataRes['DYN_UID']);
        $configuration['CURRENT_DYNAFORM'] = trim($dataRes['DYN_UID']);
        $configuration['PRO_UID'] = trim($dataRes['PRO_UID']);

        $criteriaD = new Criteria();
        $criteriaD->addSelectColumn(DynaformPeer::DYN_CONTENT);
        $criteriaD->addSelectColumn(DynaformPeer::PRO_UID);
        $criteriaD->add(DynaformPeer::DYN_UID, trim($dataRes['DYN_UID']));
        $resultD = DynaformPeer::doSelectRS($criteriaD);
        $resultD->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $resultD->next();
        $configuration = $resultD->getRow();

        $field = new stdClass();
        $field->type = '';
        $field->label = '';
        $field->options = [];

        $obj = new PmDynaform($configuration);

        $message = G::LoadTranslation('ID_CASE_RESPONSE_NOT_AVAILABLE');
        if ($dataRes['ABE_RES_DATA'] !== '') {
            $value = unserialize($dataRes['ABE_RES_DATA']);
            $actionField = str_replace(['@@', '@#', '@=', '@%', '@?', '@$'], '', $dataRes['ABE_ACTION_FIELD']);
            $variables = G::json_decode($configuration['DYN_CONTENT'], true);
            if (is_array($value)) {
                if (isset($variables['items'][0]['items'])) {
                    $fields = $variables['items'][0]['items'];
                }
            } else {
                if (isset($variables['items'][0]['items'])) {
                    $fields = $variables['items'][0]['items'];
                    foreach ($fields as $key => $row) {
                        foreach ($row as $var) {
                            if (isset($var['variable'])) {
                                if ($var['variable'] === $actionField) {
                                    $field->label = isset($var['label']) ? $var['label'] : '';
                                    $field->type = isset($var['type']) ? $var['type'] : '';
                                    $values = $var['options'];
                                    foreach ($values as $val) {
                                        $field->options[$val['value']] = $val['value'];
                                    }
                                }
                            }
                        }
                    }
                }

                switch ($field->type) {
                    case 'dropdown':
                    case 'radiogroup':
                    case 'radio':
                        if (!empty($field->options[$value])) {
                            $message = $field->label . ': ';
                            $message .= $field->options[$value];
                        }
                        break;
                    /**
                     * 'yesno' is deprecated in version ProcessMaker 3.x.x.
                     * @deprecated
                     */
                    case 'yesno':
                        $message = $field->label . ': ';
                        $message .= $value == 1 ? G::LoadTranslation('ID_YES') : G::LoadTranslation('ID_NO');
                        break;
                    case 'checkgroup':
                    case 'checkbox':
                        $message = $field->label . ': ';
                        if (!empty($value)) {
                            /**
                             * Value 'On' is deprecated in version ProcessMaker 3.x.x.
                             * now return '1'.
                             * @deprecated
                             */
                            $message .= ($value == 'On' || $value == '1') ? G::LoadTranslation('ID_CHECK') : G::LoadTranslation('ID_UNCHECK');
                        }
                        break;
                }
            }
        }
        return $message;
    }

    /**
     * Verify login
     *
     * @param string $applicationUid Unique id of Case
     * @param int    $delIndex       Delegation index
     *
     * @return void
     */
    public function verifyLogin($applicationUid, $delIndex)
    {
        try {
            //Verify data and Set variables
            $case = new \ProcessMaker\BusinessModel\Cases();

            $arrayAppDelegationData = $case->getAppDelegationRecordByPk(
                $applicationUid, $delIndex, ['$applicationUid' => '$applicationUid', '$delIndex' => '$delIndex']
            );

            //Verify login
            $criteria = new Criteria('workflow');

            $criteria->add(AbeConfigurationPeer::PRO_UID, $arrayAppDelegationData['PRO_UID'], Criteria::EQUAL);
            $criteria->add(AbeConfigurationPeer::TAS_UID, $arrayAppDelegationData['TAS_UID'], Criteria::EQUAL);

            $rsCriteria = AbeConfigurationPeer::doSelectRS($criteria);
            $rsCriteria->setFetchmode(ResultSet::FETCHMODE_ASSOC);

            if ($rsCriteria->next()) {
                $record = $rsCriteria->getRow();

                if ($record['ABE_FORCE_LOGIN'] == 1) {
                    $flagLogin = false;

                    if (!isset($_SESSION['USER_LOGGED'])) {

                        if (defined('PM_SINGLE_SIGN_ON')) {
                            $pluginRegistry = PluginRegistry::loadSingleton();

                            if ($pluginRegistry->existsTrigger(PM_SINGLE_SIGN_ON)) {
                                if ($pluginRegistry->executeTriggers(PM_SINGLE_SIGN_ON, null)) {
                                    global $RBAC;

                                    //Start new session
                                    @session_destroy();
                                    session_start();
                                    session_regenerate_id();

                                    //Authenticate
                                    $_GET['u'] = $_SERVER['REQUEST_URI'];

                                    require_once(PATH_METHODS . 'login' . PATH_SEP . 'authenticationSso.php');
                                    exit(0);
                                }
                            }
                        }

                        $flagLogin = true;
                    } else {
                        if ($_SESSION['USER_LOGGED'] != $arrayAppDelegationData['USR_UID']) {
                            G::SendTemporalMessage('ID_CASE_ASSIGNED_ANOTHER_USER', 'error', 'label');

                            $flagLogin = true;
                        }
                    }

                    if ($flagLogin) {
                        header(
                            'Location: /sys' . config("system.workspace") . '/' . SYS_LANG . '/' . SYS_SKIN .
                            '/login/login?u=' . urlencode($_SERVER['REQUEST_URI'])
                        );

                        exit(0);
                    }
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}
