<?php
/**
 * cases_Delete.php
 *
 * Delete case from the cases List option
 *
 * @link https://wiki.processmaker.com/3.3/Roles#PM_DELETECASE
 */

use ProcessMaker\BusinessModel\Cases;

switch ($RBAC->userCanAccess( 'PM_CASES' )) {
    case - 2:
        G::SendTemporalMessage( 'ID_USER_HAVENT_RIGHTS_SYSTEM', 'error', 'labels' );
        G::header( 'location: ../login/login' );
        die();
        break;
    case - 1:
        G::SendTemporalMessage( 'ID_USER_HAVENT_RIGHTS_PAGE', 'error', 'labels' );
        G::header( 'location: ../login/login' );
        die();
        break;
}

try {
    if (isset($_POST['APP_UIDS'])) {
        $cases = explode(',', $_POST['APP_UIDS']);
        foreach ($cases as $appUid) {
            $case = new Cases();
            $case->deleteCase($appUid, $_SESSION['USER_LOGGED']);
        }
        // Prepare the response successfully
        if (count($cases) == 1) {
            G::outRes(G::outRes(G::LoadTranslation('ID_CASE_DELETE_SUCCESFULLY')));
        } else {
            G::outRes(G::outRes(G::LoadTranslation('ID_CASES_DELETE_SUCCESFULLY')));
        }
    }
} catch (Exception $e) {
    $token = strtotime("now");
    PMException::registerErrorLog($e, $token);
    G::outRes($e->getMessage());
}

