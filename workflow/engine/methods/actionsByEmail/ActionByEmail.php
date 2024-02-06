<?php

use ProcessMaker\Exception\RBACException;

// Check if the current user have the correct permissions to access to this resource, if not throws a RBAC Exception with code 403
if ($RBAC->userCanAccess('PM_SETUP') !== 1 || $RBAC->userCanAccess('PM_SETUP_LOGS') !== 1) {
    throw new RBACException('ID_ACCESS_DENIED', 403);
}

$oHeadPublisher->addExtJsScript('actionsByEmail/report', false); //adding a javascript file .js

G::RenderPage('publish', 'extJs');
