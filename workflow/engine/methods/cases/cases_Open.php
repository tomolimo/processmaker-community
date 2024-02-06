<?php
/**
 * cases_Open.php
 *
 * @see cases/casesList.js
 * @see cases/cases_Step.php
 * @see cases/cases_CatchSelfService.php
 * @see cases/derivatedGmail.php
 * @see cases/open.php
 * @see controllers/Home::indexSingle()
 * @see controllers/Home::startCase()
 * @see pmGmail/sso.php
 * @see webentry/access.php
 *
 * @link https://wiki.processmaker.com/3.2/Cases/Cases#Search_Criteria
 */

if(isset( $_GET['gmail']) && $_GET['gmail'] == 1){
    $_SESSION['gmail'] = 1;
}

/* Permissions */
if ($RBAC->userCanAccess( 'PM_CASES' ) != 1) {
    switch ($RBAC->userCanAccess( 'PM_CASES' )) {
        case - 2:
            G::SendTemporalMessage( 'ID_USER_HAVENT_RIGHTS_SYSTEM', 'error', 'labels' );
            G::header( 'location: ../login/login' );
            break;
        case - 1:
        default:
            G::SendTemporalMessage( 'ID_USER_HAVENT_RIGHTS_PAGE', 'error', 'labels' );
            G::header( 'location: ../login/login' );
            break;
    }
}

$caseInstance = new Cases();

//cleaning the case session data
Cases::clearCaseSessionData();

try {
    //Loading data for a Jump request
    if (!isset($_GET['APP_UID']) && isset($_GET['APP_NUMBER'])) {
        $_GET['APP_UID'] = $caseInstance->getApplicationUIDByNumber( $_GET['APP_NUMBER'] );
        //Get the index related to the userLogged but this thread can be OPEN or CLOSED
        $_GET['DEL_INDEX'] = $caseInstance->getCurrentDelegation($_GET['APP_UID'], $_SESSION['USER_LOGGED']);

        //if the application doesn't exist
        if (is_null($_GET['APP_UID'])) {
            G::SendMessageText( G::LoadTranslation( 'ID_CASE_DOES_NOT_EXISTS' ), 'info' );
            G::header( 'location: casesListExtJs' );
            exit();
        }

        //if the application exists but the
        if (is_null($_GET['DEL_INDEX'])) {
            G::SendMessageText( G::LoadTranslation( 'ID_CASE_IS_CURRENTLY_WITH_ANOTHER_USER' ), 'info' );
            G::header( 'location: casesListExtJs' );
            exit();
        }
    }

    $appUid = $_GET['APP_UID'];
    $delIndex = $_GET['DEL_INDEX'];
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    //loading application data
    $fieldCase = $caseInstance->loadCase($appUid, $delIndex);

    if (!isset($_SESSION['CURRENT_TASK'])) {
        $_SESSION['CURRENT_TASK'] = $fieldCase['TAS_UID'];
    } elseif ($_SESSION['CURRENT_TASK'] == '') {
        $_SESSION['CURRENT_TASK'] = $fieldCase['TAS_UID'];
    }

    unset($_SESSION['ACTION']);
    $flagJump = '';
    if ($action == 'jump') {
        $_SESSION['ACTION'] = 'jump';
        $flagJump = 1;
    }

    switch ($fieldCase['APP_STATUS']) {
        case 'DRAFT':
        case 'TO_DO':
            //Check if the case is in pause, check a valid record in table APP_DELAY
            $isPaused = AppDelay::isPaused($appUid, $delIndex);

            //Check if the case is a waiting for a SYNCHRONOUS subprocess
            $subAppData = new SubApplication();
            $caseSubprocessPending = $subAppData->isSubProcessWithCasePending($appUid, $delIndex);

            if ($isPaused || $caseSubprocessPending) {
                //the case is paused show only the resume
                $_SESSION['APPLICATION'] = $appUid;
                $_SESSION['INDEX'] = $delIndex;
                $_SESSION['PROCESS'] = $fieldCase['PRO_UID'];
                $_SESSION['TASK'] = -1;
                $_SESSION['STEP_POSITION'] = 0;
                $_SESSION['CURRENT_TASK'] = $fieldCase['TAS_UID'];

                require_once(PATH_METHODS . 'cases' . PATH_SEP . 'cases_Resume.php');
                exit();
            }

            /**
             * these routine is to verify if the case was acceded from advanced search list
             */
            if ($action == 'search') {
                //verify if the case is with the current user
                $delegationUsers = AppDelegation::getCurrentUsers($appUid, $delIndex);
                if ($delegationUsers['USR_UID'] !== $_SESSION['USER_LOGGED'] && !empty($delegationUsers['USR_UID'])) {
                    //distinct "" for selfservice
                    //so we show just the resume
                    $_SESSION['alreadyDerivated'] = true;
                    $_SESSION['APPLICATION'] = $appUid;
                    $_SESSION['INDEX'] = $delIndex;
                    $_SESSION['PROCESS'] = $fieldCase['PRO_UID'];
                    $_SESSION['TASK'] = -1;
                    $_SESSION['STEP_POSITION'] = 0;

                    require_once(PATH_METHODS . 'cases' . PATH_SEP . 'cases_Resume.php');
                    exit();
                }

            }

            //Proceed and try to open the case
            $appDelegation = new AppDelegation();
            $delegationInfo = $appDelegation->load($appUid, $delIndex);

            //If there are no user in the delegation row, this case is in selfservice
            if (empty($delegationInfo['USR_UID'])) {
                $_SESSION['APPLICATION'] = $appUid;
                $_SESSION['INDEX'] = $delIndex;
                $_SESSION['PROCESS'] = $fieldCase['PRO_UID'];
                $_SESSION['TASK'] = -1;
                $_SESSION['STEP_POSITION'] = 0;
                $_SESSION['CURRENT_TASK'] = $fieldCase['TAS_UID'];

                //If the task is in the valid selfservice tasks for this user, then catch the case, else just view the resume
                if ($caseInstance->isSelfService($_SESSION['USER_LOGGED'], $fieldCase['TAS_UID'], $appUid)) {
                    require_once(PATH_METHODS . 'cases' . PATH_SEP . 'cases_CatchSelfService.php');
                } else {
                    require_once(PATH_METHODS . 'cases' . PATH_SEP . 'cases_Resume.php');
                }

                exit();
            }

            //If the current users is in the AppDelegation row and the thread is open will be open the case
            if (($delegationInfo['USR_UID'] == $_SESSION['USER_LOGGED'] && $delegationInfo['DEL_THREAD_STATUS'] === 'OPEN')
            && $action != 'sent')
            {
                $_SESSION['APPLICATION'] = $appUid;
                $_SESSION['INDEX'] = $delIndex;

                if (is_null($fieldCase['DEL_INIT_DATE'])) {
                    $caseInstance->setDelInitDate($appUid, $delIndex);
                    $fieldCase = $caseInstance->loadCase($appUid, $delIndex);
                }

                $_SESSION['PROCESS'] = $fieldCase['PRO_UID'];
                $_SESSION['TASK'] = $fieldCase['TAS_UID'];
                $_SESSION['STEP_POSITION'] = 0;

                /* Redirect to next step */
                unset($_SESSION['bNoShowSteps']);

                /** Execute a trigger when a case is open */
                $caseInstance->getExecuteTriggerProcess($appUid, 'OPEN');

                $nextStep = $caseInstance->getNextStep(
                    $_SESSION['PROCESS'],
                    $_SESSION['APPLICATION'],
                    $_SESSION['INDEX'],
                    $_SESSION['STEP_POSITION']
                );
                $pageOpenCase = $nextStep['PAGE'];

                G::header('location: ' . $pageOpenCase);

            } else {
                $_SESSION['APPLICATION'] = $appUid;
                $_SESSION['PROCESS'] = $fieldCase['PRO_UID'];
                $_SESSION['TASK'] = -1;
                $_SESSION['bNoShowSteps'] = 1;
                $_SESSION['STEP_POSITION'] = 0;

                //When the case have another user or current user doesn't have rights to this self-service,
                //Just view the case Resume
                if ($action === 'search' || $action === 'to_reassign') {
                    //We need to use the index sent with the corresponding record
                    $_SESSION['INDEX'] = $delIndex;
                } else {
                    //Get DEL_INDEX
                    $criteria = new Criteria('workflow');
                    $criteria->addSelectColumn(AppDelegationPeer::DEL_INDEX);
                    $criteria->add(AppDelegationPeer::APP_UID, $appUid);
                    $criteria->add(AppDelegationPeer::DEL_LAST_INDEX, 1);
                    $rs = AppDelegationPeer::doSelectRS($criteria);
                    $rs->setFetchmode(ResultSet::FETCHMODE_ASSOC);
                    $rs->next();
                    $row = $rs->getRow();
                    $_SESSION['INDEX'] = $row['DEL_INDEX'];
                }

                $fields = $caseInstance->loadCase($_SESSION['APPLICATION'], $_SESSION['INDEX'], $flagJump);
                $_SESSION['CURRENT_TASK'] = $fields['TAS_UID'];

                require_once(PATH_METHODS . 'cases' . PATH_SEP . 'cases_Resume.php');

            }
            break;
        default: //APP_STATUS IS COMPLETED OR CANCELLED
            $_SESSION['APPLICATION'] = $appUid;
            $_SESSION['INDEX'] = $caseInstance->getCurrentDelegationCase($_GET['APP_UID']);
            $_SESSION['PROCESS'] = $fieldCase['PRO_UID'];
            $_SESSION['TASK'] = -1;
            $_SESSION['STEP_POSITION'] = 0;
            $fields = $caseInstance->loadCase($_SESSION['APPLICATION'], $_SESSION['INDEX'], $flagJump);
            $_SESSION['CURRENT_TASK'] = $fields['TAS_UID'];

            require_once(PATH_METHODS . 'cases' . PATH_SEP . 'cases_Resume.php');
    }
} catch (Exception $e) {
    $message = [];
    $message['MESSAGE'] = $e->getMessage();
    $G_PUBLISH = new Publisher();
    $G_PUBLISH->AddContent('xmlform', 'xmlform', 'login/showMessage', '', $message);
    G::RenderPage('publishBlank', 'blank');
}

