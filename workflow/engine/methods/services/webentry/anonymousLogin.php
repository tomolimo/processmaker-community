<?php

/**
 * This service is to start PM with the anonymous user.
 */

/* @var $RBAC RBAC */
global $RBAC;
G::LoadClass('pmFunctions');
try {
    if (empty($_REQUEST['we_uid'])) {
        throw new Exception('Missing required field "we_uid"');
    }

    $weUid = $_REQUEST['we_uid'];

    $webEntry = WebEntryPeer::retrieveByPK($weUid);
    if (empty($webEntry)) {
        throw new Exception('Undefined WebEntry');
    }

    $userUid = $webEntry->getUsrUid();

    if (!empty($_SESSION['__WEBENTRYCONTINUE_USER_LOGGED__'])) {
        $userUid = $_SESSION['__WEBENTRYCONTINUE_USER_LOGGED__'];
        unset($_SESSION['__WEBENTRYCONTINUE_USER_LOGGED__']);
    }

    $userInfo = UsersPeer::retrieveByPK($userUid);
    if (empty($userInfo)) {
        throw new Exception('WebEntry User not found');
    }

    $_SESSION = [];
    initUserSession($userUid, $userInfo->getUsrUsername());

    $result = [
        'user_logged' => $userUid,
        'userName' => $userInfo->getUsrUsername(),
        'firstName' => $userInfo->getUsrFirstName(),
        'lastName' => $userInfo->getUsrLastName(),
        'mail' => $userInfo->getUsrEmail(),
        'image' => '../users/users_ViewPhoto?t=' . microtime(true),
    ];
} catch (Exception $e) {
    $result = [
        'error' => $e->getMessage(),
    ];
    http_response_code(500);
}
echo G::json_encode($result);
