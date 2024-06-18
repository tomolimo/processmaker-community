<?php

use ProcessMaker\Core\System;

// Auto loader
require_once __DIR__ . '/../../../bootstrap/autoload.php';

// Initialize core
$app = new Maveriks\WebApplication();
$app->setRootDir(PROCESSMAKER_PATH);
$app->loadEnvironment('', true, false);

// Trap -V before pake
if (in_array('-v', $argv) || in_array('-V', $argv) || in_array('--version', $argv)) {
    printf("ProcessMaker version %s\n",
        pakeColor::colorize(trim(file_get_contents(PATH_GULLIVER . 'VERSION')), 'INFO'));
    exit(0);
}

// Register tasks
$directories = [PATH_HOME . 'engine/bin/tasks'];
$pluginsDirectories = glob(PATH_PLUGINS . "*");
foreach ($pluginsDirectories as $dir) {
    if (!is_dir($dir)) {
        continue;
    }
    if (is_dir("$dir/bin/tasks")) {
        $directories[] = "$dir/bin/tasks";
    }
}
foreach ($directories as $dir) {
    foreach (glob("$dir/*.php") as $filename) {
        include_once $filename;
    }
}

// Set time zone, if not defined
if (!defined('TIME_ZONE')) {
    // Get workspace
    $args = $argv;
    $cliName = array_shift($args);
    $taskName = array_shift($args);
    $workspace = array_shift($args);
    if (isset($workspace[0])) {
        while ($workspace[0] == '-') {
            $workspace = array_shift($args);
        }
    }

    // Get time zone (global or by workspace)
    $arraySystemConfiguration = System::getSystemConfiguration('', '', $workspace);

    // Set time zone
    $_SESSION['__SYSTEM_UTC_TIME_ZONE__'] = (int)($arraySystemConfiguration['system_utc_time_zone']) == 1;
    define('TIME_ZONE',
        (isset($_SESSION['__SYSTEM_UTC_TIME_ZONE__']) && $_SESSION['__SYSTEM_UTC_TIME_ZONE__']) ? 'UTC' : $arraySystemConfiguration['time_zone']);
    ini_set('date.timezone', TIME_ZONE);
    date_default_timezone_set(TIME_ZONE);
    config(['app.timezone' => TIME_ZONE]);
}

// Run command
CLI::run();
exit(0);
