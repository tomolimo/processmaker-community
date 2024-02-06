<?php

namespace ProcessMaker\Cases;

use AbeResponses;
use AppDelegation;
use AppDelegationPeer;
use AppDocumentDrive;
use BasePeer;
use Cases;
use Derivation;
use Event;
use Exception;
use G;
use Illuminate\Support\Facades\Log;
use PMLicensedFeatures;
use ProcessMaker\BusinessModel\Cases\InputDocument;
use ProcessMaker\BusinessModel\Pmgmail;
use ProcessMaker\ChangeLog\ChangeLog;
use stdClass;
use Users;
use WsBase;

trait CasesTrait
{

    /**
     * This initiates the routing of the case given the application and the form
     * data in the web application interface.
     * @param string $processUid
     * @param string $application
     * @param array $postForm
     * @param string $status
     * @param boolean $flagGmail
     * @param string $tasUid
     * @param integer $index
     * @param string $userLogged
     * @return stdClass
     */
    public function routeCase($processUid, $application, $postForm, $status, $flagGmail, $tasUid, $index, $userLogged): stdClass
    {
        //warning: we are not using the result value of function thisIsTheCurrentUser, so I'm commenting to optimize speed.
        $appFields = $this->loadCase($application);
        $appFields['APP_DATA'] = array_merge($appFields['APP_DATA'], G::getSystemConstants());

        $triggerDebug = [];
        $triggers = $this->loadTriggers($tasUid, 'ASSIGN_TASK', -2, 'BEFORE');

        //if there are some triggers to execute
        if (sizeof($triggers) > 0) {
            //Execute triggers before derivation
            $appFields['APP_DATA'] = $this->executeTriggers($tasUid, 'ASSIGN_TASK', -2, 'BEFORE', $appFields['APP_DATA']);

            //save trigger variables for debugger
            $triggerDebug[] = [
                'NUM_TRIGGERS' => sizeof($triggers),
                'TIME' => G::toUpper(G::loadTranslation('ID_BEFORE')),
                'TRIGGERS_NAMES' => array_column($triggers, 'TRI_TITLE'),
                'TRIGGERS_VALUES' => $triggers,
                'TRIGGERS_EXECUTION_TIME' => $this->arrayTriggerExecutionTime
            ];
        }

        unset($appFields['APP_STATUS']);
        unset($appFields['APP_PROC_STATUS']);
        unset($appFields['APP_PROC_CODE']);
        unset($appFields['APP_PIN']);

        $appFields["DEL_INDEX"] = $index;
        $appFields["TAS_UID"] = $tasUid;
        $appFields["USER_UID"] = $userLogged;
        $appFields["CURRENT_DYNAFORM"] = "-2";
        $appFields["OBJECT_TYPE"] = "ASSIGN_TASK";

        //save data
        $this->updateCase($application, $appFields);

        //prepare information for the derivation
        $derivation = new Derivation();
        $currentDerivation = [
            'APP_UID' => $application,
            'DEL_INDEX' => $index,
            'APP_STATUS' => $status,
            'TAS_UID' => $tasUid,
            'ROU_TYPE' => $postForm['ROU_TYPE']
        ];
        $dataForPrepareInfo = [
            'USER_UID' => $userLogged,
            'APP_UID' => $application,
            'DEL_INDEX' => $index
        ];

        //we define some parameters in the before the derivation
        //then this function will be route the case
        $arrayDerivationResult = $derivation->beforeDerivate(
                $dataForPrepareInfo,
                $postForm['TASKS'],
                $postForm['ROU_TYPE'],
                $currentDerivation
        );

        if (!empty($arrayDerivationResult)) {
            foreach ($postForm['TASKS'] as $key => $value) {
                if (isset($value['TAS_UID'])) {
                    foreach ($arrayDerivationResult as $value2) {
                        if ($value2['TAS_UID'] == $value['TAS_UID']) {
                            $postForm['TASKS'][$key]['DEL_INDEX'] = $value2['DEL_INDEX'];
                            break;
                        }
                    }
                }
            }
        }

        $appFields = $this->loadCase($application); //refresh appFields, because in derivations should change some values
        $triggers = $this->loadTriggers($tasUid, 'ASSIGN_TASK', -2, 'AFTER'); //load the triggers after derivation
        if (sizeof($triggers) > 0) {
            $appFields['APP_DATA'] = $this->ExecuteTriggers($tasUid, 'ASSIGN_TASK', -2, 'AFTER', $appFields['APP_DATA']); //Execute triggers after derivation

            $triggerDebug[] = [
                'NUM_TRIGGERS' => sizeof($triggers),
                'TIME' => G::toUpper(G::loadTranslation('ID_AFTER')),
                'TRIGGERS_NAMES' => array_column($triggers, 'TRI_TITLE'),
                'TRIGGERS_VALUES' => $triggers,
                'TRIGGERS_EXECUTION_TIME' => $this->arrayTriggerExecutionTime
            ];
        }
        unset($appFields['APP_STATUS']);
        unset($appFields['APP_PROC_STATUS']);
        unset($appFields['APP_PROC_CODE']);
        unset($appFields['APP_PIN']);

        $appFields["DEL_INDEX"] = $index;
        $appFields["TAS_UID"] = $tasUid;
        $appFields["USER_UID"] = $userLogged;
        $appFields["CURRENT_DYNAFORM"] = "-2";
        $appFields["OBJECT_TYPE"] = "ASSIGN_TASK";

        $this->updateCase($application, $appFields);

        // Send notifications - Start
        $user = new Users();
        $userInfo = $user->load($userLogged);
        $fromName = $userInfo['USR_FIRSTNAME'] . ' ' . $userInfo['USR_LASTNAME'];

        $fromData = $fromName . ($userInfo['USR_EMAIL'] != '' ? ' <' . $userInfo['USR_EMAIL'] . '>' : '');

        if ($flagGmail === true) {
            $appDel = new AppDelegation();
            $actualThread = $appDel->Load($application, $index);

            $appDelPrev = $appDel->LoadParallel($application);
            $pmGmail = new Pmgmail();
            foreach ($appDelPrev as $app) {
                if (($app['DEL_INDEX'] != $index) && ($app['DEL_PREVIOUS'] != $actualThread['DEL_PREVIOUS'])) {
                    $pmGmail->gmailsIfSelfServiceValueBased($application, $app['DEL_INDEX'], $postForm['TASKS'], $appFields['APP_DATA']);
                }
            }
        }

        try {
            $this->sendNotifications($tasUid, $postForm['TASKS'], $appFields['APP_DATA'], $application, $index, $fromData);
        } catch (Exception $e) {
            G::SendTemporalMessage(G::loadTranslation('ID_NOTIFICATION_ERROR') . ' - ' . $e->getMessage(), 'warning', 'string', null, '100%');
        }
        // Send notifications - End
        // Events - Start
        $event = new Event();

        $event->closeAppEvents($processUid, $application, $index, $tasUid);
        $currentAppDel = AppDelegationPeer::retrieveByPk($application, $index + 1);
        $multipleDelegation = false;
        // check if there are multiple derivations
        if (count($postForm['TASKS']) > 1) {
            $multipleDelegation = true;
        }
        // If the case has been delegated
        if (isset($currentAppDel)) {
            // if there is just a single derivation the TASK_UID can be set by the delegation data
            if (!$multipleDelegation) {
                $arrayResult = $currentAppDel->toArray(BasePeer::TYPE_FIELDNAME);
                $event->createAppEvents($arrayResult['PRO_UID'], $arrayResult['APP_UID'], $arrayResult['DEL_INDEX'], $arrayResult['TAS_UID']);
            } else {
                // else we need to check every task and create the events if it have any
                foreach ($postForm['TASKS'] as $taskDelegated) {
                    $arrayResult = $currentAppDel->toArray(BasePeer::TYPE_FIELDNAME);
                    $event->createAppEvents($arrayResult['PRO_UID'], $arrayResult['APP_UID'], $arrayResult['DEL_INDEX'], $taskDelegated['TAS_UID']);
                }
            }
        }
        //Events - End


        $result = [
            'appFields' => $appFields,
            'triggerDebug' => $triggerDebug
        ];
        return (object) $result;
    }

    /**
     * This initiates the routing of the case given the application and the form 
     * data in the email application interface.
     * @param string $appUid
     * @param int $delIndex
     * @param string $aber
     * @param string $dynUid
     * @param array $forms
     * @param string $remoteAddr
     * @param array $files
     * @return array
     * @throws Exception
     */
    public function routeCaseActionByEmail($appUid, $delIndex, $aber, $dynUid, $forms, $remoteAddr, $files): array
    {
        //Load data related to the case
        $case = new Cases();
        $fields = $case->loadCase($appUid, $delIndex);

        // Check if the current thread is not finished
        if (!is_null($fields['DEL_FINISH_DATE'])) {
            $message = G::loadTranslation('ID_ABE_FORM_ALREADY_FILLED');
            Log::error($message);
            throw new Exception($message);
        }
        // Merge the data
        $fields['APP_DATA'] = array_merge($fields['APP_DATA'], $forms);

        //Get current user info
        $delegation = new AppDelegation();
        $currentUsrUid = $delegation->getUserAssignedInThread($appUid, $delIndex);
        if (!is_null($currentUsrUid)) {
            $users = new Users();
            $userInfo = $users->loadDetails($currentUsrUid);
            $fields["APP_DATA"]["USER_LOGGED"] = $currentUsrUid;
            $fields["APP_DATA"]["USR_USERNAME"] = $userInfo['USR_USERNAME'];
        }

        foreach ($fields["APP_DATA"] as $index => $value) {
            $_SESSION[$index] = $value;
        }

        $fields['CURRENT_DYNAFORM'] = $dynUid;
        $fields['USER_UID'] = $fields['CURRENT_USER_UID'];

        ChangeLog::getChangeLog()
                ->getUsrIdByUsrUid($fields['USER_UID'], true)
                ->setSourceId(ChangeLog::FromABE);

        //Update case info
        $case->updateCase($appUid, $fields);
        if (isset($files['form'])) {
            if (isset($files["form"]["name"]) && count($files["form"]["name"]) > 0) {
                $inputDocument = new InputDocument();
                $inputDocument->uploadFileCase($files, $case, $fields, $currentUsrUid, $appUid, $delIndex);
            }
        }
        $wsBase = new WsBase();
        $result = $wsBase->derivateCase($fields['CURRENT_USER_UID'], $appUid, $delIndex, true);
        $code = is_array($result) ? $result['status_code'] : $result->status_code;

        $dataResponses = [];
        $dataResponses['ABE_REQ_UID'] = $aber;
        $dataResponses['ABE_RES_CLIENT_IP'] = $remoteAddr;
        $dataResponses['ABE_RES_DATA'] = serialize($forms);
        $dataResponses['ABE_RES_STATUS'] = 'PENDING';
        $dataResponses['ABE_RES_MESSAGE'] = '';

        try {
            require_once 'classes/model/AbeResponses.php';
            $abeResponses = new AbeResponses();
            $dataResponses['ABE_RES_UID'] = $abeResponses->createOrUpdate($dataResponses);
        } catch (Exception $error) {
            $message = $error->getMessage();
            Log::error($message);
            throw $error;
        }

        if ($code == 0) {
            //Save Cases Notes
            $abeRequest = loadAbeRequest($aber);
            $abeConfiguration = loadAbeConfiguration($abeRequest['ABE_UID']);

            if ($abeConfiguration['ABE_CASE_NOTE_IN_RESPONSE'] == 1) {
                $response = new stdclass();
                $response->usrUid = $fields['APP_DATA']['USER_LOGGED'];
                $response->appUid = $appUid;
                $response->delIndex = $delIndex;
                $response->noteText = "Check the information that was sent for the receiver: " . $abeRequest['ABE_REQ_SENT_TO'];
                postNote($response);
            }

            $abeRequest['ABE_REQ_ANSWERED'] = 1;
            $code == 0 ? uploadAbeRequest($abeRequest) : '';
        } else {
            $resStatusCode = is_array($result) ? $result['status_code'] : $result->status_code;
            $resMessage = is_array($result) ? $result['message'] : $result->message;
            $message = 'An error occurred while the application was being processed.<br /><br />
                        Error code: ' . $resStatusCode . '<br />
                        Error message: ' . $resMessage . '<br /><br />';
            Log::error($message);
            throw new Exception($message);
        }

        // Update
        $resMessage = is_array($result) ? $result['message'] : $result->message;
        $dataResponses['ABE_RES_STATUS'] = ($code == 0 ? 'SENT' : 'ERROR');
        $dataResponses['ABE_RES_MESSAGE'] = ($code == 0 ? '-' : $resMessage);

        try {
            $abeResponses = new AbeResponses();
            $abeResponses->createOrUpdate($dataResponses);
        } catch (Exception $error) {
            $message = $error->getMessage();
            Log::error($message);
            throw $error;
        }
        return $dataResponses;
    }
}
