<?php
/**
 * pluginsChange.php
 * If the feature is enable and the code_scanner_scope was enable with the argument enable_plugin, will check the code
 * Review when a plugin was enable
 *
 * @link https://wiki.processmaker.com/3.0/Plugins#Enable_and_Disable_a_Plugin
 */

// lets display the items
use ProcessMaker\Plugins\PluginRegistry;

$pluginFile = $_GET['id'];
$pluginStatus = $_GET['status'];

$items = array ();
//here we are enabling or disabling the plugin and all related options registered.
$filter = new InputFilter();
$path = PATH_PLUGINS . $pluginFile;
$path = $filter->validateInput($path, 'path');

$oPluginRegistry = PluginRegistry::loadSingleton();

if ($handle = opendir(PATH_PLUGINS)) {
    while (false !== ($file = readdir($handle))) {
        if (strpos($file, '.php', 1) && $file == $pluginFile) {
            if ($pluginStatus == '1') {
                // change to disable
                $details = $oPluginRegistry->getPluginDetails($pluginFile);
                $oPluginRegistry->disablePlugin($details->getNamespace());
                $oPluginRegistry->savePlugin($details->getNamespace());
                G::auditLog("DisablePlugin", "Plugin Name: " . $details->getNamespace());
            } else {
                $pluginName = str_replace(".php", "", $pluginFile);

                if (is_file(PATH_PLUGINS . $pluginName . ".php") && is_dir(PATH_PLUGINS . $pluginName)) {

                    // change to ENABLED
                    require_once($path);
                    $details = $oPluginRegistry->getPluginDetails($pluginFile);
                    $oPluginRegistry->enablePlugin($details->getNamespace());
                    $oPluginRegistry->setupPlugins(); //get and setup enabled plugins
                    $oPluginRegistry->savePlugin($details->getNamespace());
                    G::auditLog("EnablePlugin", "Plugin Name: " . $details->getNamespace());
                }
            }
        }
    }
    closedir($handle);
}
