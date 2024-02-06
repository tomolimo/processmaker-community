<?php
/**
 * proxySaveReassignCasesList.php
 *
 * Reassign functionality only from the cases list Review
 * @see https://wiki.processmaker.com/3.0/Cases#Reassign
 */

use ProcessMaker\Model\Delegation;

// $_POST['data'] is information about the cases that will reassign
$dataPost = G::json_decode($_POST['data']);

$casesReassignedCount = 0;
$serverResponse = [];

// if there are no records to save return -1
if (empty($dataPost)) {
    $serverResponse['TOTAL'] = -1;

    echo G::json_encode($serverResponse);
    die();
} elseif (is_array($dataPost)) {
    $cases = new Cases();
    $currentCasesReassigned = 0;
    foreach ($dataPost as $data) {
        // It was supposed will return only one thread related to the task
        // todo: implement the reassign case for multi instance task
        $openThreads = Delegation::getOpenThreads($data->APP_NUMBER, $data->TAS_UID);
        if (!empty($openThreads)) {
            // Get the user information assigned in the index
            $currentUsrUid = Delegation::getCurrentUser($openThreads['APP_NUMBER'], $openThreads['DEL_INDEX']);
            $flagReassign = true;
            // Define the flag: it was supposed that the case was assigned another person
            if (!empty($currentUsrUid)) {
                if ($currentUsrUid === $data->APP_REASSIGN_USER_UID) {
                    $flagReassign = false;
                }
            } else {
                // Return an error if the index was CLOSED
                throw new Exception(G::LoadTranslation('ID_REASSIGNMENT_ERROR'));
            }
            // If the currentUsrUid is different to nextUser, create the thread
            if ($flagReassign) {
                $cases->reassignCase(
                    $openThreads['APP_UID'],
                    $openThreads['DEL_INDEX'],
                    (!empty($openThreads['USR_UID']) ? $openThreads['USR_UID'] : $_SESSION['USER_LOGGED']),
                    $data->APP_REASSIGN_USER_UID
                );
            }

            $currentCasesReassigned++;
            $casesReassignedCount++;
            $serverResponse[] = [
                'APP_REASSIGN_USER' => $data->APP_REASSIGN_USER,
                'APP_TITLE' => $data->APP_TITLE,
                'TAS_TITLE' => $data->APP_TAS_TITLE,
                'REASSIGNED_CASES' => $currentCasesReassigned
            ];

            // Save the note reassign reason
            if (!empty($data->NOTE_REASON)) {
                $appNotes = new AppNotes();
                $noteContent = addslashes($data->NOTE_REASON);
                $appNotes->postNewNote(
                    $openThreads['APP_UID'],
                    $_SESSION['USER_LOGGED'],
                    $noteContent,
                    isset($data->NOTIFY_REASSIGN) ? $data->NOTIFY_REASSIGN : false
                );
            }
        } else {
            // Return an error if the index was CLOSED
            throw new Exception(G::LoadTranslation('ID_REASSIGNMENT_ERROR'));
        }
    }
}

$serverResponse['TOTAL'] = $casesReassignedCount;
echo G::json_encode($serverResponse);

