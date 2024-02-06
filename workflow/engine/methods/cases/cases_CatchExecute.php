<?php
/**
 * cases_CatchExecute.php
 * This page is executed when we claim the case
 * 
 * @link https://wiki.processmaker.com/3.2/Cases/Cases#Unassigned
 */
if (!isset($_SESSION['USER_LOGGED'])) {
    $responseObject = new stdclass();
    $responseObject->error = G::LoadTranslation('ID_LOGIN_AGAIN');
    $responseObject->success = true;
    $responseObject->lostSession = true;
    print G::json_encode($responseObject);
    die();
}
/* Permissions */
switch ($RBAC->userCanAccess('PM_CASES')) {
    case - 2:
        G::SendTemporalMessage('ID_USER_HAVENT_RIGHTS_SYSTEM', 'error', 'labels');
        G::header('location: ../login/login');
        die();
        break;
    case - 1:
        G::SendTemporalMessage('ID_USER_HAVENT_RIGHTS_PAGE', 'error', 'labels');
        G::header('location: ../login/login');
        die();
        break;
}

if (isset($_POST['form']['BTN_CANCEL'])) {
    header("Location: ../cases/main");
    die();
}

/* Includes */

$case = new Cases();
$appUid = $_SESSION['APPLICATION'];
$delIndex = $_SESSION['INDEX'];

$appDelegation = new AppDelegation();
$delegation = $appDelegation->load($appUid, $delIndex);

// if there are no user in the delegation row, this case is still in selfservice
if ($delegation['USR_UID'] == "") {
    $case->setCatchUser($_SESSION['APPLICATION'], $_SESSION['INDEX'], $_SESSION['USER_LOGGED']);
    //changing email labels if the claim comes from gmail
    if (array_key_exists('gmail', $_SESSION) && $_SESSION['gmail'] == 1) {
        die('<script type="text/javascript">
        parent.document.getElementById("iframePM").setAttribute("src", "'.$_SESSION["server"].'cases/cases_Open?APP_UID=' . $_SESSION["APPLICATION"] . '&DEL_INDEX=' . $_SESSION["INDEX"] . '&action=unassigned");
        </script>');
    }
} else {
    $hideMessage = false;
    if (isset($_REQUEST['hideMessage'])) {
        $hideMessage = $_REQUEST['hideMessage'] === 'true' ? true : false;
    }
    if ($hideMessage === false) {
        G::SendMessageText(G::LoadTranslation('ID_CASE_ALREADY_DERIVATED'), 'error');
    }
}

$validation = (SYS_SKIN != 'uxs') ? 'true' : 'false';

unset($_SESSION['TASK']);

die('<script type="text/javascript">
  if (' . $validation . ') {
      if (window.parent.frames.length != 0) {
          parent.location = "open?APP_UID=' . $_SESSION['APPLICATION'] . '&DEL_INDEX=' . $_SESSION['INDEX'] . '&action=jump";
      } else {
          window.location = "../cases/cases_Open?APP_UID=' . $_SESSION['APPLICATION'] . '&DEL_INDEX=' . $_SESSION['INDEX'] . '&action=jump";
      }
  } else {
      window.location = "../cases/cases_Open?APP_UID=' . $_SESSION['APPLICATION'] . '&DEL_INDEX=' . $_SESSION['INDEX'] . '&action=jump";
  }
  </script>');
