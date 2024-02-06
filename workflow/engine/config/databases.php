<?php

if (defined('PATH_DB') && !empty(config("system.workspace"))) {

    if (!file_exists(PATH_DB . config("system.workspace") . '/db.php')) {
        throw new Exception("Could not find db.php in current workspace " . config("system.workspace"));
    }

    //These constants must not exist, they will be created by "db.php".
    $constants = [
        'DB_ADAPTER',
        'DB_HOST',
        'DB_NAME',
        'DB_USER',
        'DB_PASS',
        'DB_RBAC_HOST',
        'DB_RBAC_NAME',
        'DB_RBAC_USER',
        'DB_RBAC_PASS' ,
        'DB_REPORT_HOST',
        'DB_REPORT_NAME',
        'DB_REPORT_USER',
        'DB_REPORT_PASS',
    ];
    $load = true;
    foreach ($constants as $value) {
        if (defined($value)) {
            $load = false;
            break;
        }
    }
    if ($load === true) {
        require_once(PATH_DB . config("system.workspace") . '/db.php');
    }
    //to do: enable for other databases
    $dbType = DB_ADAPTER;
    $dsn = DB_ADAPTER . '://' . DB_USER . ':' . urlencode(DB_PASS) . '@' . DB_HOST . '/' . DB_NAME;

    //to do: enable a mechanism to select RBAC Database
    $dsnRbac = DB_ADAPTER . '://' . DB_RBAC_USER . ':' . urlencode(DB_RBAC_PASS) . '@' . DB_RBAC_HOST . '/' . DB_RBAC_NAME;

    //to do: enable a mechanism to select report Database
    $dsnReport = DB_ADAPTER . '://' . DB_REPORT_USER . ':' . urlencode(DB_REPORT_PASS) . '@' . DB_REPORT_HOST . '/' . DB_REPORT_NAME;

    switch (DB_ADAPTER) {
        case 'mysql':
            $dsn .= '?encoding=utf8';
            $dsnRbac .= '?encoding=utf8';
            $dsnReport .= '?encoding=utf8';
            break;
        case 'mssql':
        case 'sqlsrv':
            break;
        default:
            break;
    }

    $pro ['datasources']['workflow']['connection'] = $dsn;
    $pro ['datasources']['workflow']['adapter'] = DB_ADAPTER;

    $pro ['datasources']['rbac']['connection'] = $dsnRbac;
    $pro ['datasources']['rbac']['adapter'] = DB_ADAPTER;

    $pro ['datasources']['rp']['connection'] = $dsnReport;
    $pro ['datasources']['rp']['adapter'] = DB_ADAPTER;

    // "workflow" connection
    $dbHost = explode(':', DB_HOST);
    config(['database.connections.workflow.host' => $dbHost[0]]);
    config(['database.connections.workflow.database' => DB_NAME]);
    config(['database.connections.workflow.username' => DB_USER]);
    config(['database.connections.workflow.password' => DB_PASS]);
    if (count($dbHost) > 1) {
        config(['database.connections.workflow.port' => $dbHost[1]]);
    }

    // "rbac" connection
    $dbRbacHost = explode(':', DB_RBAC_HOST);
    config(['database.connections.rbac.driver' => DB_ADAPTER]);
    config(['database.connections.rbac.host' => $dbRbacHost[0]]);
    config(['database.connections.rbac.database' => DB_RBAC_NAME]);
    config(['database.connections.rbac.username' => DB_RBAC_USER]);
    config(['database.connections.rbac.password' => DB_RBAC_PASS]);
    if (count($dbRbacHost) > 1) {
        config(['database.connections.rbac.port' => $dbRbacHost[1]]);
    }

    // "rp" connection
    $dbReportHost = explode(':', DB_REPORT_HOST);
    config(['database.connections.rp.driver' => DB_ADAPTER]);
    config(['database.connections.rp.host' => $dbReportHost[0]]);
    config(['database.connections.rp.database' => DB_REPORT_NAME]);
    config(['database.connections.rp.username' => DB_REPORT_USER]);
    config(['database.connections.rp.password' => DB_REPORT_PASS]);
    if (count($dbReportHost) > 1) {
        config(['database.connections.rp.port' => $dbReportHost[1]]);
    }
}

$pro ['datasources']['dbarray']['connection'] = 'dbarray://user:pass@localhost/pm_os';
$pro ['datasources']['dbarray']['adapter'] = 'dbarray';

return $pro;
