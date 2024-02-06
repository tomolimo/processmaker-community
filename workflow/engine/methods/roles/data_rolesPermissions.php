<?php

$filter = new InputFilter();
$_GET = $filter->xssFilterHard($_GET);
$_REQUEST = $filter->xssFilterHard($_REQUEST);
$ROL_UID = $_GET['rUID'];
$TYPE_DATA = $_GET["type"];

global $RBAC;

$filter = (isset($_REQUEST['textFilter'])) ? $_REQUEST['textFilter'] : '';

//BUG 7554: erik/ hook for existents environments that have not PM_CANCELCASE
if ($RBAC->permissionsObj->loadByCode('PM_CANCELCASE') === false) {
    $RBAC->permissionsObj->create(array('PER_CODE' => 'PM_CANCELCASE', 'PER_CREATE_DATE' => date('Y-m-d H:i:s')
    ));
}

if ($TYPE_DATA == 'list') {
    $oDataset = $RBAC->getRolePermissions($ROL_UID, $filter, 1);
}
if ($TYPE_DATA == 'show') {
    $oDataset = $RBAC->getAllPermissions($ROL_UID, $RBAC->sSystem, $filter, 1);
}

$rows = [];
$rolesPermissions = new RolesPermissions();
$permissionsAdmin = $RBAC->loadPermissionAdmin();

while ($oDataset->next()) {
    $row = $oDataset->getRow();
    $rolesPermissions->setPerUid($row['PER_UID']);
    // Get permission name
    $row['PER_NAME'] = $rolesPermissions->getPermissionName();
    // Define permission type
    $row['TYPE'] = array_search($row['PER_UID'], array_column($permissionsAdmin, 'PER_UID')) !== false ? 'ADMIN' : 'CUSTOM';

    $rows[] = $row;
}

$result = [
    'permissions' => $rows
];
echo G::json_encode($result);
