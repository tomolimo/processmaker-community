<?php

use Processmaker\Core\System;

$filter = new InputFilter();
$_POST = $filter->xssFilterHard($_POST);
$_GET = $filter->xssFilterHard($_GET);
$request = isset($_POST['request']) ? $_POST['request'] : (isset($_GET['request']) ? $_GET['request'] : null);

switch ($request) {
    //check if the APP_CACHE VIEW table and their triggers are installed
    case 'info':
        $result = new stdClass();
        $result->info = [];

        //check the language, if no info in config about language, the default is 'en'
        $oConf = new Configurations();
        $oConf->loadConfig($x, 'APP_CACHE_VIEW_ENGINE', '', '', '', '');
        $appCacheViewEngine = $oConf->aConfig;

        if (isset($appCacheViewEngine['LANG'])) {
            $lang = (defined('SYS_LANG')) ? SYS_LANG : $appCacheViewEngine['LANG'];
            $status = strtoupper($appCacheViewEngine['STATUS']);
        } else {
            $confParams = array('LANG' => (defined('SYS_LANG')) ? SYS_LANG : 'en', 'STATUS' => '');
            $oConf->aConfig = $confParams;
            $oConf->saveConfig('APP_CACHE_VIEW_ENGINE', '', '', '');
            $lang = (defined('SYS_LANG')) ? SYS_LANG : 'en';
            $status = '';
        }

        //get user Root from hash
        $result->info = [];
        $result->error = false;

        //setup the appcacheview object, and the path for the sql files
        $appCache = new AppCacheView();
        $appCache->setPathToAppCacheFiles(PATH_METHODS . 'setup' . PATH_SEP . 'setupSchemas' . PATH_SEP);

        $res = $appCache->getMySQLVersion();
        //load translations  G::LoadTranslation
        $result->info[] = array('name' => G::LoadTranslation('ID_CACHE_BUILDER_MYSQL_VERSION'), 'value' => $res);

        $res = $appCache->checkGrantsForUser(false);
        $currentUser = $res['user'];
        $currentUserIsSuper = $res['super'];
        $result->info[] = array('name' => G::LoadTranslation('ID_CACHE_BUILDER_CURRENT_USER'), 'value' => $currentUser);
        $result->info[] = array('name' => G::LoadTranslation('ID_CACHE_BUILDER_USER_SUPER_PRIVILEGE'), 'value' => $currentUserIsSuper);

        try {
            PROPEL::Init(PATH_METHODS . 'dbConnections/rootDbConnections.php');
            $con = Propel::getConnection("root");
        } catch (Exception $e) {
            $result->info[] = array('name' => 'Checking MySql Root user', 'value' => 'failed');
            $result->error = true;
            $result->errorMsg = $e->getMessage();
        }

        //if user does not have the SUPER privilege we need to use the root user and grant the SUPER priv. to normal user.
        if (!$currentUserIsSuper && !$result->error) {
            $res = $appCache->checkGrantsForUser(true);
            if (!isset($res['error'])) {
                $result->info[] = array('name' => G::LoadTranslation('ID_ROOT_USER'), 'value' => $res['user']);
                $result->info[] = array('name' => G::LoadTranslation('ID_ROOT_USER_SUPER'), 'value' => $res['super']);
            } else {
                $result->info[] = array('name' => 'Error', 'value' => $res['msg']);
            }
        }

        //now check if table APPCACHEVIEW exists, and it have correct number of fields, etc.
        $res = $appCache->checkAppCacheView();
        $result->info[] = array('name' => G::LoadTranslation('ID_CACHE_BUILDER_TABLE'), 'value' => $res['found']);

        $result->info[] = array('name' => G::LoadTranslation('ID_CACHE_BUILDER_ROWS'), 'value' => $res['count']);

        //now check if we have the triggers installed
        //APP_DELEGATION INSERT
        $res = $appCache->triggerAppDelegationInsert($lang, false);
        $result->info[] = array('name' => G::LoadTranslation('ID_CACHE_BUILDER_TRIGGER_INSERT'), 'value' => $res);

        //APP_DELEGATION Update
        $res = $appCache->triggerAppDelegationUpdate($lang, false);
        $result->info[] = array('name' => G::LoadTranslation('ID_CACHE_BUILDER_TRIGGER_UPDATE'), 'value' => $res);

        //APPLICATION UPDATE
        $res = $appCache->triggerApplicationUpdate($lang, false);
        $result->info[] = array('name' => G::LoadTranslation('ID_CACHE_BUILDER_TRIGGER_APPLICATION_UPDATE'), 'value' => $res);

        //APPLICATION DELETE
        $res = $appCache->triggerApplicationDelete($lang, false);
        $result->info[] = array('name' => G::LoadTranslation('ID_CACHE_BUILDER_TRIGGER_APPLICATION_DELETE'), 'value' => $res);

        //SUB_APPLICATION INSERT
        $res = $appCache->triggerSubApplicationInsert($lang, false);

        //CONTENT UPDATE
        $res = $appCache->triggerContentUpdate($lang, false);
        $result->info[] = array("name" => G::LoadTranslation('ID_CACHE_BUILDER_TRIGGER_CONTENT_UPDATE'), "value" => $res);

        //show language
        $result->info[] = array('name' => G::LoadTranslation('ID_CACHE_BUILDER_LANGUAGE'), 'value' => $lang);

        echo G::json_encode($result);
        break;
    case 'getLangList':
        $Translations = G::getModel('Translation');
        $result = new stdClass();
        $result->rows = [];

        $langs = $Translations->getTranslationEnvironments();
        foreach ($langs as $lang) {
            $result->rows[] = array('LAN_ID' => $lang['LOCALE'], 'LAN_NAME' => $lang['LANGUAGE']);
        }

        print(G::json_encode($result));
        break;
    case 'build':
        $sqlToExe = [];
        $conf = new Configurations();

        //DEPRECATED $lang = $_POST['lang'];
        //there is no more support for other languages that english
        $lang = (defined('SYS_LANG')) ? SYS_LANG : 'en';

        try {
            //setup the appcacheview object, and the path for the sql files
            $appCache = new AppCacheView();
            $appCache->setPathToAppCacheFiles(PATH_METHODS . 'setup' . PATH_SEP . 'setupSchemas' . PATH_SEP);

            //Update APP_DELEGATION.DEL_LAST_INDEX data
            $res = $appCache->updateAppDelegationDelLastIndex($lang, true);

            //APP_DELEGATION INSERT
            $res = $appCache->triggerAppDelegationInsert($lang, true);


            //APP_DELEGATION Update
            $res = $appCache->triggerAppDelegationUpdate($lang, true);


            //APPLICATION UPDATE
            $res = $appCache->triggerApplicationUpdate($lang, true);


            //APPLICATION DELETE
            $res = $appCache->triggerApplicationDelete($lang, true);

            //SUB_APPLICATION INSERT
            $res = $appCache->triggerSubApplicationInsert($lang, false);

            //CONTENT UPDATE
            $res = $appCache->triggerContentUpdate($lang, true);

            //build using the method in AppCacheView Class
            $res = $appCache->fillAppCacheView($lang);

            //set status in config table
            $confParams = array('LANG' => $lang, 'STATUS' => 'active');
            $conf->aConfig = $confParams;
            $conf->saveConfig('APP_CACHE_VIEW_ENGINE', '', '', '');

            $result = new StdClass();
            $result->success = true;
            $result->msg = G::LoadTranslation('ID_TITLE_COMPLETED');
            G::auditLog("BuildCache");
            echo G::json_encode($result);
        } catch (Exception $e) {
            $confParams = array('lang' => $lang, 'status' => 'failed');
            $appCacheViewEngine = $oServerConf->setProperty('APP_CACHE_VIEW_ENGINE', $confParams);

            $token = strtotime("now");
            PMException::registerErrorLog($e, $token);
            $varRes = '{success: false, msg:"' . G::LoadTranslation("ID_EXCEPTION_LOG_INTERFAZ", array($token)) . '"}';
            G::outRes($varRes);
        }
        break;
    case 'recreate-root':
        // Get the post variables
        $user = !empty($_POST['user']) ? $_POST['user'] : '';
        $pass = !empty($_POST['password']) ? $_POST['password'] : '';
        $server = !empty($_POST['host']) ? $_POST['host'] : '';
        $code = !empty($_POST['codeCaptcha']) ? $_POST['codeCaptcha'] : '';

        // Check if in the host was included the port
        $server = explode(':', $server);
        $serverName = $server[0];
        $port = (count($server) > 1) ? $server[1] : '';

        // Review if the captcha is not empty
        if (empty($code)) {
            echo G::loadTranslation('ID_CAPTCHA_CODE_INCORRECT');
            break;
        }
        // Review if th captcha is incorrect
        if ($code !== $_SESSION['securimage_code_disp']['default']) {
            echo G::loadTranslation('ID_CAPTCHA_CODE_INCORRECT');
            break;
        }
        // Define a message of failure
        $message = G::loadTranslation('ID_MESSAGE_ROOT_CHANGE_FAILURE');
        if (!empty($user) && !empty($pass) && !empty($serverName)) {
            list($success, $message) = System::checkPermissionsDbUser(DB_ADAPTER, $serverName, $port, $user, $pass);
            if ($success) {
                $id = 'ID_MESSAGE_ROOT_CHANGE_FAILURE';
                if (System::regenerateCredentiaslPathInstalled($serverName, $user, $pass)) {
                    $id = 'ID_MESSAGE_ROOT_CHANGE_SUCESS';
                }
                $message = G::loadTranslation($id);
            }
        }

        echo $message;
        break;
    case 'captcha':
        require_once PATH_TRUNK . 'vendor/dapphp/securimage/securimage.php';
        $img = new Securimage();
        $img->show();
        break;
}
