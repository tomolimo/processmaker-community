<?php

/**
 * cron_single.php
 *
 * @see workflow/engine/bin/cron.php
 * @see workflow/engine/bin/timereventcron.php
 * @see workflow/engine/bin/ldapcron.php
 * @see workflow/engine/bin/sendnotificationscron.php
 * @see workflow/engine/bin/webentriescron.php
 * @see workflow/engine/methods/setup/cron.php
 * 
 * @link https://wiki.processmaker.com/3.2/Executing_cron.php
 */

use Illuminate\Foundation\Http\Kernel;

require_once __DIR__ . '/../../../gulliver/system/class.g.php';
require_once __DIR__ . '/../../../bootstrap/autoload.php';
require_once __DIR__ . '/../../../bootstrap/app.php';

use ProcessMaker\Core\JobsManager;
use ProcessMaker\Core\System;
use ProcessMaker\Plugins\PluginRegistry;
use ProcessMaker\TaskScheduler\Task;

register_shutdown_function(function () {
    if (class_exists("Propel")) {
        Propel::close();
    }
});

try {
    //Verify data
    if (count($argv) < 7) {
        throw new Exception('Error: Invalid number of arguments');
    }

    for ($i = 1; $i <= 3; $i++) {
        $argv[$i] = base64_decode($argv[$i]);

        if (!is_dir($argv[$i])) {
            throw new Exception('Error: The path "' . $argv[$i] . '" is invalid');
        }
    }

    //Set variables
    $osIsLinux = strtoupper(substr(PHP_OS, 0, 3)) != 'WIN';

    $pathHome = $argv[1];
    $pathTrunk = $argv[2];
    $pathOutTrunk = $argv[3];
    $cronName = $argv[4];
    $workspace = $argv[5];
    $now = $argv[6]; //date
    //asynchronous flag
    $asynchronous = false;
    $result = array_search('+async', $argv);
    if ($result !== false && is_int($result)) {
        $asynchronous = true;
        unset($argv[$result]);
        $argv = array_values($argv);
    }
    //Defines constants
    define('PATH_SEP', ($osIsLinux) ? '/' : '\\');

    define('PATH_HOME', $pathHome);
    define('PATH_TRUNK', $pathTrunk);
    define('PATH_OUTTRUNK', $pathOutTrunk);

    define('PATH_CLASSES', PATH_HOME . 'engine' . PATH_SEP . 'classes' . PATH_SEP);

    define('SYS_LANG', 'en');

    require_once(PATH_HOME . 'engine' . PATH_SEP . 'config' . PATH_SEP . 'paths.php');
    require_once(PATH_TRUNK . 'framework' . PATH_SEP . 'src' . PATH_SEP . 'Maveriks' . PATH_SEP . 'Util' . PATH_SEP . 'ClassLoader.php');

    // Class Loader - /ProcessMaker/BusinessModel
    $classLoader = \Maveriks\Util\ClassLoader::getInstance();
    $classLoader->add(PATH_TRUNK . 'framework' . PATH_SEP . 'src' . PATH_SEP, 'Maveriks');
    $classLoader->add(PATH_TRUNK . 'workflow' . PATH_SEP . 'engine' . PATH_SEP . 'src' . PATH_SEP, 'ProcessMaker');
    $classLoader->add(PATH_TRUNK . 'workflow' . PATH_SEP . 'engine' . PATH_SEP . 'src' . PATH_SEP);

    // Add vendors to autoloader
    $classLoader->addClass('Bootstrap', PATH_TRUNK . 'gulliver' . PATH_SEP . 'system' . PATH_SEP . 'class.bootstrap.php');
    $classLoader->addModelClassPath(PATH_TRUNK . 'workflow' . PATH_SEP . 'engine' . PATH_SEP . 'classes' . PATH_SEP . 'model' . PATH_SEP);

    // Get the configurations related to the workspace
    $arraySystemConfiguration = System::getSystemConfiguration('', '', $workspace);

    // Define the debug value
    $e_all = (defined('E_DEPRECATED')) ? E_ALL & ~E_DEPRECATED : E_ALL;
    $e_all = (defined('E_STRICT')) ? $e_all & ~E_STRICT : $e_all;
    $e_all = ($arraySystemConfiguration['debug']) ? $e_all : $e_all & ~E_NOTICE;

    // In community version the default value is 0
    $_SESSION['__SYSTEM_UTC_TIME_ZONE__'] = (int)($arraySystemConfiguration['system_utc_time_zone']) == 1;

    app()->useStoragePath(realpath(PATH_DATA));
    app()->make(Kernel::class)->bootstrap();
    restore_error_handler();

    // Do not change any of these settings directly, use env.ini instead
    ini_set('display_errors', $arraySystemConfiguration['debug']);
    ini_set('error_reporting', $e_all);
    ini_set('short_open_tag', 'On');
    ini_set('default_charset', 'UTF-8');
    ini_set('soap.wsdl_cache_enabled', $arraySystemConfiguration['wsdl_cache']);
    ini_set('date.timezone', $_SESSION['__SYSTEM_UTC_TIME_ZONE__'] ? 'UTC' : $arraySystemConfiguration['time_zone']);

    define('DEBUG_SQL_LOG', $arraySystemConfiguration['debug_sql']);
    define('DEBUG_TIME_LOG', $arraySystemConfiguration['debug_time']);
    define('DEBUG_CALENDAR_LOG', $arraySystemConfiguration['debug_calendar']);
    define('MEMCACHED_ENABLED', $arraySystemConfiguration['memcached']);
    define('MEMCACHED_SERVER', $arraySystemConfiguration['memcached_server']);
    define('TIME_ZONE', ini_get('date.timezone'));

    date_default_timezone_set(TIME_ZONE);

    config(['app.timezone' => TIME_ZONE]);

    spl_autoload_register(['Bootstrap', 'autoloadClass']);

    //Set variables

    $argvx = '';

    for ($i = 7; $i <= count($argv) - 1; $i++) {
            $argvx = $argvx . (($argvx != '') ? ' ' : '') . $argv[$i];
    }
    global $sObject;
    $sObject = $workspace;

    //Workflow
    saveLog('main', 'action', 'checking folder ' . PATH_DB . $workspace);

    if (is_dir(PATH_DB . $workspace) && file_exists(PATH_DB . $workspace . PATH_SEP . 'db.php')) {
        define('SYS_SYS', $workspace);
        config(["system.workspace" => $workspace]);

        include_once(PATH_HOME . 'engine' . PATH_SEP . 'config' . PATH_SEP . 'paths_installed.php');
        include_once(PATH_HOME . 'engine' . PATH_SEP . 'config' . PATH_SEP . 'paths.php');

        //PM Paths DATA
        define('PATH_DATA_SITE', PATH_DATA . 'sites/' . config("system.workspace") . '/');
        define('PATH_DOCUMENT', PATH_DATA_SITE . 'files/');
        define('PATH_DATA_MAILTEMPLATES', PATH_DATA_SITE . 'mailTemplates/');
        define('PATH_DATA_PUBLIC', PATH_DATA_SITE . 'public/');
        define('PATH_DATA_REPORTS', PATH_DATA_SITE . 'reports/');
        define('PATH_DYNAFORM', PATH_DATA_SITE . 'xmlForms/');
        define('PATH_IMAGES_ENVIRONMENT_FILES', PATH_DATA_SITE . 'usersFiles' . PATH_SEP);
        define('PATH_IMAGES_ENVIRONMENT_USERS', PATH_DATA_SITE . 'usersPhotographies' . PATH_SEP);

        if (is_file(PATH_DATA_SITE . PATH_SEP . '.server_info')) {
            $SERVER_INFO = file_get_contents(PATH_DATA_SITE . PATH_SEP . '.server_info');
            $SERVER_INFO = unserialize($SERVER_INFO);

            define('SERVER_NAME', $SERVER_INFO['SERVER_NAME']);
            define('SERVER_PORT', $SERVER_INFO['SERVER_PORT']);
            //to do improvement G::is_https()
            if ((isset($SERVER_INFO['HTTPS']) && $SERVER_INFO['HTTPS'] == 'on') ||
                    (isset($SERVER_INFO['HTTP_X_FORWARDED_PROTO']) && $SERVER_INFO['HTTP_X_FORWARDED_PROTO'] == 'https')) {
                define('REQUEST_SCHEME', 'https');
            } else {
                define('REQUEST_SCHEME', $SERVER_INFO['REQUEST_SCHEME']);
            }
        } else {
            eprintln('WARNING! No server info found!', 'red');
        }
        //load Processmaker translations
        Bootstrap::LoadTranslationObject(SYS_LANG);
        //DB
        $phpCode = '';

        $fileDb = fopen(PATH_DB . $workspace . PATH_SEP . 'db.php', 'r');

        if ($fileDb) {
            while (!feof($fileDb)) {
                $buffer = fgets($fileDb, 4096); //Read a line

                $phpCode .= preg_replace('/define\s*\(\s*[\x22\x27](.*)[\x22\x27]\s*,\s*(\x22.*\x22|\x27.*\x27)\s*\)\s*;/i', '$$1 = $2;', $buffer);
            }

            fclose($fileDb);
        }

        $phpCode = str_replace(['<?php', '<?', '?>'], ['', '', ''], $phpCode);

        eval($phpCode);

        $dsn = $DB_ADAPTER . '://' . $DB_USER . ':' . $DB_PASS . '@' . $DB_HOST . '/' . $DB_NAME;
        $dsnRbac = $DB_ADAPTER . '://' . $DB_RBAC_USER . ':' . $DB_RBAC_PASS . '@' . $DB_RBAC_HOST . '/' . $DB_RBAC_NAME;
        $dsnRp = $DB_ADAPTER . '://' . $DB_REPORT_USER . ':' . $DB_REPORT_PASS . '@' . $DB_REPORT_HOST . '/' . $DB_REPORT_NAME;

        switch ($DB_ADAPTER) {
            case 'mysql':
                $dsn .= '?encoding=utf8';
                $dsnRbac .= '?encoding=utf8';
                break;
            case 'mssql':
                break;
            default:
                break;
        }

        $pro = [];
        $pro['datasources']['workflow']['connection'] = $dsn;
        $pro['datasources']['workflow']['adapter'] = $DB_ADAPTER;
        $pro['datasources']['rbac']['connection'] = $dsnRbac;
        $pro['datasources']['rbac']['adapter'] = $DB_ADAPTER;
        $pro['datasources']['rp']['connection'] = $dsnRp;
        $pro['datasources']['rp']['adapter'] = $DB_ADAPTER;

        $oFile = fopen(PATH_CORE . 'config' . PATH_SEP . '_databases_.php', 'w');
        fwrite($oFile, '<?php global $pro; return $pro; ?>');
        fclose($oFile);

        Propel::init(PATH_CORE . 'config' . PATH_SEP . '_databases_.php');

        /**
         * Load Laravel database connection
         */
        $dbHost = explode(':', $DB_HOST);
        config(['database.connections.workflow.host' => $dbHost[0]]);
        config(['database.connections.workflow.database' => $DB_NAME]);
        config(['database.connections.workflow.username' => $DB_USER]);
        config(['database.connections.workflow.password' => $DB_PASS]);
        if (count($dbHost) > 1) {
            config(['database.connections.workflow.port' => $dbHost[1]]);
        }

        //Enable RBAC, We need to keep both variables in upper and lower case.
        $rbac = $RBAC = RBAC::getSingleton(PATH_DATA, session_id());
        $rbac->sSystem = 'PROCESSMAKER';

        if (!defined('DB_ADAPTER')) {
            define('DB_ADAPTER', $DB_ADAPTER);
        }
        if (!defined('DB_HOST')) {
            define('DB_HOST', $DB_HOST);
        }
        if (!defined('DB_NAME')) {
            define('DB_NAME', $DB_NAME);
        }
        if (!defined('DB_USER')) {
            define('DB_USER', $DB_USER);
        }
        if (!defined('DB_PASS')) {
            define('DB_PASS', $DB_PASS);
        }
        if (!defined('DB_RBAC_HOST')) {
            define('DB_RBAC_HOST', $DB_RBAC_HOST);
        }
        if (!defined('DB_RBAC_NAME')) {
            define('DB_RBAC_NAME', $DB_RBAC_NAME);
        }
        if (!defined('DB_RBAC_USER')) {
            define('DB_RBAC_USER', $DB_RBAC_USER);
        }
        if (!defined('DB_RBAC_PASS')) {
            define('DB_RBAC_PASS', $DB_RBAC_PASS);
        }
        if (!defined('DB_REPORT_HOST')) {
            define('DB_REPORT_HOST', $DB_REPORT_HOST);
        }
        if (!defined('DB_REPORT_NAME')) {
            define('DB_REPORT_NAME', $DB_REPORT_NAME);
        }
        if (!defined('DB_REPORT_USER')) {
            define('DB_REPORT_USER', $DB_REPORT_USER);
        }
        if (!defined('DB_REPORT_PASS')) {
            define('DB_REPORT_PASS', $DB_REPORT_PASS);
        }
        if (!defined('SYS_SKIN')) {
            define('SYS_SKIN', $arraySystemConfiguration['default_skin']);
        }

        $dateSystem = date('Y-m-d H:i:s');
        if (empty($now)) {
            $now = $dateSystem;
        }

        //Processing
        eprintln('Processing workspace: ' . $workspace, 'green');
        
        /**
         * JobsManager
         */
        JobsManager::getSingleton()->init();

        // We load plugins' pmFunctions
        $oPluginRegistry = PluginRegistry::loadSingleton();
        $oPluginRegistry->init();

        try {
            switch ($cronName) {
                case 'cron':
                    try {
                        $task = new Task($asynchronous, $sObject);
                        if (empty($argvx) || strpos($argvx, "emails") !== false) {
                            $task->resendEmails($now, $dateSystem);
                        }
                        if (empty($argvx) || strpos($argvx, "unpause") !== false) {
                            $task->unpauseApplications($now);
                        }
                        if (empty($argvx) || strpos($argvx, "calculate") !== false) {
                            $task->calculateDuration();
                        }
                        executeEvents();
                        executeScheduledCases();
                        executeUpdateAppTitle();
                        if (empty($argvx) || strpos($argvx, "unassigned-case") !== false) {
                            $task->executeCaseSelfService();
                        }
                        if (empty($argvx) || strpos($argvx, "clean-self-service-tables") !== false) {
                            $task->cleanSelfServiceTables();
                        }
                        if (empty($argvx) || strpos($argvx, "plugins") !== false) {
                            $task->executePlugins();
                        }
                    } catch (Exception $oError) {
                        saveLog("main", "error", "Error processing workspace : " . $oError->getMessage() . "\n");
                    }
                    break;
                case 'ldapcron':
                    $task = new Task($asynchronous, $sObject);
                    $task->ldapcron(in_array('+debug', $argv));
                    break;
                case 'messageeventcron':
                    $task = new Task($asynchronous, $sObject);
                    $task->messageeventcron();
                    break;
                case 'timereventcron':
                    $task = new Task($asynchronous, $sObject);
                    $task->timerEventCron($now, true);
                    break;
                case 'sendnotificationscron':
                    if (empty($argvx) || strpos($argvx, "send-notifications") !== false) {
                        $task = new Task($asynchronous, $sObject);
                        $task->sendNotifications();
                    }
                    break;
                case 'webentriescron':
                    $task = new Task($asynchronous, $sObject);
                    $task->webEntriesCron();
                    break;
            }
        } catch (Exception $e) {
            $token = strtotime("now");
            PMException::registerErrorLog($e, $token);
            G::outRes(G::LoadTranslation("ID_EXCEPTION_LOG_INTERFAZ", array($token)) . "\n");

            eprintln('Problem in workspace: ' . $workspace . ' it was omitted.', 'red');
        }

        eprintln();
    }

    if (file_exists(PATH_CORE . 'config' . PATH_SEP . '_databases_.php')) {
        unlink(PATH_CORE . 'config' . PATH_SEP . '_databases_.php');
    }
} catch (Exception $e) {
    $token = strtotime("now");
    PMException::registerErrorLog($e, $token);
    G::outRes(G::LoadTranslation("ID_EXCEPTION_LOG_INTERFAZ", array($token)) . "\n");
}

function executeEvents()
{
    global $sLastExecution;
    global $argvx;
    global $now;

    $log = array();

    if ($argvx != "" && strpos($argvx, "events") === false) {
        return false;
    }

    setExecutionMessage("Executing events");
    setExecutionResultMessage('PROCESSING');

    try {
        $oAppEvent = new AppEvent();
        saveLog('executeEvents', 'action', "Executing Events $sLastExecution, $now ");
        $n = $oAppEvent->executeEvents($now, false, $log, 1);

        foreach ($log as $value) {
            $arrayCron = unserialize(trim(@file_get_contents(PATH_DATA . "cron")));
            $arrayCron["processcTimeStart"] = time();
            @file_put_contents(PATH_DATA . "cron", serialize($arrayCron));

            saveLog('executeEvents', 'action', "Execute Events : $value, $now ");
        }

        setExecutionMessage("|- End Execution events");
        setExecutionResultMessage("Processed $n");
    } catch (Exception $oError) {
        setExecutionResultMessage('WITH ERRORS', 'error');
        eprintln("  '-" . $oError->getMessage(), 'red');
        saveLog('calculateAlertsDueDate', 'Error', 'Error Executing Events: ' . $oError->getMessage());
    }
}

function executeScheduledCases($now = null)
{
    try {
        global $argvx;
        global $now;
        $log = array();

        if ($argvx != "" && strpos($argvx, "scheduler") === false) {
            return false;
        }

        setExecutionMessage("Executing the scheduled starting cases");
        setExecutionResultMessage('PROCESSING');

        $oCaseScheduler = new CaseScheduler();
        $oCaseScheduler->caseSchedulerCron($now, $log, 1);

        foreach ($log as $value) {
            $arrayCron = unserialize(trim(@file_get_contents(PATH_DATA . "cron")));
            $arrayCron["processcTimeStart"] = time();
            @file_put_contents(PATH_DATA . "cron", serialize($arrayCron));

            saveLog('executeScheduledCases', 'action', "OK Case# $value");
        }

        setExecutionResultMessage('DONE');
    } catch (Exception $oError) {
        setExecutionResultMessage('WITH ERRORS', 'error');
        eprintln("  '-" . $oError->getMessage(), 'red');
    }
}

function executeUpdateAppTitle()
{
    try {
        global $argvx;

        if ($argvx != "" && strpos($argvx, "update-case-labels") === false) {
            return false;
        }

        $criteriaConf = new Criteria("workflow");

        $criteriaConf->addSelectColumn(ConfigurationPeer::OBJ_UID);
        $criteriaConf->addSelectColumn(ConfigurationPeer::CFG_VALUE);
        $criteriaConf->add(ConfigurationPeer::CFG_UID, "TAS_APP_TITLE_UPDATE");

        $rsCriteriaConf = ConfigurationPeer::doSelectRS($criteriaConf);
        $rsCriteriaConf->setFetchmode(ResultSet::FETCHMODE_ASSOC);

        setExecutionMessage("Update case labels");
        saveLog("updateCaseLabels", "action", "Update case labels", "c");

        while ($rsCriteriaConf->next()) {
            $row = $rsCriteriaConf->getRow();

            $taskUid = $row["OBJ_UID"];
            $lang = $row["CFG_VALUE"];

            //Update case labels
            $appcv = new AppCacheView();
            $appcv->appTitleByTaskCaseLabelUpdate($taskUid, $lang, 1);

            //Delete record
            $criteria = new Criteria("workflow");

            $criteria->add(ConfigurationPeer::CFG_UID, "TAS_APP_TITLE_UPDATE");
            $criteria->add(ConfigurationPeer::OBJ_UID, $taskUid);
            $criteria->add(ConfigurationPeer::CFG_VALUE, $lang);

            $numRowDeleted = ConfigurationPeer::doDelete($criteria);

            saveLog("updateCaseLabels", "action", "OK Task $taskUid");
        }

        setExecutionResultMessage("DONE");
    } catch (Exception $e) {
        setExecutionResultMessage("WITH ERRORS", "error");
        eprintln("  '-" . $e->getMessage(), "red");
        saveLog("updateCaseLabels", "error", "Error updating case labels: " . $e->getMessage());
    }
}

/**
 * @deprecated This function is only used in this file and must be deleted.
 * @global string $sObject
 * @global string $isDebug
 * @param string $sSource
 * @param string $sType
 * @param string $sDescription
 */
function saveLog($sSource, $sType, $sDescription)
{
    try {
        global $sObject;
        global $isDebug;

        if ($isDebug) {
            print date("H:i:s") . " ($sSource) $sType $sDescription <br />\n";
        }

        G::verifyPath(PATH_DATA . "log" . PATH_SEP, true);
        G::log("| $sObject | " . $sSource . " | $sType | " . $sDescription, PATH_DATA);
    } catch (Exception $e) {
        //CONTINUE
    }
}

/**
 * @deprecated This function is only used in this file and must be deleted.
 * @param string $m
 */
function setExecutionMessage($m)
{
    $len = strlen($m);
    $linesize = 60;
    $rOffset = $linesize - $len;

    eprint("* $m");

    for ($i = 0; $i < $rOffset; $i++) {
        eprint('.');
    }
}

/**
 * @deprecated This function is only used in this file and must be deleted.
 * @param string $m
 * @param string $t
 */
function setExecutionResultMessage($m, $t = '')
{
    $c = 'green';

    if ($t == 'error') {
        $c = 'red';
    }

    if ($t == 'info') {
        $c = 'yellow';
    }

    if ($t == 'warning') {
        $c = 'yellow';
    }

    eprintln("[$m]", $c);
}
