<?php

use Illuminate\Support\Facades\Cache;
use ProcessMaker\BusinessModel\User;
use ProcessMaker\Core\System;
use ProcessMaker\Plugins\PluginRegistry;

try {
    $usr = '';
    $pwd = '';

    if (strpos($_SERVER['HTTP_REFERER'], 'home/login') !== false) {
        $urlLogin = '../home/login';
    } else {
        $urlLogin = (substr(SYS_SKIN, 0, 2) !== 'ux')? 'login' : '../main/login';
    }

    if (!$RBAC->singleSignOn) {
        setcookie("singleSignOn", '0', time() + (24 * 60 * 60), '/');
        if (!isset($_POST['form']) ) {
            G::SendTemporalMessage ('ID_USER_HAVENT_RIGHTS_SYSTEM', 'error');
            G::header('Location: login');
            die();
        }

        $frm = $_POST['form'];

        $changePassword = false;
        if (isset($_POST['form']['__USR_PASSWORD_CHANGE__'])) {
            $value = Cache::pull($_POST['form']['__USR_PASSWORD_CHANGE__']);
            $changePassword = !empty($value);
            if ($changePassword === true) {
                $_POST['form']['USER_ENV'] = $value['userEnvironment'];
                $_POST['form']['BROWSER_TIME_ZONE_OFFSET'] = $value['browserTimeZoneOffset'];
                $frm['USR_USERNAME'] = $value['usrUsername'];
                $frm['USR_PASSWORD'] = $value['usrPassword'];
            }
        }

        if (isset($frm['USR_USERNAME'])) {
            $usr = mb_strtolower(trim($frm['USR_USERNAME']), 'UTF-8');
            $pwd = trim($frm['USR_PASSWORD']);
        }
        Cache::put('ldapMessageError', '', 2);
        $uid = $RBAC->VerifyLogin($usr, $pwd);
        $ldapMessageError = Cache::pull('ldapMessageError');
        $RBAC->cleanSessionFiles(72); //cleaning session files older than 72 hours

        switch ($uid) {
            //The user does doesn't exist
            case -1:
                $errLabel = 'WRONG_LOGIN_CREDENTIALS';
                break;
            //The password is incorrect
            case -2:
                $errLabel = 'WRONG_LOGIN_CREDENTIALS';
                if (isset($_SESSION['__AUTH_ERROR__'])) {
                    G::SendMessageText($_SESSION['__AUTH_ERROR__'], "warning");
                    unset($_SESSION['__AUTH_ERROR__']);
                }
                break;
            //The user is inactive
            case -3:
                require_once 'classes/model/Users.php';
                $user = new Users();
                $aUser = $user->loadByUsernameInArray($usr);

                switch ($aUser['USR_STATUS']) {
                    case 'VACATION':
                        $uid = $aUser['USR_UID'];
                        $RBAC->changeUserStatus($uid, 1);
                        $aUser['USR_STATUS'] = 'ACTIVE';
                        $user->update($aUser);
                        break;
                    case 'INACTIVE':
                        $errLabel = 'ID_USER_INACTIVE';
                        break;
                }
                break;
            //The Due date is finished
            case -4:
                $errLabel = 'ID_USER_INACTIVE_BY_DATE';
                break;
            case -5:
                $errLabel = 'ID_AUTHENTICATION_SOURCE_INVALID';
                break;
            case -6:
                $errLabel = 'ID_ROLE_INACTIVE';
                break;
            case -7:
                $errLabel = 'ID_LECA';
                break;
        }

        //to avoid empty string in user field.  This will avoid a weird message "this row doesn't exist"
        if ( !isset($uid) ) {
            $uid = -1;
            $errLabel = 'WRONG_LOGIN_CREDENTIALS';
        }

        $_SESSION["USERNAME_PREVIOUS1"] = (isset($_SESSION["USERNAME_PREVIOUS2"]))? $_SESSION["USERNAME_PREVIOUS2"] : "";
        $_SESSION["USERNAME_PREVIOUS2"] = $usr;
        $_SESSION["FAILED_LOGINS"]      = (isset($frm['FAILED_LOGINS']))? $frm['FAILED_LOGINS'] : 0;

        if (!isset($uid) || $uid < 0) {
            if ($_SESSION["USERNAME_PREVIOUS1"] != "" && $_SESSION["USERNAME_PREVIOUS2"] != "" && $_SESSION["USERNAME_PREVIOUS1"] != $_SESSION["USERNAME_PREVIOUS2"]) {
                $_SESSION["FAILED_LOGINS"] = 0;
            }

            if (isset($_SESSION['FAILED_LOGINS']) && ($uid == -1 || $uid == -2)) {
                $_SESSION['FAILED_LOGINS']++;
            }
            if (!defined('PPP_FAILED_LOGINS')) {
                define('PPP_FAILED_LOGINS', 0);
            }
            if (PPP_FAILED_LOGINS > 0) {
                if ($_SESSION['FAILED_LOGINS'] >= PPP_FAILED_LOGINS) {
                    $oConnection = Propel::getConnection('rbac');
                    $oStatement  = $oConnection->prepareStatement("SELECT USR_UID FROM RBAC_USERS WHERE USR_USERNAME = '" . $usr . "'");
                    $oDataset    = $oStatement->executeQuery();
                    if ($oDataset->next()) {
                        $sUserUID = $oDataset->getString('USR_UID');
                        $oConnection = Propel::getConnection('rbac');
                        $oStatement  = $oConnection->prepareStatement("UPDATE RBAC_USERS SET USR_STATUS = 0 WHERE USR_UID = '" . $sUserUID . "'");
                        $oStatement->executeQuery();
                        $oConnection = Propel::getConnection('workflow');
                        $oStatement  = $oConnection->prepareStatement("UPDATE USERS SET USR_STATUS = 'INACTIVE' WHERE USR_UID = '" . $sUserUID . "'");
                        $oStatement->executeQuery();
                        unset($_SESSION['FAILED_LOGINS']);
                        $errLabel = G::LoadTranslation('ID_ACCOUNT') . ' "' . $usr . '" ' . G::LoadTranslation('ID_ACCOUNT_DISABLED_CONTACT_ADMIN');
                    }
                    //Log failed authentications
            	    $message  = "| Many failed authentication attempts for USER: " . $usr . " | IP: " . G::getIpAddress() . " |  WS: " . config("system.workspace");
            	    $message .= " | BROWSER: " . $_SERVER['HTTP_USER_AGENT'];

            	    G::log($message, PATH_DATA, 'loginFailed.log');
                }
            }

            if (strpos($_SERVER['HTTP_REFERER'], 'home/login') !== false) {
                $d = serialize(['u' => $usr, 'p' => $pwd, 'm' => G::LoadTranslation($errLabel)]);
                $urlLogin = $urlLogin . '?d=' . base64_encode($d);
            } else {
                if (empty($ldapMessageError)) {
                    G::SendTemporalMessage($errLabel, "warning");
                } else {
                    G::SendTemporalMessage($ldapMessageError, "warning", "string");
                }
            }

            $u = (array_key_exists('form', $_POST) && array_key_exists('URL', $_POST['form']))? 'u=' . urlencode(htmlspecialchars_decode($_POST['form']['URL'])) : '';

            if ($u != '') {
                $urlLogin = $urlLogin . ((preg_match('/^.+\?.+$/', $urlLogin))? '&' : '?') . $u;
            }

            G::header('Location: ' . $urlLogin);
            exit(0);
        }

        if (!isset( $_SESSION['WORKSPACE'] ) ) {
            $_SESSION['WORKSPACE'] = config("system.workspace");
        }

        //Execute the SSO Script from plugin
        $oPluginRegistry = PluginRegistry::loadSingleton();
        $lSession="";
        $loginInfo = new loginInfo ($usr, $pwd, $lSession  );
        if ($oPluginRegistry->existsTrigger ( PM_LOGIN )) {
            $oPluginRegistry->executeTriggers ( PM_LOGIN , $loginInfo );
        }
        EnterpriseClass::enterpriseSystemUpdate($loginInfo);
        initUserSession($uid, $usr);
    } else {
        setcookie("singleSignOn", '1', time() + (24 * 60 * 60), '/');
        $uid = $RBAC->userObj->fields['USR_UID'];
        $usr = $RBAC->userObj->fields['USR_USERNAME'];
        initUserSession($uid, $usr);
    }

    //Set default Languaje
    if (isset($frm['USER_LANG'])) {
        if ($frm['USER_LANG'] != '') {
            $lang = $frm['USER_LANG'];
            if($frm['USER_LANG'] == "default"){
                //Check the USR_DEFAULT_LANG
                require_once 'classes/model/Users.php';
                $user = new Users();
                $rsUser = $user->userLanguaje($_SESSION['USER_LOGGED']);
                $rsUser->next();
                $rowUser = $rsUser->getRow();
                if( isset($rowUser["USR_DEFAULT_LANG"]) &&  $rowUser["USR_DEFAULT_LANG"]!=''){
                    $lang = $rowUser["USR_DEFAULT_LANG"];
                } else {
                    //Check the login_defaultLanguage
                    $oConf = new Configurations();
                    $oConf->loadConfig($obj, 'ENVIRONMENT_SETTINGS', '');
                    if (isset($oConf->aConfig["login_defaultLanguage"]) && $oConf->aConfig["login_defaultLanguage"] != "") {
                        $lang = $oConf->aConfig["login_defaultLanguage"];
                    }else{
                        if(SYS_LANG != ''){
                            $lang = SYS_LANG;
                        }else{
                            $lang = 'en';
                        }
                    }
                }
            } else {
                $lang = $frm['USER_LANG'];
            }
        }
    } else {
        if (defined("SYS_LANG") && SYS_LANG != "") {
            $lang = SYS_LANG;
        } else {
            $lang = 'en';
        }
    }

    //Set User Time Zone
    $user = UsersPeer::retrieveByPK($_SESSION['USER_LOGGED']);

    if (!is_null($user)) {
        $userTimeZone = $user->getUsrTimeZone();

        if (trim($userTimeZone) == '') {
            $arraySystemConfiguration = System::getSystemConfiguration('', '', config("system.workspace"));

            $userTimeZone = $arraySystemConfiguration['time_zone'];
        }

        $_SESSION['USR_TIME_ZONE'] = $userTimeZone;
    }


    //Set data
    $aUser = $RBAC->userObj->load($_SESSION['USER_LOGGED']);
    $RBAC->loadUserRolePermission($RBAC->sSystem, $_SESSION['USER_LOGGED']);
    //$rol = $RBAC->rolesObj->load($RBAC->aUserInfo['PROCESSMAKER']['ROLE']['ROL_UID']);
    $_SESSION['USR_FULLNAME'] = $aUser['USR_FIRSTNAME'] . ' ' . $aUser['USR_LASTNAME'];
    //$_SESSION['USR_ROLENAME'] = $rol['ROL_NAME'];

    unset($_SESSION['FAILED_LOGINS']);

    // Assign the uid of user to userloggedobj
    $RBAC->loadUserRolePermission($RBAC->sSystem, $uid);
    $res = $RBAC->userCanAccess('PM_LOGIN/strict');
    if ($res != 1 ) {
        if ($res == -2) {
            G::SendTemporalMessage ('ID_USER_HAVENT_RIGHTS_SYSTEM', "error");
        } else {
            G::SendTemporalMessage ('ID_USER_HAVENT_RIGHTS_PAGE', "error");
        }
        G::header  ("location: login.html");
        die;
    }

    /**log in table Login**/
    require_once 'classes/model/LoginLog.php';
    $weblog=new LoginLog();
    $aLog['LOG_UID']            = G::generateUniqueID();
    $aLog['LOG_STATUS']         = 'ACTIVE';
    $aLog['LOG_IP']             = G::getIpAddress();
    $aLog['LOG_SID']            = session_id();
    $aLog['LOG_INIT_DATE']      = date('Y-m-d H:i:s');
    //$aLog['LOG_END_DATE']       = '0000-00-00 00:00:00';
    $aLog['LOG_CLIENT_HOSTNAME']= System::getServerHost();
    $aLog['USR_UID']            = $_SESSION['USER_LOGGED'];
    $weblog->create($aLog);
    /**end log**/

    //**** defining and saving server info, this file has the values of the global array $_SERVER ****
    //this file is useful for command line environment (no Browser), I mean for triggers, crons and other executed over command line

    $_CSERVER = $_SERVER;
    unset($_CSERVER['REQUEST_TIME']);
    unset($_CSERVER['REMOTE_PORT']);
    $cput = serialize($_CSERVER);
    if (!is_file(PATH_DATA_SITE . '.server_info')) {
        file_put_contents(PATH_DATA_SITE . '.server_info', $cput);
    } else {
        $c = file_get_contents(PATH_DATA_SITE . '.server_info');
        if (G::encryptOld($c) != G::encryptOld($cput)) {
            file_put_contents(PATH_DATA_SITE . '.server_info', $cput);
        }
    }

    /* Check password using policy - Start */
    require_once 'classes/model/UsersProperties.php';
    $userProperty = new UsersProperties();

    // getting default user location
    if (isset($_REQUEST['form']['URL']) && $_REQUEST['form']['URL'] != '') {
        if (isset($_SERVER['HTTP_REFERER'])) {
            if (strpos($_SERVER['HTTP_REFERER'], 'processes/processes_Map?PRO_UID=') !== false) {
                $sLocation = $_SERVER['HTTP_REFERER'];
            } else {
                $sLocation = G::sanitizeInput($_REQUEST['form']['URL']);
            }
        } else {
            $sLocation = G::sanitizeInput($_REQUEST['form']['URL']);
        }
    } else {
        if (isset($_REQUEST['u']) && $_REQUEST['u'] != '') {
            $sLocation = G::sanitizeInput($_REQUEST['u']);
        } else {
            $sLocation = $userProperty->redirectTo($_SESSION['USER_LOGGED'], $lang);
        }
    }

    if ($RBAC->singleSignOn) {
        // Update the User's last login date
        updateUserLastLogin($aLog);
        G::header('Location: ' . $sLocation);
        die();
    }

    $userPropertyInfo = $userProperty->loadOrCreateIfNotExists($_SESSION['USER_LOGGED'], array('USR_PASSWORD_HISTORY' => serialize(array(G::encryptOld($pwd)))));
    
    //change password
    if ($changePassword === true) {
        $user = new User();
        $currentUser = $user->changePassword($_SESSION['USER_LOGGED'], $_POST['form']['USR_PASSWORD']);
        // Update the User's last login date
        updateUserLastLogin($aLog);
        G::header('Location: ' . $currentUser["__REDIRECT_PATH__"]);
        return;
    }
    
    //Get the errors in the password
    $errorInPassword = $userProperty->validatePassword(
        $_POST['form']['USR_PASSWORD'],
        $userPropertyInfo['USR_LAST_UPDATE_DATE'],
        $userPropertyInfo['USR_LOGGED_NEXT_TIME']
    );
    //The other authentication methods should not be validated by password security policies.
    if (!empty($aUser['USR_AUTH_TYPE'])) {
        $authType = $aUser['USR_AUTH_TYPE'];
        if ($authType != "mysql" && $authType != "") {
            $policiesToExclude = [
                'ID_PPP_MINIMUM_LENGTH',
                'ID_PPP_MAXIMUM_LENGTH',
                'ID_PPP_NUMERICAL_CHARACTER_REQUIRED',
                'ID_PPP_UPPERCASE_CHARACTER_REQUIRED',
                'ID_PPP_SPECIAL_CHARACTER_REQUIRED'
            ];
            $errorInPassword = array_diff($errorInPassword, $policiesToExclude);
            $errorInPassword = array_values($errorInPassword);
        }
    }
    //Get the policies enabled
    $policiesInPassword = $userProperty->validatePassword('', date('Y-m-d'), $userPropertyInfo['USR_LOGGED_NEXT_TIME'], true);
    //Enable change password from GAP
    if (!isset($enableChangePasswordAfterNextLogin)) {
        $enableChangePasswordAfterNextLogin = true;
    }

    if ($enableChangePasswordAfterNextLogin && !empty($errorInPassword)) {
        if (!defined('NO_DISPLAY_USERNAME')) {
            define('NO_DISPLAY_USERNAME', 1);
        }
        //We will to get the message for the login
        $messPassword = $policySection = $userProperty->getMessageValidatePassword($policiesInPassword, false);
        $changePassword = '<span style="font-weight:normal;">';
        if (array_search('ID_PPP_CHANGE_PASSWORD_AFTER_NEXT_LOGIN', $errorInPassword)) {
            $changePassword .= G::LoadTranslation('ID_PPP_CHANGE_PASSWORD_AFTER_NEXT_LOGIN') . '<br/><br/>';
        }

        $messPassword['DESCRIPTION'] = $changePassword . $policySection['DESCRIPTION'] . '</span>';
        $G_PUBLISH = new Publisher;
        $version = explode('.', trim(file_get_contents(PATH_GULLIVER . 'VERSION')));
        $version = isset($version[0]) ? intval($version[0]) : 0;

        if ($version >= 3) {
            $values = [
                "usrUsername" => $usr,
                "usrPassword" => $pwd,
                "userEnvironment" => config("system.workspace"),
                "browserTimeZoneOffset" => $_POST['form']['BROWSER_TIME_ZONE_OFFSET']
            ];
            $messPassword['__USR_PASSWORD_CHANGE__'] = G::generateUniqueID();
            Cache::put($messPassword['__USR_PASSWORD_CHANGE__'], $values, 2);
            $G_PUBLISH->AddContent('xmlform', 'xmlform', 'login/changePasswordpm3', '', $messPassword, 'sysLoginVerify');
            G::RenderPage('publish');
            session_destroy();
        } else {
            $G_PUBLISH->AddContent('xmlform', 'xmlform', 'login/changePassword', '', $messPassword, 'changePassword');
            G::RenderPage('publish');
        }
        die;
    }

    $configS = System::getSystemConfiguration('', '', config("system.workspace"));
    $activeSession = isset($configS['session_block']) ? !(int)$configS['session_block']:true;
    if ($activeSession){
        setcookie("PM-TabPrimary", 101010010, time() + (24 * 60 * 60), '/');
    }

    // Update the User's last login date
    updateUserLastLogin($aLog);

    $oPluginRegistry = PluginRegistry::loadSingleton();
    if ($oPluginRegistry->existsTrigger ( PM_AFTER_LOGIN )) {
        $oPluginRegistry->executeTriggers ( PM_AFTER_LOGIN , $_SESSION['USER_LOGGED'] );
    }

    G::header('Location: ' . $sLocation);
    die;
} catch ( Exception $e ) {
    $aMessage['MESSAGE'] = $e->getMessage();
    $G_PUBLISH = new Publisher;
    $G_PUBLISH->AddContent('xmlform', 'xmlform', 'login/showMessage', '', $aMessage );
    G::RenderPage( 'publish' );
    die;
}
