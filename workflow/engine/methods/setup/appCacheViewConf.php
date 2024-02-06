<?php
/**
 * Rebuilding the cases list cache and changing the MySQL credentials
 *
 * @see processWorkspace()
 *
 * @link https://wiki.processmaker.com/3.2/Clearing_the_Case_List_Cache
 */

use ProcessMaker\Core\Installer;

global $RBAC;
$RBAC->requirePermissions('PM_SETUP');
// Define the content of the case list cache builder
$headPublisher = headPublisher::getSingleton();
$headPublisher->addExtJsScript('setup/appCacheViewConf', false); //adding a javascript file .js
$headPublisher->addContent('setup/appCacheViewConf'); //adding a html file  .html.

// Get some configurations
$conf = new Configurations();
$conf->loadConfig($x, 'APP_CACHE_VIEW_ENGINE', '', '', '', '');
$lang = isset($conf->aConfig['LANG']) ? $conf->aConfig['LANG'] : 'en';

// Assign the language configured
$headPublisher->assign('currentLang', $lang);

// Get the mysql version
$mysqlVersion = getMysqlVersion();
$maxMysqlVersion = InstallerModule::MYSQL_VERSION_MAXIMUM_SUPPORTED;
if (version_compare($mysqlVersion, $maxMysqlVersion, '<')) {
    $userNameMaxLength = 16;
} else {
	$userNameMaxLength = 32;
}
$headPublisher->assign('userNameMaxLength', $userNameMaxLength);

G::RenderPage('publish', 'extJs');