<?php

use ProcessMaker\Core\System;
use ProcessMaker\Util\DateTime;

/**
 * Skeleton subclass for representing a row from the 'APP_NOTES' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements. This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 * @package classes.model
 */

class AppNotes extends BaseAppNotes
{
    /**
     * Get the existing case notes information from a case
     *
     * @param string $appUid
     * @param string $usrUid
     * @param string $start
     * @param int $limit
     * @param string $sort
     * @param string $dir
     * @param string $dateFrom
     * @param string $dateTo
     * @param string $search
     *
     * @return array
     *
     * @see \Cases->getCaseNotes()
     * @see \AppProxy->getNotesList()
     * @see \Home->getAppsData()
     * @see workflow/engine/methods/cases/caseNotesAjax.php->getNotesList()
     * @see \ProcessMaker\BusinessModel\Cases->getCaseNotes()
     * @see \ProcessMaker\Services\Api\Light->doGetCaseNotes()
     *
     * @link https://wiki.processmaker.com/3.2/Case_Notes#Viewing_Existing_Case_Notes
     */
    public function getNotesList(
        $appUid,
        $usrUid = '',
        $start = '',
        $limit = 25,
        $sort = 'APP_NOTES.NOTE_DATE',
        $dir = 'DESC',
        $dateFrom = '',
        $dateTo = '',
        $search = ''
    ) {
        $criteria = new Criteria('workflow');
        $criteria->clearSelectColumns();

        $criteria->addSelectColumn(AppNotesPeer::APP_UID);
        $criteria->addSelectColumn(AppNotesPeer::USR_UID);
        $criteria->addSelectColumn(AppNotesPeer::NOTE_DATE);
        $criteria->addSelectColumn(AppNotesPeer::NOTE_CONTENT);
        $criteria->addSelectColumn(AppNotesPeer::NOTE_TYPE);
        $criteria->addSelectColumn(AppNotesPeer::NOTE_AVAILABILITY);
        $criteria->addSelectColumn(AppNotesPeer::NOTE_ORIGIN_OBJ);
        $criteria->addSelectColumn(AppNotesPeer::NOTE_AFFECTED_OBJ1);
        $criteria->addSelectColumn(AppNotesPeer::NOTE_AFFECTED_OBJ2);
        $criteria->addSelectColumn(AppNotesPeer::NOTE_RECIPIENTS);
        $criteria->addSelectColumn(UsersPeer::USR_USERNAME);
        $criteria->addSelectColumn(UsersPeer::USR_FIRSTNAME);
        $criteria->addSelectColumn(UsersPeer::USR_LASTNAME);
        $criteria->addSelectColumn(UsersPeer::USR_EMAIL);

        $criteria->addJoin(AppNotesPeer::USR_UID, UsersPeer::USR_UID, Criteria::LEFT_JOIN);

        $criteria->add(AppNotesPeer::APP_UID, $appUid, Criteria::EQUAL);

        if ($usrUid != '') {
            $criteria->add(AppNotesPeer::USR_UID, $usrUid, Criteria::EQUAL);
        }
        if ($dateFrom != '') {
            $criteria->add(AppNotesPeer::NOTE_DATE, $dateFrom, Criteria::GREATER_EQUAL);
        }
        if ($dateTo != '') {
            $criteria->add(AppNotesPeer::NOTE_DATE, $dateTo, Criteria::LESS_EQUAL);
        }
        if ($search != '') {
            $criteria->add(AppNotesPeer::NOTE_CONTENT, '%' . $search . '%', Criteria::LIKE);
        }

        if ($dir == 'DESC') {
            $criteria->addDescendingOrderByColumn($sort);
        } else {
            $criteria->addAscendingOrderByColumn($sort);
        }

        $response = [];
        $totalCount = AppNotesPeer::doCount($criteria);
        $response['totalCount'] = $totalCount;
        $response['notes'] = [];

        if ($start != '') {
            $criteria->setLimit($limit);
            $criteria->setOffset($start);
        }

        $dataset = AppNotesPeer::doSelectRS($criteria);
        $dataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $dataset->next();

        while ($row = $dataset->getRow()) {
            $row['NOTE_CONTENT'] = stripslashes($row['NOTE_CONTENT']);
            $response['notes'][] = $row;
            $dataset->next();
        }

        $result = [];
        $result['criteria'] = $criteria;
        $result['array'] = $response;

        return $result;
    }

    public function postNewNote ($appUid, $usrUid, $noteContent, $notify = true, $noteAvalibility = "PUBLIC", $noteRecipients = "", $noteType = "USER", $noteDate = "now")
    {
        $this->setAppUid( $appUid );
        $this->setUsrUid( $usrUid );
        $this->setNoteDate( $noteDate );
        $this->setNoteContent( $noteContent );
        $this->setNoteType( $noteType );
        $this->setNoteAvailability( $noteAvalibility );
        $this->setNoteOriginObj( '' );
        $this->setNoteAffectedObj1( '' );
        $this->setNoteAffectedObj2( '' );
        $this->setNoteRecipients( $noteRecipients );

        if ($this->validate()) {
            // we save it, since we get no validation errors, or do whatever else you like.
            $res = $this->save();
            $msg = '';
        } else {
            // Something went wrong. We can now get the validationFailures and handle them.
            $msg = '';
            $validationFailuresArray = $this->getValidationFailures();
            foreach ($validationFailuresArray as $objValidationFailure) {
                $msg .= $objValidationFailure->getMessage() . "<br/>";
            }
            //return array ( 'codError' => -100, 'rowsAffected' => 0, 'message' => $msg );
        }
        if ($msg != "") {
            $response['success'] = G::LoadTranslation("ID_FAILURE");
            $response['message'] = $msg;
        } else {
            $response['success'] = 'success';
            $response['message'] = '';
        }

        if ($notify) {
            if ($noteRecipients == "") {
                $noteRecipientsA = array ();
                $oCase = new Cases();
                $p = $oCase->getUsersParticipatedInCase( $appUid );
                foreach ($p['array'] as $key => $userParticipated) {
                    $noteRecipientsA[] = $key;
                }
                $noteRecipients = implode( ",", $noteRecipientsA );
            }

            $this->sendNoteNotification( $appUid, $usrUid, $noteContent, $noteRecipients );
        }

        return $response;
    }

    /**
     * Case note notification
     *
     * @param string $appUid
     * @param string $usrUid
     * @param string $noteContent
     * @param string $noteRecipients
     * @param string $from
     * @param integer $delIndex
     * @return void
     * @throws Exception
     *
     * @see AppNotes->addCaseNote()
     * @see AppNotes->postNewNote()
     * @see workflow/engine/src/ProcessMaker/Util/helpers.php::postNote()
    */
    public function sendNoteNotification ($appUid, $usrUid, $noteContent, $noteRecipients, $from = '', $delIndex = 0)
    {
        try {

            $configuration = System::getEmailConfiguration();

            $msgError = "";
            if (! isset( $configuration['MESS_ENABLED'] ) || $configuration['MESS_ENABLED'] != '1') {
                $msgError = "The default configuration wasn't defined";
                $configuration['MESS_ENGINE'] = '';
            }

            //This value can be empty when the previous task is: 'Script Task', 'Timer Event' or other without user.
            if (!empty($usrUid)) {
                $users = new Users();
                $userInfo = $users->load($usrUid);
                $authorName = ((($userInfo['USR_FIRSTNAME'] != '') || ($userInfo['USR_LASTNAME'] != '')) ? $userInfo['USR_FIRSTNAME'] . ' ' . $userInfo['USR_LASTNAME'] . ' ' : '') . '<' . $userInfo['USR_EMAIL'] . '>';
            } else {
                $authorName = G::LoadTranslation('UID_UNDEFINED_USER');
            }

            $cases = new Cases();
            $fieldCase = $cases->loadCase($appUid, $delIndex);
            $configNoteNotification['subject'] = G::LoadTranslation('ID_MESSAGE_SUBJECT_NOTE_NOTIFICATION') . " @#APP_TITLE ";
            //Define the body for the notification
            $configNoteNotification['body'] = $this->getBodyCaseNote($authorName, $noteContent);
            $body = nl2br(G::replaceDataField($configNoteNotification['body'], $fieldCase, 'mysql', false));

            $users = new Users();
            $recipientsArray = explode(",", $noteRecipients);

            foreach ($recipientsArray as $recipientUid) {
                $userInfo = $users->load($recipientUid);
                $to = ((($userInfo['USR_FIRSTNAME'] != '') || ($userInfo['USR_LASTNAME'] != '')) ? $userInfo['USR_FIRSTNAME'] . ' ' . $userInfo['USR_LASTNAME'] . ' ' : '') . '<' . $userInfo['USR_EMAIL'] . '>';

                $spool = new SpoolRun();
                $spool->setConfig($configuration);
                $messageArray = AppMessage::buildMessageRow(
                    '',
                    $appUid,
                    $delIndex,
                    WsBase::MESSAGE_TYPE_CASE_NOTE,
                    G::replaceDataField($configNoteNotification['subject'], $fieldCase, 'mysql', false),
                    G::buildFrom($configuration, $from),
                    $to,
                    $body,
                    '',
                    '',
                    '',
                    '',
                    'pending',
                    1,
                    $msgError,
                    true,
                    (isset($fieldCase['APP_NUMBER'])) ? $fieldCase['APP_NUMBER'] : 0,
                    (isset($fieldCase['PRO_ID'])) ? $fieldCase['PRO_ID'] : 0,
                    (isset($fieldCase['TAS_ID'])) ? $fieldCase['TAS_ID'] : 0
                );
                $spool->create($messageArray);

                if ($msgError == '') {
                    if (($configuration['MESS_BACKGROUND'] == '') || ($configuration['MESS_TRY_SEND_INMEDIATLY'] == '1')) {
                        $spool->sendMail();
                    }
                }

            }
            //Send derivation notification - End
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function addCaseNote($applicationUid, $userUid, $note, $sendMail)
    {
        $response = $this->postNewNote($applicationUid, $userUid, $note, false);

        if ($sendMail == 1) {

            $case = new Cases();

            $p = $case->getUsersParticipatedInCase($applicationUid, 'ACTIVE');
            $noteRecipientsList = array();

            foreach ($p["array"] as $key => $userParticipated) {
                if ($key != '') {
                    $noteRecipientsList[] = $key;
                }
            }

            $noteRecipients = implode(",", $noteRecipientsList);
            $note = stripslashes($note);

            $this->sendNoteNotification($applicationUid, $userUid, $note, $noteRecipients);
        }

        return $response;
    }

    /**
     * Add htmlEntities to notes in node_content
     * @param $notes
     * @return array
     */
    public static function applyHtmlentitiesInNotes($notes)
    {
        if (isset($notes) && isset($notes["array"])) {
            foreach ($notes["array"]["notes"] as &$note) {
                $note["NOTE_CONTENT"] = htmlentities($note["NOTE_CONTENT"], ENT_QUOTES, 'UTF-8');
            }
        }
        return $notes;
    }

    /**
     * Define the body for the case note notification
     *
     * @param string $authorName
     * @param string $noteContent
     *
     * @return string
    */
    private function getBodyCaseNote($authorName = '', $noteContent = '')
    {
        $body = G::LoadTranslation('ID_CASE_TITLE') . ': @#APP_TITLE<br />';
        $body .= G::LoadTranslation('ID_CASE_NUMBER') . ': @#APP_NUMBER<br />';
        $body .= G::LoadTranslation('ID_AUTHOR') . ': ' . $authorName . '<br /><br />' . $noteContent;

        return $body;
    }

}

