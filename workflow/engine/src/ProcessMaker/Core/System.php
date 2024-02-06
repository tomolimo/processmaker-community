<?php

namespace ProcessMaker\Core;

use Configurations;
use DomDocument;
use Exception;
use Faker;
use G;
use GzipFile;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\Facades\DB;
use InputFilter;
use InstallerModule;
use Net;
use schema;
use WorkspaceTools;

class System
{
    public $sFilename;
    public $sFilesList;
    public $sUpgradeFileList;
    public $aErrors;
    public $aWorkspaces;
    public $sRevision;
    public $sPath;
    public $newSystemClass;
    private static $config = null;
    private static $debug = null;
    private static $instance;
    private static $defaultConfig = [
        'debug' => 0,
        'debug_sql' => 0,
        'debug_time' => 0,
        'debug_calendar' => 0,
        'wsdl_cache' => 1,
        'memory_limit' => "256M",
        'time_zone' => 'America/New_York',
        'expiration_year' => '1',
        'memcached' => 0,
        'memcached_server' => '',
        'default_skin' => 'neoclassic',
        'default_lang' => 'en',
        'proxy_host' => '',
        'proxy_port' => '',
        'proxy_user' => '',
        'proxy_pass' => '',
        'size_log_file' => 5000000,
        'number_log_file' => 5,
        'ie_cookie_lifetime' => 1,
        'safari_cookie_lifetime' => 1,
        'error_reporting' => "",
        'display_errors' => 'On',
        'enable_blacklist' => 0,
        'code_scanner_scope' => 'import_plugin,enable_plugin,import_process,trigger',
        'system_utc_time_zone' => 0,
        'server_protocol' => '',
        'leave_case_warning' => 0,
        'server_hostname_requests_frontend' => '',
        'load_headers_ie' => 0,
        'redirect_to_mobile' => 0,
        'disable_php_upload_execution' => 0,
        'disable_download_documents_session_validation' => 0,
        'logs_max_files' => 60,
        'logs_location' => '',
        'logging_level' => 'INFO',
        'smtp_timeout' => 20,
        'google_map_api_key' => '',
        'google_map_signature' => '',
        'upload_attempts_limit_per_user' => '60,1',
        'files_white_list' => '',
        'delay' => '0',
        'tries' => '10',
        'retry_after' => '90'
    ];

    /**
     * List currently installed plugins
     *
     * param
     *
     * @return array with the names of the plugins
     */
    public static function getPlugins()
    {
        $plugins = [];

        foreach (glob(PATH_PLUGINS . "*") as $filename) {
            $info = pathinfo($filename);
            if (array_key_exists("extension", $info) && (strcmp($info["extension"], "php") == 0)) {
                $plugins[] = basename($filename, ".php");
            }
        }

        sort($plugins, SORT_STRING);
        return $plugins;
    }

    /**
     * Lists existing workspaces, returning an array of workspaceTools object
     * for each.
     * This is a class method, it does not require an instance.
     *
     * @access public
     * @return array of workspace tools objects
     */
    public static function listWorkspaces()
    {
        $oDirectory = dir(PATH_DB);
        $aWorkspaces = [];
        foreach (glob(PATH_DB . "*") as $filename) {
            if (is_dir($filename) && file_exists($filename . "/db.php")) {
                $aWorkspaces[] = new WorkspaceTools(basename($filename));
            }
        }
        return $aWorkspaces;
    }

    /**
     * Get the ProcessMaker version.
     * If version-pmos.php is not found, try to
     * retrieve the version from git.
     *
     * @return string system
     */
    public static function getVersion()
    {
        if (!defined('PM_VERSION')) {
            if (file_exists(PATH_METHODS . 'login/version-pmos.php')) {
                include(PATH_METHODS . 'login/version-pmos.php');
            } else {
                $version = self::getVersionFromGit();
                if ($version === false) {
                    $version = 'Development Version';
                }
                define('PM_VERSION', $version);
            }
        }
        return PM_VERSION;
    }

    /**
     * Get the branch and tag information from a git repository.
     *
     * @return string branch and tag information
     */
    public static function getVersionFromGit($dir = null)
    {
        if ($dir == null) {
            $dir = PATH_TRUNK;
        }
        if (!file_exists("$dir/.git")) {
            return false;
        }
        if (exec("cd $dir && git branch --no-color 2> /dev/null | sed -e '/^[^*]/d' -e 's/^* \(.*\)$/(Branch \\1)/'", $target)) {
            //exec( "cd $dir && git describe", $target ); ??? <-- thi is returning "fatal: No names found, cannot describe anything." on apache error log file
            return implode(' ', $target);
        }
        return false;
    }

    /**
     * Get system information
     *
     *
     * @return array with system information
     */
    public static function getSysInfo()
    {
        $ipe = isset($_SERVER['SSH_CONNECTION']) ? explode(" ", $_SERVER['SSH_CONNECTION']) : [];

        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } else {
            $ip = getenv('REMOTE_ADDR');
        }

        /* For distros with the lsb_release, this returns a one-line description of
         * the distro name, such as "CentOS release 5.3 (Final)" or "Ubuntu 10.10"
         */
        $distro = '';
        if (file_exists("/dev/")) { //Windows does not have this folder
            $distro = exec("lsb_release -d -s 2> /dev/null");
        }

        /* For distros without lsb_release, we look for *release (such as
         * redhat-release, gentoo-release, SuSE-release, etc) or *version (such as
         * debian_version, slackware-version, etc)
         */
        if (empty($distro)) {
            foreach (glob("/etc/*release") as $filename) {
                $distro = trim(file_get_contents($filename));
                if (!empty($distro)) {
                    break;
                }
            }
            if (empty($distro)) {
                foreach (glob("/etc/*version") as $filename) {
                    $distro = trim(file_get_contents($filename));
                    if (!empty($distro)) {
                        break;
                    }
                }
            }
        }

        /* CentOS returns a string with quotes, remove them and append
         * the OS name (such as LINUX, WINNT, DARWIN, etc)
         */
        $distro = trim($distro, "\"") . " (" . PHP_OS . ")";

        $Fields = [];
        $Fields['SYSTEM'] = $distro;
        $Fields['PHP'] = phpversion();
        $Fields['PM_VERSION'] = self::getVersion();
        $Fields['SERVER_ADDR'] = isset($ipe[2]) ? $ipe[2] : ''; //lookup($ipe[2]);
        $Fields['IP'] = isset($ipe[0]) ? $ipe[0] : ''; //lookup($ipe[0]);


        $Fields['PLUGINS_LIST'] = System::getPlugins();

        return $Fields;
    }

    /**
     * Load the po files
     *
     * @return array $items
     */
    public static function listPoFiles()
    {
        $folders = glob(PATH_CORE . '/content/translations/*');

        $items = glob(PATH_CORE . '/content/translations/*.po');
        foreach ($folders as $folder) {
            if (is_dir($folder)) {
                $add = glob($folder . "/*.po");
                $items = array_merge($items, $add);
            }
        }

        return $items;
    }

    /**
     * This function checks files to do updated to pm
     *
     * @name verifyFileForUpgrade
     *
     * @return boolean
     */
    public function verifyFileForUpgrade()
    {

        $filter = new InputFilter();
        $upgradeFilename = isset($_FILES['form']['name']['UPGRADE_FILENAME']) ? $_FILES['form']['name']['UPGRADE_FILENAME'] : '';
        $tempFilename = isset($_FILES['form']['tmp_name']['UPGRADE_FILENAME']) ? $_FILES['form']['tmp_name']['UPGRADE_FILENAME'] : '';
        $this->sRevision = str_replace('.tar.gz', '', str_replace('pmos-patch-', '', $upgradeFilename));
        $sTemFilename = $tempFilename;
        $sTemFilename = $filter->xssFilterHard($sTemFilename, 'path');
        $pathFile = $filter->xssFilterHard(PATH_DATA . 'upgrade' . PATH_SEP . $upgradeFilename, 'path');
        $this->sFilename = $pathFile;
        $this->sPath = dirname($this->sFilename) . PATH_SEP;
        G::mk_dir(PATH_DATA . 'upgrade');
        if (!move_uploaded_file($sTemFilename, $this->sFilename)) {
            return false;
        }
        return true;
    }

    /**
     * This function gets files to do updated to pm
     *
     * @name getUpgradedFilesList
     * @return void
     * @throws Exception
     */
    public function getUpgradedFilesList()
    {
        $this->sFilesList = new GzipFile($this->sFilename);
        $this->sFilesList->set_options(array('basedir' => dirname($this->sFilename), 'overwrite' => 1
        ));
        $this->sFilesList->extract_files();
        if (count($this->sFilesList->error) > 0) {
            $msg = '';
            foreach ($this->sFilesList->error as $key => $val) {
                $msg .= $val . "\n";
            }
            throw new Exception($msg);
        }
        if (count($this->sFilesList->files) == 0) {
            throw new Exception('The uploaded file is an invalid patch file.');
        }
    }

    /**
     * This function checks to do updated for boot
     *
     * @name verifyForBootstrapUpgrade
     * @return boolean
     */
    public function verifyForBootstrapUpgrade()
    {
        foreach ($this->sFilesList->files as $sFile) {
            if (basename($sFile) == 'schema.xml') {
                $this->newSystemClass = $sFile;
                return true;
            }
        }
        return false;
    }

    /**
     * This function updates to the files
     *
     * @name upgrade
     *
     * @return object
     * @throws Exception
     */
    public function upgrade()
    {
        //get special files
        $sListFile = '';
        $sCheckListFile = '';
        $sPatchVersionFile = '';
        $sPoFile = '';
        $sSchemaFile = '';
        $sSchemaRBACFile = '';
        foreach ($this->sFilesList->files as $sFile) {
            if (basename($sFile) == 'schema.xml') {
                if (strpos($sFile, '/rbac/engine/') === false) {
                    $sOldSchema = '';
                    $sSchemaFile = $sFile;
                } else {
                    $sOldSchemaRBAC = '';
                    $sSchemaRBACFile = $sFile;
                }
            }

            //files.lst
            if (basename($sFile) == 'files.lst') {
                $this->sUpgradeFileList = $sFile;
            }

            //files.lst
            if (basename($sFile) == 'patch.version.txt') {
                $sPatchVersionFile = $sFile;
            }

            //files.rev.txt
            if (substr(basename($sFile), 0, 6) == 'files.' && substr(basename($sFile), -4) == '.txt') {
                $sCheckListFile = $sFile;
            }

            //po files
            $sExtension = substr($sFile, strrpos($sFile, '.') + 1, strlen($sFile));
            if ($sExtension == 'po') {
                $sPoFile = $sFile;
            }
        }

        $pmVersion = explode('-', self::getVersion());
        array_shift($pmVersion);
        $patchVersion = explode('-', $this->sRevision);

        if ($sPatchVersionFile != '' && file_exists($sPatchVersionFile)) {
            $this->sRevision = file_get_contents($sPatchVersionFile);
            $patchVersion = explode('-', $this->sRevision);
        }

        if (!file_exists(PATH_DATA . 'log' . PATH_SEP)) {
            G::mk_dir(PATH_DATA . 'log' . PATH_SEP);
        }

        //empty query log
        $sqlLog = PATH_DATA . 'log' . PATH_SEP . "query.log";
        $fp = fopen($sqlLog, "w+");
        fwrite($fp, "");
        fclose($fp);

        $aEnvironmentsUpdated = [];
        $aEnvironmentsDiff = [];
        $aErrors = [];

        //now will verify each folder and file has permissions to write and add files.
        if ($this->sUpgradeFileList != '') {
            $bCopySchema = true;
            $oFile = fopen($this->sUpgradeFileList, 'r');
            while ($sLine = trim(fgets($oFile))) {
                $sLine = substr($sLine, 1);
                $aAux = explode(PATH_SEP, $sLine);
                array_shift($aAux);
                $sFilePath = implode(PATH_SEP, $aAux);
                $targetFileName = PATH_TRUNK . $sFilePath;
                if (!is_dir($this->sPath . 'processmaker' . PATH_SEP . $sFilePath)) {
                    //if we are updating or deleting a file
                    if (file_exists($this->sPath . 'processmaker' . PATH_SEP . $sFilePath)) {
                        if (file_exists($targetFileName)) {
                            if (!is_writable($targetFileName)) {
                                throw (new Exception("File $targetFileName is not writable."));
                            }
                        } else {
                            //verify parent folder, and ask if that folder is writable
                            $auxDir = explode('/', $targetFileName);
                            array_pop($auxDir);
                            $parentDir = implode('/', $auxDir);
                            if (!is_dir($parentDir)) {
                                //throw (new Exception("File $parentDir is an invalid directory."));
                                G::mk_dir($parentDir);
                            }
                            if (!is_writable($parentDir)) {
                                throw (new Exception("Directory $parentDir is not writable."));
                            }
                        }
                    } else {
                        //delete unused files
                        if (file_exists($targetFileName) && !is_writable($targetFileName)) {
                            throw (new Exception("File $targetFileName is not writable."));
                        }
                    }
                } else {
                    $dirName = PATH_TRUNK . $sFilePath;
                    if ($dirName[strlen($dirName) - 1] == '/') {
                        $dirName = substr($dirName, 0, strlen($dirName) - 1);
                    }
                    $auxDir = explode('/', $dirName);
                    array_pop($auxDir);
                    $parentDir = implode('/', $auxDir);
                    if (file_exists($dirName)) {
                        if (is_writable($dirName)) {
                            //print "e. ok $dirName <br>";
                        } else {
                            throw (new Exception("$dirName  is not writable"));
                        }
                    } else {
                        if (is_writable($parentDir)) {
                            mkdir($dirName, 0777);
                        } else {
                            throw (new Exception("$dirName does not exist and parent folder $parentDir is not writable"));
                        }
                    }
                }
            }
        }

        //processing list file files.lst
        if ($this->sUpgradeFileList != '') {
            $bCopySchema = true;
            $oFile = fopen($this->sUpgradeFileList, 'r');
            while ($sLine = trim(fgets($oFile))) {
                $action = substr($sLine, 0, 1);
                $sLine = substr($sLine, 1);
                $aAux = explode(PATH_SEP, $sLine);
                array_shift($aAux);
                $sFilePath = implode(PATH_SEP, $aAux);
                $targetFileName = PATH_TRUNK . $sFilePath;
                if (strtoupper($action) != 'D') {
                    if (!is_dir($this->sPath . 'processmaker' . PATH_SEP . $sFilePath)) {
                        if (file_exists($this->sPath . 'processmaker' . PATH_SEP . $sFilePath)) {
                            if (strpos($sFilePath, 'schema.xml') !== false && $bCopySchema) {
                                $bCopySchema = false;
                                $sOldSchema = str_replace('schema.xml', 'schema_' . date('Ymd') . '.xml', PATH_TRUNK . $sFilePath);
                                $this->pm_copy(PATH_TRUNK . $sFilePath, $sOldSchema);
                            }
                            if (file_exists($targetFileName)) {
                                if (is_writable($targetFileName)) {
                                    $this->pm_copy($this->sPath . 'processmaker' . PATH_SEP . $sFilePath, $targetFileName);
                                    @chmod($targetFileName, 0666);
                                } else {
                                    throw (new Exception("Failed to open file: Permission denied in $targetFileName."));
                                }
                            } else {
                                $this->pm_copy($this->sPath . 'processmaker' . PATH_SEP . $sFilePath, $targetFileName);
                                @chmod($targetFileName, 0666);
                            }
                        } else {
                            //delete unused files
                            if (file_exists($targetFileName)) {
                                @unlink($targetFileName);
                            }
                        }
                    } else {
                        if (!file_exists(PATH_TRUNK . $sFilePath)) {
                            mkdir(PATH_TRUNK . $sFilePath, 0777);
                        }
                    }
                } elseif (file_exists(PATH_TRUNK . $sFilePath) && $sFilePath != 'workflow/engine/gulliver') {
                    @unlink(PATH_TRUNK . $sFilePath);
                }
            }
        }

        //end files copied.
        $missedFiles = '';
        $distinctFiles = '';
        $missed = 0;
        $distinct = 0;
        //checking files of this installation server with the files in Repository Code.
        if ($sCheckListFile != '') {
            $fp = fopen($sCheckListFile, 'r');
            while (!feof($fp)) {
                $line = explode(' ', fgets($fp));
                if (count($line) == 3) {
                    $file = PATH_TRUNK . trim($line[2]);
                    if (is_readable($file)) {
                        $size = sprintf("%07d", filesize($file));
                        $checksum = sprintf("%010u", G::encryptCrc32(file_get_contents($file)));
                        if (!($line[0] == $size && $line[1] == $checksum) && substr($file, -4) != '.xml') {
                            $distinctFiles .= $file . "\n";
                            $distinct++;
                        }
                    } else {
                        $missedFiles .= $file . "\n";
                        $missed++;
                    }
                }
            }
            fclose($fp);
        }

        if ($missed > 0) {
            $aErrors[] = "Warning: there are $missed missed files. ";
        }
        $aErrors[] = $missedFiles;

        if ($distinct > 0) {
            $aErrors[] = "Warning: there are $distinct files with differences. ";
            $aErrors[] = $distinctFiles;
        }

        //now include the files and classes needed for upgrade databases, dont move this files, because we
        //are getting the last files in this point.  Even the files was in the patch we will take the new ones.
        include PATH_METHODS . PATH_SEP . 'setup' . PATH_SEP . 'upgrade_RBAC.php';

        $bForceXml = true;
        $bParseSchema = true;
        $bParseSchemaRBAC = true;
        $oDirectory = dir(PATH_DB);

        //count db.php files ( workspaces )
        $aWorkspaces = [];
        while (($sObject = $oDirectory->read())) {
            if (is_dir(PATH_DB . $sObject) && substr($sObject, 0, 1) != '.' && file_exists(PATH_DB . $sObject . PATH_SEP . 'db.php')) {
                $aWorkspaces[] = $sObject;
            }
        }
        $aUpgradeData = [];
        $aUpgradeData['workspaces'] = $aWorkspaces;
        $aUpgradeData['wsQuantity'] = count($aWorkspaces);
        $aUpgradeData['sPoFile'] = $sPoFile;
        $aUpgradeData['bForceXmlPoFile'] = true;
        $aUpgradeData['sSchemaFile'] = $sSchemaFile;
        $aUpgradeData['sSchemaRBACFile'] = $sSchemaRBACFile;

        file_put_contents(PATH_DATA . 'log' . PATH_SEP . "upgrade.data.bin", serialize($aUpgradeData));

        $sSchemaFile = '';
        $sPoFile = '';
        $sSchemaRBACFile = '';

        $oDirectory = dir(PATH_DB);
        while (($sObject = $oDirectory->read())) {
            if (is_dir(PATH_DB . $sObject) && substr($sObject, 0, 1) != '.') {
                if (file_exists(PATH_DB . $sObject . PATH_SEP . 'db.php')) {
                    eval($this->getDatabaseCredentials(PATH_DB . $sObject . PATH_SEP . 'db.php'));
                }
                $aEnvironmentsUpdated[] = $sObject;
                $aEnvironmentsDiff[] = $sObject;
            }
        }
        $oDirectory->close();
        @unlink(PATH_CORE . 'config/_databases_.php');

        //clean up smarty directory
        $oDirectory = dir(PATH_SMARTY_C);
        while ($sFilename = $oDirectory->read()) {
            if (($sFilename != '.') && ($sFilename != '..')) {
                @unlink(PATH_SMARTY_C . PATH_SEP . $sFilename);
            }
        }


        $filter = new InputFilter();

        //clean up xmlform folders
        $sDir = PATH_C . 'xmlform';
        $sDir = $filter->xssFilterHard($sDir, 'path');
        if (file_exists($sDir) && is_dir($sDir)) {
            $oDirectory = dir($sDir);
            while ($sObjectName = $oDirectory->read()) {
                if (($sObjectName != '.') && ($sObjectName != '..')) {
                    if (is_dir($sDir . PATH_SEP . $sObjectName)) {
                        G::rm_dir($sDir . PATH_SEP . $sObjectName);
                    }
                }
            }
            $oDirectory->close();
        }

        //changing the PM_VERSION according the patch file name
        $oFile = fopen(PATH_METHODS . 'login/version-pmos.php', 'w+');
        if (isset($this->sRevision) && $this->sRevision != '') {
            fwrite($oFile, "<?php\n  define ( 'PM_VERSION' , str_replace ( ' ','',  '1.6-" . $this->sRevision . "' ));\n?>");
        } else {
            fwrite($oFile, "<?php\n  define ( 'PM_VERSION' , str_replace ( ' ','',  'unknow' ));\n?>");
        }
        fclose($oFile);
        $ver = explode("-", $this->sRevision);
        $this->aErrors = $aErrors;
        $this->aWorkspaces = $aWorkspaces;

        return $ver;
    }

    /**
     * This function does to clean up to the upgrate directory
     *
     * @name cleanupUpgradeDirectory
     *
     * @return array
     */
    public function cleanupUpgradeDirectory()
    {
        G::rm_dir(PATH_DATA . "upgrade" . PATH_SEP . "processmaker");
    }

    /**
     * This function creates a directory
     *
     *
     * @name pm_copy
     *
     * @param string $source
     * @param string $target
     * @return void
     */
    public function pm_copy($source, $target)
    {
        if (!is_dir(dirname($target))) {
            G::mk_dir(dirname($target));
        }
        if (!copy($source, $target)) {
            krumo($source);
            krumo($target);
        }
    }

    /**
     * This function gets info about db
     *
     * @name getDatabaseCredentials
     *
     * @param string $dbFile
     * @return string $sContent
     */
    public function getDatabaseCredentials($dbFile)
    {
        $sContent = file_get_contents($dbFile);
        $sContent = str_replace('<?php', '', $sContent);
        $sContent = str_replace('<?', '', $sContent);
        $sContent = str_replace('?>', '', $sContent);
        $sContent = str_replace('define', '', $sContent);
        $sContent = str_replace("('", '$', $sContent);
        $sContent = str_replace("',", '=', $sContent);
        $sContent = str_replace(");", ';', $sContent);
        return $sContent;
    }

    /**
     * Retrieves the system schema.
     *
     * @return schema content in an array
     */
    public static function getSystemSchema()
    {
        return System::getSchema(PATH_TRUNK . "workflow/engine/config/schema.xml");
    }

    /**
     * Retrieves the system schema rbac.
     *
     * @return schema content in an array
     */
    public static function getSystemSchemaRbac()
    {
        return System::getSchema(PATH_TRUNK . "rbac/engine/config/schema.xml");
    }

    /**
     * Retrieves the schema for a plugin.
     *
     * @param string $pluginName name of the plugin
     * @return $sContent
     */
    public static function getPluginSchema($pluginName)
    {

        $filter = new InputFilter();
        $pathFile = $filter->xssFilterHard(PATH_PLUGINS . $pluginName . "/config/schema.xml", 'path');
        if (file_exists($pathFile)) {
            return System::getSchema($pathFile);
        } else {
            return false;
        }
    }

    /**
     * Retrieves a schema array from a file.
     *
     * @param string $schemaFile schema filename
     *
     * @return array
     */
    public static function getSchema($schemaFile)
    {
        // This is the MySQL mapping that Propel uses (from MysqlPlatform.php)
        $mysqlTypes = [
            'NUMERIC' => 'DECIMAL',
            'LONGVARCHAR' => 'MEDIUMTEXT',
            'TIMESTAMP' => 'DATETIME',
            'BU_TIMESTAMP' => 'DATETIME',
            'BINARY' => 'BLOB',
            'VARBINARY' => 'MEDIUMBLOB',
            'LONGVARBINARY' => 'LONGBLOB',
            'BLOB' => 'LONGBLOB',
            'CLOB' => 'LONGTEXT',
            // This is not from Propel, but is required to get INT right
            'INTEGER' => 'INT'
        ];

        $schema = [];

        // Parse schema
        $xml = new DomDocument();
        $xml->load($schemaFile);

        // Build the schema array
        $tablesNodes = $xml->getElementsByTagName('table');
        foreach ($tablesNodes as $tableNode) {
            $primaryKeys = [];
            $tableName = $tableNode->getAttribute('name');
            $schema[$tableName] = [];
            $columnsNodes = $tableNode->getElementsByTagName('column');
            foreach ($columnsNodes as $columnNode) {
                $columnName = $columnNode->getAttribute('name');

                // Get the field type. Propel uses VARCHAR if nothing else is specified
                $type = $columnNode->hasAttribute('type') ? strtoupper($columnNode->getAttribute('type')) : "VARCHAR";

                // Convert type to MySQL type according to Propel
                if (array_key_exists($type, $mysqlTypes)) {
                    $type = $mysqlTypes[$type];
                }

                // Get "size" attribute
                $size = $columnNode->hasAttribute('size') ? $columnNode->getAttribute('size') : null;

                // Add default sizes from MySQL
                if ($type == "TINYINT" && !$size) {
                    $size = "4";
                }
                if ($type == "INT" && !$size) {
                    $size = "11";
                }

                if ($size) {
                    $type = "$type($size)";
                }

                // Get "required" attribute
                $required = $columnNode->hasAttribute('required') ? $columnNode->getAttribute('required') : null;

                // Convert $required to a bool
                $required = (in_array(strtolower($required), ['1', 'true']));
                $autoIncrement = $columnNode->hasAttribute('autoIncrement') ? $columnNode->hasAttribute('autoIncrement') : false;
                $unique = $columnNode->hasAttribute('unique') ? $columnNode->hasAttribute('unique') : false;
                $default = $columnNode->hasAttribute('default') ? $columnNode->getAttribute('default') : null;

                $primaryKey = $columnNode->hasAttribute('primaryKey') ? $columnNode->getAttribute('primaryKey') : null;

                // Convert $primaryKey to a bool
                $primaryKey = (in_array(strtolower($primaryKey), ['1', 'true']));
                if ($primaryKey) {
                    $primaryKeys[] = $columnName;
                }

                // Add the metadata from the column
                $schema[$tableName][$columnName] = [
                    'Field' => $columnName,
                    'Type' => $type,
                    'Null' => $required ? "NO" : "YES",
                    'Default' => $default,
                    'AutoIncrement' => $autoIncrement,
                    'Unique' => $unique,
                    'PrimaryKey' => $primaryKey
                ];
            }

            // Add primary keys to "indexes" list
            if (is_array($primaryKeys) && count($primaryKeys) > 0) {
                $schema[$tableName]['INDEXES']['PRIMARY'] = $primaryKeys;
            }

            // Search normal indexes
            $indexesNodes = $tableNode->getElementsByTagName('index');
            foreach ($indexesNodes as $indexNode) {
                $indexes = [];
                $indexesColumnsNodes = $indexNode->getElementsByTagName('index-column');
                foreach ($indexesColumnsNodes as $indexColumnNode) {
                    $indexes[] = $indexColumnNode->getAttribute('name');
                }
                $schema[$tableName]['INDEXES'][$indexNode->getAttribute('name')] = $indexes;
            }

            // Search fulltext indexes
            $fulltextNodes = $tableNode->getElementsByTagName('fulltext');
            foreach ($fulltextNodes as $fulltextNode) {
                $fulltextIndexes = [];
                $indexColumnsNodes = $fulltextNode->getElementsByTagName('index-column');
                foreach ($indexColumnsNodes as $indexColumnsNode) {
                    $fulltextIndexes[] = $indexColumnsNode->getAttribute('name');
                }
                $schema[$tableName]['FULLTEXT'][$fulltextNode->getAttribute('name')] = $fulltextIndexes;
            }
        }

        return $schema;
    }

    /**
     * Returns tables name without prefix RBAC
     *
     * @param array $aOldSchema original schema array
     * @return array with tablesToRename
     */
    public static function verifyRbacSchema($aOldSchema)
    {
        $aChanges = [];

        foreach ($aOldSchema as $sTableName => $aColumns) {
            if (substr($sTableName, 0, 4) != 'RBAC') {
                $aChanges[] = $sTableName;
            }
        }

        return $aChanges;
    }

    /**
     * Returns the difference between two schemas
     *
     * @param array $oldSchema original schema
     * @param array $newSchema new schema
     *
     * @return array with tablesToAdd, tablesToAlter, tablesWithNewIndex, tablesToAlterIndex, tablesWithNewFulltext and tablesToAlterFulltext
     */
    public static function compareSchema($oldSchema, $newSchema)
    {
        $changes = [
            'tablesToAdd' => [],
            'tablesToAlter' => [],
            'tablesWithNewIndex' => [],
            'tablesToAlterIndex' => [],
            'tablesWithNewFulltext' => [],
            'tablesToAlterFulltext' => []
        ];

        // New tables to create and alter
        foreach ($newSchema as $tableName => $columns) {
            if (!isset($oldSchema[$tableName])) {
                $changes['tablesToAdd'][$tableName] = $columns;
            } else {
                // Drop old columns
                foreach ($oldSchema[$tableName] as $columnName => $parameters) {
                    if (!isset($newSchema[$tableName][$columnName])) {
                        if (!isset($changes['tablesToAlter'][$tableName])) {
                            $changes['tablesToAlter'][$tableName] = ['DROP' => [], 'ADD' => [], 'CHANGE' => []];
                        }
                        $changes['tablesToAlter'][$tableName]['DROP'][$columnName] = $columnName;
                    }
                }

                // Create new columns
                foreach ($columns as $columnName => $parameters) {
                    if ($columnName != 'INDEXES' && $columnName != 'FULLTEXT') {
                        if (!isset($oldSchema[$tableName][$columnName])) {
                            // This column doesn't exist in old schema
                            if (!isset($changes['tablesToAlter'][$tableName])) {
                                $changes['tablesToAlter'][$tableName] = ['DROP' => [], 'ADD' => [], 'CHANGE' => []];
                            }
                            $changes['tablesToAlter'][$tableName]['ADD'][$columnName] = $parameters;
                        } else {
                            // The column exists
                            $newField = $newSchema[$tableName][$columnName];
                            $oldField = $oldSchema[$tableName][$columnName];
                            // Both are null, no change is required
                            if (!isset($newField['Default']) && !isset($oldField['Default'])) {
                                $changeDefaultAttr = false;
                                // One of them is null, change is required
                            }
                            if (!isset($newField['Default']) && isset($oldField['Default']) && $oldField['Default'] != '') {
                                $changeDefaultAttr = true;
                            }
                            if (isset($newField['Default']) && !isset($oldField['Default'])) {
                                $changeDefaultAttr = true;
                                // Both are defined and they are different.
                            }
                            if (isset($newField['Default']) && isset($oldField['Default'])) {
                                if ($newField['Default'] != $oldField['Default']) {
                                    $changeDefaultAttr = true;
                                } else {
                                    $changeDefaultAttr = false;
                                }
                            }
                            // Special cases, BLOB and TEXT columns cannot have DEFAULT values. http://dev.mysql.com/doc/refman/5.0/en/blob.html
                            if (in_array(strtolower($newField['Type']), ['text', 'mediumtext'])) {
                                $changeDefaultAttr = false;
                            }
                            // #1067 - Invalid default value for datetime field
                            if (in_array($newField['Type'], ['datetime']) && isset($newField['Default']) && $newField['Default'] == '') {
                                $changeDefaultAttr = false;
                            }

                            // #1067 - Invalid default value for int field
                            if (substr($newField['Type'], 0, 3) == "INT" && isset($newField['Default']) && $newField['Default'] == '') {
                                $changeDefaultAttr = false;
                            }

                            // If any difference exists, then insert the difference in "changes" variable
                            if (strcasecmp($newField['Field'], $oldField['Field']) !== 0 || strcasecmp($newField['Type'], $oldField['Type']) !== 0 || strcasecmp($newField['Null'], $oldField['Null']) !== 0 || $changeDefaultAttr) {
                                if (!isset($changes['tablesToAlter'][$tableName])) {
                                    $changes['tablesToAlter'][$tableName] = ['DROP' => [], 'ADD' => [], 'CHANGE' => []];
                                }
                                $changes['tablesToAlter'][$tableName]['CHANGE'][$columnName]['Field'] = $newField['Field'];
                                $changes['tablesToAlter'][$tableName]['CHANGE'][$columnName]['Type'] = $newField['Type'];
                                $changes['tablesToAlter'][$tableName]['CHANGE'][$columnName]['Null'] = $newField['Null'];
                                if (isset($newField['Default'])) {
                                    $changes['tablesToAlter'][$tableName]['CHANGE'][$columnName]['Default'] = $newField['Default'];
                                } else {
                                    $changes['tablesToAlter'][$tableName]['CHANGE'][$columnName]['Default'] = null;
                                }
                            }
                        }
                    }
                    // Only columns, no the indexes column
                }
                // Now check the normal indexes of the table
                if (isset($newSchema[$tableName]['INDEXES'])) {
                    foreach ($newSchema[$tableName]['INDEXES'] as $indexName => $indexFields) {
                        if (!isset($oldSchema[$tableName]['INDEXES'][$indexName])) {
                            if (!isset($changes['tablesWithNewIndex'][$tableName])) {
                                $changes['tablesWithNewIndex'][$tableName] = [];
                            }
                            $changes['tablesWithNewIndex'][$tableName][$indexName] = $indexFields;
                        } else {
                            if ($oldSchema[$tableName]['INDEXES'][$indexName] != $indexFields) {
                                if (!isset($changes['tablesToAlterIndex'][$tableName])) {
                                    $changes['tablesToAlterIndex'][$tableName] = [];
                                }
                                $changes['tablesToAlterIndex'][$tableName][$indexName] = $indexFields;
                            }
                        }
                    }
                }

                // Now check the "fulltext" indexes of the table
                if (isset($newSchema[$tableName]['FULLTEXT'])) {
                    foreach ($newSchema[$tableName]['FULLTEXT'] as $indexName => $indexFields) {
                        if (!isset($oldSchema[$tableName]['FULLTEXT'][$indexName])) {
                            if (!isset($changes['tablesWithNewFulltext'][$tableName])) {
                                $changes['tablesWithNewFulltext'][$tableName] = [];
                            }
                            $changes['tablesWithNewFulltext'][$tableName][$indexName] = $indexFields;
                        } else {
                            if ($oldSchema[$tableName]['FULLTEXT'][$indexName] != $indexFields) {
                                if (!isset($changes['tablesToAlterFulltext'][$tableName])) {
                                    $changes['tablesToAlterFulltext'][$tableName] = [];
                                }
                                $changes['tablesToAlterFulltext'][$tableName][$indexName] = $indexFields;
                            }
                        }
                    }
                }
            }
        }
        return $changes;
    }

    public static function getEmailConfiguration()
    {
        $emailServer = new \ProcessMaker\BusinessModel\EmailServer();

        $arrayEmailServerDefault = $emailServer->getEmailServerDefault();

        if (count($arrayEmailServerDefault) > 0) {
            $arrayDataEmailServerConfig = array(
                "MESS_ENGINE" => $arrayEmailServerDefault["MESS_ENGINE"],
                "MESS_SERVER" => $arrayEmailServerDefault["MESS_SERVER"],
                "MESS_PORT" => (int) ($arrayEmailServerDefault["MESS_PORT"]),
                "MESS_RAUTH" => (int) ($arrayEmailServerDefault["MESS_RAUTH"]),
                "MESS_ACCOUNT" => $arrayEmailServerDefault["MESS_ACCOUNT"],
                "MESS_PASSWORD" => $arrayEmailServerDefault["MESS_PASSWORD"],
                "MESS_FROM_MAIL" => $arrayEmailServerDefault["MESS_FROM_MAIL"],
                "MESS_FROM_NAME" => $arrayEmailServerDefault["MESS_FROM_NAME"],
                "SMTPSecure" => $arrayEmailServerDefault["SMTPSECURE"],
                "MESS_TRY_SEND_INMEDIATLY" => (int) ($arrayEmailServerDefault["MESS_TRY_SEND_INMEDIATLY"]),
                "MAIL_TO" => $arrayEmailServerDefault["MAIL_TO"],
                "MESS_DEFAULT" => (int) ($arrayEmailServerDefault["MESS_DEFAULT"]),
                "MESS_ENABLED" => 1,
                "MESS_BACKGROUND" => "",
                "MESS_PASSWORD_HIDDEN" => "",
                "MESS_EXECUTE_EVERY" => "",
                "MESS_SEND_MAX" => ""
            );

            //Return
            return $arrayDataEmailServerConfig;
        } else {

            $conf = new Configurations();
            $config = $conf->load("Emails");

            return $config;
        }
    }

    /**
     * Get the list of skins
     *
     * @return array $skinListArray
     */
    public static function getSkingList()
    {
        //Create Skins custom folder if it doesn't exists
        if (!is_dir(PATH_CUSTOM_SKINS)) {
            G::verifyPath(PATH_CUSTOM_SKINS, true);
        }

        //Get Skin Config files
        $skinListArray = [];
        $customSkins = glob(PATH_CUSTOM_SKINS . "*/config.xml");

        if (!is_array($customSkins)) {
            $customSkins = [];
        }

        // getting al base skins
        $baseSkins = glob(G::ExpandPath("skinEngine") . '*/config.xml');

        // filtering no public skins (uxs, simplified)
        foreach ($baseSkins as $i => $skinName) {
            if (strpos($skinName, 'simplified') !== false || strpos($skinName, 'uxs') !== false || strpos($skinName, 'uxmodern') !== false) {
                unset($baseSkins[$i]);
            }
        }

        $customSkins = array_merge($baseSkins, $customSkins);
        $global = G::LoadTranslation('ID_GLOBAL');

        //Read and parse each Configuration File
        foreach ($customSkins as $key => $configInformation) {
            $folderId = basename(dirname($configInformation));

            if ($folderId == 'base') {
                $folderId = 'classic';
            }

            $partnerFlag = (defined('PARTNER_FLAG')) ? PARTNER_FLAG : false;
            if ($partnerFlag && ($folderId == 'classic')) {
                continue;
            }

            $xmlConfiguration = file_get_contents($configInformation);
            $xmlConfigurationObj = G::xmlParser($xmlConfiguration);

            if (isset($xmlConfigurationObj->result['skinConfiguration'])) {
                $skinInformationArray = $skinFilesArray = $xmlConfigurationObj->result['skinConfiguration']['__CONTENT__']['information']['__CONTENT__'];
                $res = [];
                $res['SKIN_FOLDER_ID'] = strtolower($folderId);

                foreach ($skinInformationArray as $keyInfo => $infoValue) {
                    $res['SKIN_' . strtoupper($keyInfo)] = (isset($infoValue['__VALUE__'])) ? $infoValue['__VALUE__'] : '';
                }
                $res['SKIN_CREATEDATE'] = (isset($res['SKIN_CREATEDATE'])) ? $res['SKIN_CREATEDATE'] : '';
                $res['SKIN_MODIFIEDDATE'] = (isset($res['SKIN_MODIFIEDDATE'])) ? $res['SKIN_MODIFIEDDATE'] : '';

                $res['SKIN_TYPE_GLOBAL'] = false;
                if (isset($res['SKIN_WORKSPACE'])) {
                    if ($res['SKIN_WORKSPACE'] === '') {
                        $res['SKIN_WORKSPACE'] = $global;
                        $res['SKIN_TYPE_GLOBAL'] = true;
                    }
                } else {
                    $res['SKIN_WORKSPACE'] = $global;
                    $res['SKIN_TYPE_GLOBAL'] = true;
                }

                $swWS = true;
                if ($res['SKIN_WORKSPACE'] != $global) {
                    $workspace = explode("|", $res['SKIN_WORKSPACE']);
                    $swWS = false;
                    foreach ($workspace as $key => $value) {
                        if ($value == config("system.workspace")) {
                            $swWS = true;
                            break;
                        }
                    }
                }
                if ($swWS) {
                    $skinListArray['skins'][] = $res;
                }
            }
        }

        $skinListArray['currentSkin'] = SYS_SKIN;

        return $skinListArray;
    }

    /**
     * Get all time zones
     *
     * @return array $skinListArray
     * @throws Exception
     * @deprecated this method is deprecated
     */
    public function getAllTimeZones()
    {
        throw new Exception(__METHOD__ . ': The method is deprecated');
    }

    /**
     * Get the system configuration
     *
     * @return array $config
     */
    public static function getSystemConfiguration($globalIniFile = '', $wsIniFile = '', $wsName = '')
    {
        if (!is_null(self::$config)) {
            return self::$config;
        }

        if (empty($globalIniFile)) {
            $globalIniFile = PATH_CORE . 'config' . PATH_SEP . 'env.ini';
        }

        if (empty($wsIniFile)) {

            if (defined('PATH_DB')) {
                // if we're on a valid workspace env.
                if (empty($wsName)) {
                    try {
                        if (function_exists('config')) {
                            $wsName = config("system.workspace");
                        }
                    } catch (Exception $exception) {
                        $wsName = '';
                    }
                    if (empty($wsName)) {
                        $uriParts = explode('/', getenv("REQUEST_URI"));
                        if (isset($uriParts[1])) {
                            if (substr($uriParts[1], 0, 3) === 'sys') {
                                $wsName = substr($uriParts[1], 3);
                            }
                        }
                    }
                }
                $wsIniFile = PATH_DB . $wsName . PATH_SEP . 'env.ini';
            }
        }

        // default configuration for "error_reporting" conf
        if (empty(self::$defaultConfig["error_reporting"])) {
            $errorReportingDefault = defined('E_DEPRECATED') ? E_ALL & ~E_DEPRECATED : E_ALL;
            $errorReportingDefault = defined('E_STRICT') ? $errorReportingDefault & ~E_STRICT : $errorReportingDefault;
            self::$defaultConfig["error_reporting"] = $errorReportingDefault;
        }

        $config = self::$defaultConfig;

        // read the global env.ini configuration file
        if (($globalConf = @parse_ini_file($globalIniFile)) !== false) {
            $config = array_merge($config, $globalConf);
        }

        // Workspace environment configuration
        if (file_exists($wsIniFile)) {
            if (($wsConf = @parse_ini_file($wsIniFile)) !== false) {
                $config = array_merge($config, $wsConf);
            }
        }

        // validation debug config, only binary value is valid; debug = 1, to enable
        $config['debug'] = $config['debug'] == 1 ? 1 : 0;
        self::$debug = $config['debug'];

        if ($config['proxy_pass'] != '') {
            $config['proxy_pass'] = G::decrypt($config['proxy_pass'], 'proxy_pass');
        }

        return $config;
    }

    /**
     * Get information about the queries permitted and tables we can modified
     * @access public
     * @param string $globalIniFile
     * @return array of execute query Black list
     */
    public static function getQueryBlackList($globalIniFile = '')
    {
        $config = [];
        if (empty($globalIniFile)) {
            $blackListIniFile = PATH_CONFIG . 'execute-query-blacklist.ini';
            $sysTablesIniFile = PATH_CONFIG . 'system-tables.ini';
        }
        // read the global execute-query-blacklist.ini configuration file
        if (file_exists($blackListIniFile)) {
            $config = @parse_ini_file($blackListIniFile);
        }
        if (file_exists($sysTablesIniFile)) {
            $systemTables = @parse_ini_file($sysTablesIniFile);
            $config['tables'] = $systemTables['tables'];
        }
        return $config;
    }

    /**
     * Update index file
     *
     * @param string $conf
     * @return string $result
     * @throws Exception
     */
    public static function updateIndexFile($conf)
    {
        if (!file_exists(PATH_HTML . 'index.html')) {
            throw new Exception('The public index file "' . PATH_HTML . 'index.html" does not exist!');
        } else {
            if (!is_writable(PATH_HTML . 'index.html')) {
                throw new Exception('The index.html file is not writable on workflow/public_html directory.');
            }
        }

        $content = file_get_contents(PATH_HTML . 'index.html');
        $result = false;

        $patt = '/<meta\s+http\-equiv="REFRESH"\s+content=\"0;URL=(.+)\"\s*\/>/';

        @preg_match($patt, $content, $match);

        if (is_array($match) && count($match) > 0 && isset($match[1])) {
            $newUrl = "sys/" . (($conf["lang"] != "") ? $conf["lang"] : ((defined("SYS_LANG") && SYS_LANG != "") ? SYS_LANG : "en")) . "/" . $conf["skin"] . "/login/login";

            $newMetaStr = str_replace($match[1], $newUrl, $match[0]);
            $newContent = str_replace($match[0], $newMetaStr, $content);

            $result = (@file_put_contents(PATH_HTML . 'index.html', $newContent) !== false);
        }

        return $result;
    }

    /**
     * Get the system configuration
     *
     * @param string $sysName
     * @return array $config
     */
    public static function solrEnv($sysName = '')
    {
        if (empty($sysName)) {
            $conf = System::getSystemConfiguration();
        } else {
            $conf = System::getSystemConfiguration('', '', $sysName);
        }

        if (!isset($conf['solr_enabled']) || !isset($conf['solr_host']) || !isset($conf['solr_instance'])) {
            return false;
        }

        if ($conf['solr_enabled']) {
            return array('solr_enabled' => $conf['solr_enabled'], 'solr_host' => $conf['solr_host'], 'solr_instance' => $conf['solr_instance']
            );
        }

        return false;
    }

    /**
     * Get the instance of the class
     *
     * @return array $instance
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new System();
        }

        return self::$instance;
    }

    /**
     * Get if is debug mode
     *
     * @return array $debug
     */
    public static function isDebugMode()
    {
        if (is_null(self::$debug)) {
            self::getSystemConfiguration();
        }

        return self::$debug;
    }

    /**
     * Get the complete name of the server host configured for requests Front-End (e.g. https://127.0.0.1:81)
     *
     * @return string Returns an string with the complete name of the server host configured for requests Front-End
     * @throws Exception
     */
    public static function getHttpServerHostnameRequestsFrontEnd()
    {
        try {
            $arraySystemConfiguration = self::getSystemConfiguration();

            $serverProtocol = $arraySystemConfiguration['server_protocol'];
            $serverProtocol = ($serverProtocol != '') ? $serverProtocol : ((G::is_https()) ? 'https' : 'http');

            $serverHostname = $arraySystemConfiguration['server_hostname_requests_frontend'];
            $serverHostname = ($serverHostname != '') ? $serverHostname : System::getServerHost();

            //Return
            return $serverProtocol . '://' . $serverHostname;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Return version server software Apache/Nginx
     *
     * @return string version server software Apache/Nginx
     */
    public static function getServerVersion()
    {
        $serverVersion = 'Undetermined';

        if (stripos($_SERVER['SERVER_SOFTWARE'], 'apache') !== false) {
            $serverVersion = function_exists('apache_get_version') ? apache_get_version() : $_SERVER['SERVER_SOFTWARE'];
        }

        if (stripos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false) {
            $serverVersion = $_SERVER['SERVER_SOFTWARE'];
        }

        return $serverVersion;
    }

    /**
     * Generate user name for test
     *
     * @param int $length
     * @return string
     */
    public static function generateUserName($length = 10)
    {
        $userName = 'PM_';
        for ($i = 0; $i < $length - 3; $i++) {
            $userName .= ($i % 3) === 0 ? '?' : '#';
        }
        $faker = Faker\Factory::create();
        return $faker->bothify($userName);
    }

    /**
     * Check permission the user in db
     *
     * @param string $adapter
     * @param string $serverName
     * @param int $port
     * @param string $userName
     * @param string $pass
     * @param string $dbName
     *
     * @return array
     */
    public static function checkPermissionsDbUser($adapter = 'mysql', $serverName, $port = 3306, $userName, $pass, $dbName = '')
    {
        if (empty($port)) {
            //setting defaults ports
            switch ($adapter) {
                case 'mysql':
                    $port = 3306;
                    break;
                case 'pgsql':
                    $port = 5432;
                    break;
                case 'mssql':
                    $port = 1433;
                    break;
                case 'oracle':
                    $port = 1521;
                    break;
            }
        }

        $filter = new InputFilter();
        $serverName = $filter->validateInput($serverName);
        $userName = $filter->validateInput($userName);

        $serverNet = new Net($serverName);
        if ($serverNet->getErrno() !== 0) {
            return [false, $serverNet->error];
        }
        $serverNet->scannPort($port);
        if ($serverNet->getErrno() !== 0) {
            return [false, $serverNet->error];
        }
        $serverNet->loginDbServer($userName, $pass);
        $serverNet->setDataBase('', $port);
        if ($serverNet->getErrno() !== 0) {
            return [false, $serverNet->error];
        }

        $response = $serverNet->tryConnectServer($adapter);
        if (!empty($response) && $response->status !== 'SUCCESS' && $serverNet->getErrno() !== 0) {
            return [false, $serverNet->error];
        }

        $message = '';
        $success = false;

        $userName = $filter->validateInput($userName, 'nosql');
        try {
            $connection = 'SYSTEM';
            InstallerModule::setNewConnection($connection, $serverName, $userName, $pass, $dbName, $port);

            //Test Create Database
            $dbNameTest = 'PROCESSMAKERTESTDC';
            $result = DB::connection($connection)->statement("CREATE DATABASE $dbNameTest");
            if ($result) {
                //Test set permissions user
                $usrTest = self::generateUserName(strlen($userName));
                $passTest = '!Sample123_';
                $result = DB::connection($connection)->statement("GRANT ALL PRIVILEGES ON `$dbNameTest`.* TO $usrTest@'%%' IDENTIFIED BY '$passTest' WITH GRANT OPTION");

                if ($result) {
                    //Test Create user
                    $userTestCreate = self::generateUserName(strlen($userName));
                    $result = DB::connection($connection)->statement("CREATE USER '$userTestCreate'@'%%' IDENTIFIED BY '$passTest'");

                    if ($result) {
                        $success = true;
                        $message = G::LoadTranslation('ID_SUCCESSFUL_CONNECTION');
                    }

                    DB::connection($connection)->statement("DROP USER '$userTestCreate'@'%%'");
                    DB::connection($connection)->statement("DROP USER '$usrTest'@'%%'");
                }
                DB::connection($connection)->statement("DROP DATABASE $dbNameTest");
            }
        } catch (Exception $exception) {
            $success = false;
            $message = $exception->getMessage();
        }

        return [$success, !empty($message) ? $message : $serverNet->error];
    }

    /**
     * Regenerate credentials paths installed
     *
     * @param string $host
     * @param string $user
     * @param string $pass
     * @return bool
     */
    public static function regenerateCredentiaslPathInstalled($host, $user, $pass)
    {
        $hashOld = G::encryptOld(filemtime(PATH_GULLIVER . "/class.g.php"));
        $hash = G::encrypt($host . $hashOld . $user . $hashOld . $pass . $hashOld . (1), $hashOld);
        $insertStatements = "define ( 'HASH_INSTALLATION','{$hash}' );  \ndefine ( 'SYSTEM_HASH', '{$hashOld}' ); \n";
        $content = '';
        $filename = PATH_HOME . 'engine' . PATH_SEP . 'config' . PATH_SEP . 'paths_installed.php';
        $lines = file($filename);

        $count = 1;
        foreach ($lines as $line_num => $line) {
            $pos = strpos($line, 'define');
            if ($pos !== false && $count < 3) {
                $content .= $line;
                $count++;
            }
        }
        $content = "<?php \n" . $content . "\n" . $insertStatements . "\n";
        return file_put_contents($filename, $content) !== false;
    }

    /**
     * Set Connection Configuration using "config" helper from "Laravel" with 
     * the constants defined in file "db.php"
     */
    public static function setConnectionConfig(
            $dbAdapter,
            $dbHost,
            $dbName,
            $dbUser,
            $dbPass,
            $dbRbacHost,
            $dbRbacName,
            $dbRbacUser,
            $dbRbacPass,
            $dbReportHost,
            $dbReportName,
            $dbReportUser,
            $dbReportPass)
    {
        config(['connections.driver' => $dbAdapter]);
        config(['connections.workflow.host' => $dbHost]);
        config(['connections.workflow.database' => $dbName]);
        config(['connections.workflow.username' => $dbUser]);
        config(['connections.workflow.password' => $dbPass]);
        config(['connections.rbac.host' => $dbRbacHost]);
        config(['connections.rbac.database' => $dbRbacName]);
        config(['connections.rbac.username' => $dbRbacUser]);
        config(['connections.rbac.password' => $dbRbacPass]);
        config(['connections.report.host' => $dbReportHost]);
        config(['connections.report.database' => $dbReportName]);
        config(['connections.report.username' => $dbReportUser]);
        config(['connections.report.password' => $dbReportPass]);
    }

    /**
     * Get current server protocol.
     * 
     * @return string
     */
    public static function getServerProtocol()
    {
        $envProtocol = defined("REQUEST_SCHEME") && REQUEST_SCHEME === "https";
        return G::is_https() || $envProtocol ? "https://" : "http://";
    }

    /**
     * Get current server host
     * 
     * @return string
     */
    public static function getServerHostname()
    {
        $host = "";
        if (!empty($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'];
        } else if (defined('SERVER_NAME')) {
            $host = SERVER_NAME;
        }
        return $host;
    }

    /**
     * Get current server port.
     * 
     * @return string
     */
    public static function getServerPort()
    {
        $port = "";
        if (isset($_SERVER['SERVER_PORT'])) {
            $port = $_SERVER['SERVER_PORT'];
        } else if (defined('SERVER_PORT')) {
            $port = SERVER_PORT;
        }
        return $port;
    }

    /**
     * Get current host (hostname + port).
     * 
     * @return string
     */
    public static function getServerHost()
    {
        $port = self::getServerPort();
        if (!empty($port) && $port != '80' && $port != '443') {
            return self::getServerHostname() . ':' . $port;
        }
        return self::getServerHostname();
    }

    /**
     * Get current server protocol and host.
     * 
     * @return string
     */
    public static function getServerProtocolHost()
    {
        return self::getServerProtocol() . self::getServerHost();
    }

    /**
     * Get server main path (protocol + host + port + workspace + lang + skin).
     * 
     * @return string
     * @see ProcessMaker\BusinessModel\ProjectUser->projectWsUserCanStartTask()
     * @see ProcessMaker\BusinessModel\ProjectUser->userLogin()
     * @see ProcessMaker\BusinessModel\WebEntry->getWebEntryDataFromRecord()
     * @see ProcessMaker\BusinessModel\WebEntryEvent->getGeneratedLink()
     * @see ProcessMaker\Core\System\ActionsByEmailCoreClass->sendActionsByEmail()
     * @see ProcessMaker\Core\System\webEntryProxy->checkCredentials()
     * @see ProcessMaker\Core\System\webEntryProxy->save()
     * @see workflow/engine/classes/ProcessMap.php ProcessMap->listNewWebEntry()
     * @see workflow/engine/classes/ProcessMap.php ProcessMap->webEntry()
     * @see workflow/engine/controllers/caseSchedulerProxy.php caseSchedulerProxy->checkCredentials()
     * @see workflow/engine/methods/cases/cases_SchedulerValidateUser.php 
     * @see workflow/engine/methods/processes/processes_webEntryGenerate.php 
     * @see workflow/engine/methods/processes/processes_webEntryValidate.php
     * @see workflow/engine/methods/processes/webEntry_Val_Assig.php
     */
    public static function getServerMainPath()
    {
        $config = self::getSystemConfiguration();
        $skin = defined("SYS_SKIN") ? SYS_SKIN : $config['default_skin'];
        return self::getServerProtocolHost() . '/sys' . config("system.workspace") . '/' . SYS_LANG . '/' . $skin;
    }

    /**
     * Get default domain mail.
     * 
     * @return string
     */
    public static function getDefaultMailDomain()
    {
        return !empty(self::getServerHostname()) ? self::getServerHostname() : 'processmaker.com';
    }

    /**
     * Initialize laravel database configuration
     * @see workflow/engine/bin/tasks/cliWorkspaces.php->check_queries_incompatibilities()
     */
    public static function initLaravel()
    {
        config(['database.connections.workflow.host' => DB_HOST]);
        config(['database.connections.workflow.database' => DB_NAME]);
        config(['database.connections.workflow.username' => DB_USER]);
        config(['database.connections.workflow.password' => DB_PASS]);

        app()->useStoragePath(realpath(PATH_DATA));
        app()->make(Kernel::class)->bootstrap();
        restore_error_handler();
    }

    /**
     * If the installation file exists it returns the defined values.
     * @return object
     */
    public static function getPathsInstalled()
    {
        //default values
        $result = [
            'pathData' => getcwd() . '/shared',
            'pathCompiled' => getcwd() . '/shared/compiled',
        ];

        $pathsInstalled = getcwd() . "/workflow/engine/config/paths_installed.php";
        if (file_exists($pathsInstalled)) {
            $script = "require_once '{$pathsInstalled}';"
                    . "return ["
                    . "'pathData' => PATH_DATA,"
                    . "'pathCompiled' => PATH_C,"
                    . "'hashInstallation' => HASH_INSTALLATION,"
                    . "'systemHash' => SYSTEM_HASH,"
                    . "];";
            $result = eval($script);
        }
        return (object) $result;
    }
}
