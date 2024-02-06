<?php

/**
 * appProxy.php
 *
 * Controller for return information about the cases notes and summary form
 *
 * @link https://wiki.processmaker.com/3.2/Case_Notes
 * @link https://wiki.processmaker.com/3.2/Case_Summary
*/

use ProcessMaker\BusinessModel\Cases as BmCases;
use ProcessMaker\Exception\CaseNoteUploadFile;
use ProcessMaker\Model\AppNotes as Notes;
use ProcessMaker\Model\Delegation;
use ProcessMaker\Model\Documents;
use ProcessMaker\Model\User;
use ProcessMaker\Util\DateTime;

if (!isset($_SESSION['USER_LOGGED'])) {
    $response = new stdclass();
    $response->message = G::LoadTranslation('ID_LOGIN_AGAIN');
    $response->lostSession = true;
    print G::json_encode( $response );
    die();
}
/**
 * App controller
 *
 * @author Erik Amaru Ortiz <erik@colosa.com, aortiz.erik@gmail.com>
 * @herits Controller
 * @access public
 */

class AppProxy extends HttpProxyController
{

    /**
     * Get Notes List
     *
     * @param int $httpData->start
     * @param int $httpData->limit
     * @param string $httpData->appUid (optionalif it is not passed try use $_SESSION['APPLICATION'])
     * @return array containing the case notes
     *
     * @see workflow/engine/methods/cases/open.php
     * @see workflow/engine/methods/cases/casesListExtJs.php
     * @see workflow/engine/methods/cases/casesConsolidatedListExtJs.php
     *
     * @link https://wiki.processmaker.com/3.2/Case_Notes
     * @link https://wiki.processmaker.com/3.2/Cases/Case_Notes
     */
    function getNotesList($httpData)
    {
        if (!isset($_SESSION['USER_LOGGED'])) {
            $response = new stdclass();
            $response->message = G::LoadTranslation('ID_LOGIN_AGAIN');
            $response->lostSession = true;
            print G::json_encode($response);
            die();
        }

        $appUid = null;

        if (isset($httpData->appUid) && trim($httpData->appUid) != "") {
            $appUid = trim($httpData->appUid);
        } else {
            if (isset($_SESSION["APPLICATION"])) {
                $appUid = $_SESSION["APPLICATION"];
            }
        }

        $delIndex = 0;

        if (isset($httpData->delIndex) && trim($httpData->delIndex) != "") {
            $delIndex = (int)(trim($httpData->delIndex));
        } else {
            if (isset($_SESSION["INDEX"])) {
                $delIndex = (int)($_SESSION["INDEX"]);
            }
        }

        if (!isset($appUid)) {
            throw new Exception(G::LoadTranslation("ID_RESOLVE_APPLICATION_ID"));
        }

        $case = new Cases();

        if (!isset($_SESSION['PROCESS']) && !isset($httpData->pro)) {
            $caseLoad = $case->loadCase($appUid);
            $httpData->pro = $caseLoad['PRO_UID'];
        }

        if (!isset($httpData->pro) || empty($httpData->pro)) {
            $proUid = $_SESSION['PROCESS'];
        } else {
            $proUid = $httpData->pro;
        }

        if (!isset($httpData->tas) || empty($httpData->tas)) {
            $tasUid = isset($_SESSION['TASK']) ? $_SESSION['TASK'] : "";
        } else {
            $tasUid = $httpData->tas;
        }

        // Get user logged
        $usrUid = $_SESSION['USER_LOGGED'];

        // Review if the user has the permissions
        $respView = $case->getAllObjectsFrom($proUid, $appUid, $tasUid, $usrUid, "VIEW", $delIndex);
        $respBlock = $case->getAllObjectsFrom($proUid, $appUid, $tasUid, $usrUid, "BLOCK", $delIndex);
        if ($respView['CASES_NOTES'] == 0 && $respBlock['CASES_NOTES'] == 0) {
            return [
                'totalCount' => 0,
                'notes' => [],
                'noPerms' => 1
            ];
        }

        // Get the notes
        $appNote = new Notes();
        $total = $appNote->getTotal($appUid);
        $response = $appNote->getNotes($appUid, $httpData->start, $httpData->limit);
        $response = AppNotes::applyHtmlentitiesInNotes($response);

        // Prepare the response
        $documents = new Documents();
        $iterator = 0;
        foreach ($response['notes'] as $value) {
            $response['notes'][$iterator]['NOTE_DATE'] = DateTime::convertUtcToTimeZone($value['NOTE_DATE']);
            $response['notes'][$iterator]['attachments'] = $documents->getFiles($value['NOTE_ID'], $appUid);
            $iterator++;
        }
        // Get the total of cases notes by case
        $response['totalCount'] = $total;

        require_once("classes/model/Application.php");
        $application = new Application();
        $appInfo = $application->Load($appUid);
        $response['appTitle'] = $appInfo['APP_TITLE'];

        return $response;
    }

    /**
     * Post a note
     *
     * @param string $httpData->appUid (optional, if it is not passed try use $_SESSION['APPLICATION'])
     * @return array containg the case notes
     */
    function postNote ($httpData)
    {
        if (isset( $httpData->appUid ) && trim( $httpData->appUid ) != "") {
            $appUid = $httpData->appUid;
        } else {
            $appUid = $_SESSION['APPLICATION'];
        }

        if (! isset( $appUid )) {
            throw new Exception(G::LoadTranslation("ID_CANT_RESOLVE_APPLICATION"));
        }

        $usrUid = (isset( $_SESSION['USER_LOGGED'] )) ? $_SESSION['USER_LOGGED'] : "";
        $noteContent = addslashes( $httpData->noteText );

        //Disabling the controller response because we handle a special behavior
        $this->setSendResponse(false);

        try {
            $sendMail = intval($httpData->swSendMail);
            // Define the Case for register a case note
            $cases = new BmCases();
            $response = $cases->addNote($appUid, $usrUid, $noteContent, $sendMail);
        } catch (CaseNoteUploadFile $e) {
            $response = new stdclass();
            $response->success = 'success';
            $response->message = $e->getMessage();
            die(G::json_encode($response));
        } catch (Exception $error) {
            $response = new stdclass();
            $response->success  = 'success';
            $response->message  = G::LoadTranslation('ID_ERROR_SEND_NOTIFICATIONS');
            $response->message .= '<br /><br />' . $error->getMessage() . '<br /><br />';
            $response->message .= G::LoadTranslation('ID_CONTACT_ADMIN');
            die(G::json_encode($response));
        }

        //Send the response to client
        @ini_set("implicit_flush", 1);
        ob_start();
        if (!isset($_SESSION['USER_LOGGED'])) {
            $response = new stdclass();
            $response->message = G::LoadTranslation('ID_LOGIN_AGAIN');
            $response->lostSession = true;
            print G::json_encode( $response );
            die();
        }
        echo G::json_encode($response);
        @ob_flush();
        @flush();
        @ob_end_flush();
        ob_implicit_flush(true);
    }

    /**
     * request to open the case summary
     *
     * @param string $httpData->appUid
     * @param string $httpData->delIndex
     * @return object bool $result->succes, string $result->message(is an exception was thrown), string $result->dynUid
     */
    function requestOpenSummary ($httpData)
    {
        global $RBAC;
        $this->success = true;
        $this->dynUid = '';

        switch ($RBAC->userCanAccess( 'PM_CASES' )) {
            case - 2:
                throw new Exception( G::LoadTranslation( 'ID_USER_HAVENT_RIGHTS_SYSTEM' ) );
                break;
            case - 1:
                throw new Exception( G::LoadTranslation( 'ID_USER_HAVENT_RIGHTS_PAGE' ) );
                break;
        }

        $case = new Cases();
        
        if ($httpData->action == 'sent') { // Get the last valid delegation for participated list
            $criteria = new Criteria();
            $criteria->addSelectColumn(AppDelegationPeer::DEL_INDEX);
            $criteria->add(AppDelegationPeer::APP_UID, $httpData->appUid);
            $criteria->add(AppDelegationPeer::DEL_FINISH_DATE, null, Criteria::ISNULL);
            $criteria->addDescendingOrderByColumn(AppDelegationPeer::DEL_INDEX);
            if (AppDelegationPeer::doCount($criteria) > 0) {
                $dataset = AppDelegationPeer::doSelectRS($criteria, Propel::getDbConnection('workflow_ro'));
                $dataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
                $dataset->next();
                $row = $dataset->getRow();
                $httpData->delIndex = $row['DEL_INDEX'];
            }
        }
        $applicationFields = $case->loadCase( $httpData->appUid, $httpData->delIndex );
        $process = new Process();
        $processData = $process->load( $applicationFields['PRO_UID'] );

        if (isset( $processData['PRO_DYNAFORMS']['PROCESS'] )) {
            $this->dynUid = $processData['PRO_DYNAFORMS']['PROCESS'];
        }

        $_SESSION['_applicationFields'] = $applicationFields;
        $_SESSION['_processData'] = $processData;
        $_SESSION['APPLICATION'] = $httpData->appUid;
        $_SESSION['INDEX'] = $httpData->delIndex;
        $_SESSION['PROCESS'] = $applicationFields['PRO_UID'];
        $_SESSION['TASK'] = $applicationFields['TAS_UID'];
        $_SESSION['STEP_POSITION'] = '';
    }

    /**
     * Get the case summary data
     *
     * @param object $httpData
     *
     * @return array contain the case summary data
     */
    function getSummary ($httpData)
    {
        $case = new Cases();
        $appFields = $case->loadCase($httpData->appUid, $httpData->delIndex);
        // Get the process
        $process = new Process();
        $processInfo = $process->load($appFields['PRO_UID']);
        // Apply mask
        $createDateLabel = applyMaskDateEnvironment($appFields['CREATE_DATE'],'', false);
        $updateDateLabel = applyMaskDateEnvironment($appFields['UPDATE_DATE'],'', false);
        $delegateDateLabel = applyMaskDateEnvironment($appFields['DEL_DELEGATE_DATE'],'', false);
        // Get the duration
        $endDate = !empty($appFields['APP_FINISH_DATE']) ? $appFields['APP_FINISH_DATE'] : date("Y-m-d H:i:s");
        $threadDuration = getDiffBetweenDates($appFields['DEL_DELEGATE_DATE'], $endDate);
        // Get case properties
        $i = 0;
        $caseProperties = [
            $i++ => [
                'id' => 'TITLE',
                'label' => G::LoadTranslation('ID_CASE_PROPERTIES'),
                'value' => '',
            ],
            $i++ => [ // Case Number
                'id' => 'APP_NUMBER',
                'label' => G::LoadTranslation('ID_CASE_NUMBER') . ': ',
                'value' => $appFields['APP_NUMBER'],
            ],
            $i++ => [ // Case Description
                'id' => 'CASE_DESCRIPTION',
                'label' => G::LoadTranslation('ID_CASE_DESCRIPTION') . ': ',
                'value' => $appFields["DESCRIPTION"],
            ],
            $i++ => [ // Case Status
                'id' => 'CASE_STATUS',
                'label' => G::LoadTranslation('ID_CASE_STATUS') . ': ',
                'value' => $appFields['STATUS'],
            ],
            $i++ => [ // Case Uid
                'id' => 'APP_UID',
                'label' => G::LoadTranslation('ID_CASE_UID') . ': ',
                'value' => $appFields['APP_UID'],
            ],
            $i++ => [ // Creator
                'id' => 'CREATOR',
                'label' => G::LoadTranslation('ID_CREATOR') . ': ',
                'value' => $appFields['CREATOR'],
            ],
            $i++ => [ // Create Date
                'id' => 'CREATE_DATE',
                'label' => G::LoadTranslation('ID_CREATE_DATE') . ': ',
                'value' => DateTime::convertUtcToTimeZone($createDateLabel),
            ],
            $i++ => [ // Last Update
                'id' => 'UPDATE_DATE',
                'label' => G::LoadTranslation('ID_LAST_DATE') . ': ',
                'value' => DateTime::convertUtcToTimeZone($updateDateLabel),
            ],
        ];
        // Get the pending threads
        $delegation = new Delegation();
        $threads = $delegation::getPendingThreads($appFields['APP_NUMBER']);
        $i = 0;
        $taskProperties[$i] = [
            'id' => 'TITLE',
            'label' => G::LoadTranslation('ID_CURRENT_TASKS'),
            'value' => '',
        ];
        foreach ($threads as $row) {
            $j = 0;
            $delegateDateLabel = applyMaskDateEnvironment($row['DEL_DELEGATE_DATE'],'', false);
            $initDateLabel = applyMaskDateEnvironment($row['DEL_INIT_DATE'],'', false);
            $dueDateLabel = applyMaskDateEnvironment($row['DEL_TASK_DUE_DATE'],'', false);
            // Get thread duration
            $endDate = !empty($appFields['APP_FINISH_DATE']) ? $appFields['APP_FINISH_DATE'] : date("Y-m-d H:i:s");
            $threadDuration = getDiffBetweenDates($appFields['DEL_DELEGATE_DATE'], $endDate);
            // Get user information
            if (!empty($row['USR_ID'])) {
                $userInfo = User::getInformation($row['USR_ID']);
                $currentUser = $userInfo['usr_lastname'] .' '. $userInfo['usr_firstname'];
            } else {
                $currentUser = G::LoadTranslation('ID_UNASSIGNED');
            }
            $threadProperties = [
                $j++ => [ // Task
                    'id' => 'TASK_TITLE',
                    'label' => G::LoadTranslation('ID_TASK') . ': ',
                    'value' => $row['TAS_TITLE'],
                ],
                $j++ => [ // Case Title per thread
                    'id' => 'CASE_TITLE',
                    'label' => G::LoadTranslation('ID_CASE_THREAD_TITLE') . ': ',
                    'value' => $row['DEL_TITLE'],
                ],
                $j++ => [ // Current User
                    'id' => 'CURRENT_USER',
                    'label' => G::LoadTranslation('ID_CURRENT_USER') . ': ',
                    'value' => $currentUser,
                ],
                $j++ => [ // Task Delegate Date
                    'id' => 'DEL_DELEGATE_DATE',
                    'label' => G::LoadTranslation('ID_TASK_DELEGATE_DATE') . ': ',
                    'value' => DateTime::convertUtcToTimeZone($delegateDateLabel),
                ],
                $j++ => [ // Task Init Date
                    'id' => 'DEL_INIT_DATE',
                    'label' => G::LoadTranslation('ID_TASK_INIT_DATE') . ': ',
                    'value' => DateTime::convertUtcToTimeZone($initDateLabel),
                ],
                $j++ => [ // Task Due Date
                    'id' => 'DEL_TASK_DUE_DATE',
                    'label' => G::LoadTranslation('ID_TASK_DUE_DATE') . ': ',
                    'value' => DateTime::convertUtcToTimeZone($dueDateLabel),
                ],
            ];
            $taskProperties[++$i] = $threadProperties;
        }
        // Get summary
        $i = 0;
        $summary = [
            $i++ => [
                'id' => 'TITLE',
                'label' => G::LoadTranslation('ID_SUMMARY'),
                'value' => '',
            ],
            $i++ => [ // Process
                'id' => 'PRO_TITLE',
                'label' => G::LoadTranslation('ID_PROCESS_NAME') . ': ',
                'value' => $processInfo['PRO_TITLE'],
            ],
            $i++ => [ // Process Category
                'id' => 'CATEGORY',
                'label' => G::LoadTranslation('ID_CATEGORY_PROCESS') . ': ',
                'value' => $processInfo['PRO_CATEGORY_LABEL'],
            ],
            $i++ => [ // Process description
                'id' => 'PRO_DESCRIPTION',
                'label' => G::LoadTranslation('ID_PRO_DESCRIPTION') . ': ',
                'value' => $processInfo['PRO_DESCRIPTION'],
            ],
            $i++ => [ // Case Number
                'id' => 'APP_NUMBER',
                'label' => G::LoadTranslation('ID_CASE_NUMBER') . ': ',
                'value' => $appFields['APP_NUMBER'],
            ],
            $i++ => [ // Case Status
                'id' => 'CASE_STATUS',
                'label' => G::LoadTranslation('ID_CASE_STATUS') . ': ',
                'value' => $appFields['STATUS'],
            ],
            $i++ => [ // Create Date
                'id' => 'CREATE_DATE',
                'label' => G::LoadTranslation('ID_CREATE_DATE') . ': ',
                'value' => DateTime::convertUtcToTimeZone($createDateLabel),
            ],
            $i++ => [ // Delegate Date
                'id' => 'DEL_DELEGATE_DATE',
                'label' => G::LoadTranslation('ID_TASK_DELEGATE_DATE') . ': ',
                'value' => DateTime::convertUtcToTimeZone($delegateDateLabel),
            ],
            $i++ => [ // Duration
                'id' => 'DURATION',
                'label' => G::LoadTranslation('ID_DURATION') . ': ',
                'value' => $threadDuration,
            ]
        ];
        // Prepare the result
        $data = [];
        $data['summary'] = $summary;
        $data['caseProperties'] = $caseProperties;
        $data['taskProperties'] = $taskProperties;

        return $data;
    }
}

