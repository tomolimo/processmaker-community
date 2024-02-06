<?php

/**
 * This is an additional configuration to load all connections if exist in a particular process
 */

$dbHash = @explode(SYSTEM_HASH, G::decrypt(HASH_INSTALLATION, SYSTEM_HASH));

$host = $dbHash[0];
$user = $dbHash[1];
$pass = $dbHash[2];
$dbName = DB_NAME;

$pro = include(PATH_CORE . "config/databases.php");

$port = (!empty(config('database.connections.workflow.port'))) ? config('database.connections.workflow.port') : 3306;

$pro['datasources']['root'] = [];
$pro['datasources']['root']['connection'] = "mysql://$user:$pass@$host:$port/$dbName?encoding=utf8";
$pro['datasources']['root']['adapter'] = "mysql";

return $pro;
