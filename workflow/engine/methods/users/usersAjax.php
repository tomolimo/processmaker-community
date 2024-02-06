<?php

use ProcessMaker\BusinessModel\User as BmUser;

// Initializing variables
$action = !empty($_POST['action']) ? $_POST['action'] : '';
$result = new StdClass();

// Try to execute the requested action
try {
    // Checking access permissions for the current action
    global $RBAC;
    $RBAC->allows(basename(__FILE__), $action);

    // Executing the action
    switch ($action) {
        case 'countryList':
            $c = new Criteria();
            $c->add(IsoCountryPeer::IC_UID, null, Criteria::ISNOTNULL);
            $c->addAscendingOrderByColumn(IsoCountryPeer::IC_NAME);
            $countries = IsoCountryPeer::doSelect($c);

            $data = [];
            foreach ($countries as $row) {
                $data[] = ['IC_UID' => $row->getICUid(), 'IC_NAME' => $row->getICName()];
            }
            print(G::json_encode($data));
            break;
        case 'stateList':
            $c = new Criteria();
            $country = $_POST['IC_UID'];
            $c->add(IsoSubdivisionPeer::IC_UID, $country, Criteria::EQUAL);
            $c->addAscendingOrderByColumn(IsoSubdivisionPeer::IS_NAME);
            $locations = IsoSubdivisionPeer::doSelect($c);

            $data = [];
            foreach ($locations as $row) {
                if (($row->getISUid() != '') && ($row->getISName() != '')) {
                    $data[] = ['IS_UID' => $row->getISUid(), 'IS_NAME' => $row->getISName()];
                }
            }
            print(G::json_encode($data));
            break;
        case 'locationList':
            $c = new Criteria();
            $country = $_POST['IC_UID'];
            $state = $_POST['IS_UID'];
            $c->add(IsoLocationPeer::IC_UID, $country, Criteria::EQUAL);
            $c->add(IsoLocationPeer::IS_UID, $state, Criteria::EQUAL);
            $c->addAscendingOrderByColumn(IsoLocationPeer::IL_NAME);
            $locations = IsoLocationPeer::doSelect($c);

            $data = [];
            foreach ($locations as $row) {
                if (($row->getILUid() != '') && ($row->getILName() != '')) {
                    $data[] = ['IL_UID' => $row->getILUid(), 'IL_NAME' => $row->getILName()];
                }
            }
            print(G::json_encode($data));
            break;
        case 'usersList':
            $filter = (isset($_POST['filter'])) ? $_POST['filter'] : '';

            $arrayUser = [];

            $user = new BmUser();
            $conf = new Configurations();

            $arrayConfFormat = $conf->getFormats();

            $arrayCondition = [[UsersPeer::USR_STATUS, ['ACTIVE', 'VACATION'], Criteria::IN]];

            if (isset($_POST['USR_UID'])) {
                $arrayCondition[] = [UsersPeer::USR_UID, $_POST['USR_UID'], Criteria::NOT_EQUAL];
            }

            $results = $user->getUsers(['condition' => $arrayCondition, 'filter' => $filter], null, null, null, 25);

            foreach ($results['data'] as $record) {
                $arrayUser[] = [
                    'USR_UID' => $record['USR_UID'],
                    'USER_FULLNAME' => G::getFormatUserList($arrayConfFormat['format'], $record)
                ];
            }

            echo G::json_encode($arrayUser);
            break;
        case 'availableCalendars':
            $calendar = new Calendar();
            $calendarObj = $calendar->getCalendarList(true, true);
            $data = [['CALENDAR_UID' => '', 'CALENDAR_NAME' => '- ' . G::LoadTranslation('ID_NONE') . ' -']];
            foreach ($calendarObj['array'] as $rowId => $row) {
                if ($rowId > 0) {
                    $data[] = ['CALENDAR_UID' => $row['CALENDAR_UID'], 'CALENDAR_NAME' => $row['CALENDAR_NAME']];
                }
            }
            print(G::json_encode($data));
            break;
        case 'rolesList':
            $roles = new Roles();
            $rolesData = $roles->getAllRoles();
            $data = [];
            foreach ($rolesData as $rowId => $row) {
                $data[] = ['ROL_UID' => $row['ROL_CODE'], 'ROL_CODE' => $row['ROL_NAME']];
            }
            print(G::json_encode($data));
            break;
        case 'getUserLogedRole':
            $user = new Users();
            $userLog = $user->loadDetailed($_SESSION['USER_LOGGED']);
            print(G::json_encode([
                'USR_UID' => $userLog['USR_UID'],
                'USR_USERNAME' => $userLog['USR_USERNAME'],
                'USR_ROLE' => $userLog['USR_ROLE']
            ]));
            break;
        case 'languagesList':
            $translations = new Translation();
            $languages = $translations->getTranslationEnvironments();
            $data = [['LAN_ID' => '', 'LAN_NAME' => '- ' . G::LoadTranslation('ID_NONE') . ' -']];
            foreach ($languages as $lang) {
                $data[] = [
                    'LAN_ID' => $lang['LOCALE'],
                    'LAN_NAME' => $lang['LANGUAGE']
                ];
            }
            print(G::json_encode($data));
            break;
        case 'saveUser':
        case 'savePersonalInfo':
            try {
                verifyCsrfToken($_POST);
                $user = new BmUser();
                $form = $_POST;
                $permissionsToSaveData = $user->getPermissionsForEdit();
                $form = $user->checkPermissionForEdit($_SESSION['USER_LOGGED'], $permissionsToSaveData, $form);

                if (!empty($form["USR_EMAIL"])) {
                    $form["USR_EMAIL"] = strtolower($form["USR_EMAIL"]);
                }

                switch ($_POST['action']) {
                    case 'saveUser':
                        if (!$user->checkPermission($_SESSION['USER_LOGGED'], 'PM_USERS')) {
                            throw new Exception(G::LoadTranslation('ID_USER_NOT_HAVE_PERMISSION',
                                [$_SESSION['USER_LOGGED']]));
                        }
                        break;
                    case 'savePersonalInfo':
                        if (!$user->checkPermission($_SESSION['USER_LOGGED'], 'PM_USERS') &&
                            !$user->checkPermission($_SESSION['USER_LOGGED'], 'PM_EDITPERSONALINFO')
                        ) {
                            throw new Exception(G::LoadTranslation('ID_USER_NOT_HAVE_PERMISSION',
                                [$_SESSION['USER_LOGGED']]));
                        }
                        break;
                    default:
                        throw new Exception(G::LoadTranslation('ID_INVALID_DATA'));
                        break;
                }

                if (array_key_exists('USR_LOGGED_NEXT_TIME', $form)) {
                    $form['USR_LOGGED_NEXT_TIME'] = ($form['USR_LOGGED_NEXT_TIME']) ? 1 : 0;
                }

                $userUid = '';
                $auditLogType = '';
                if (empty($form['USR_UID'])) {
                    $arrayUserData = $user->create($form);
                    $userUid = $arrayUserData['USR_UID'];
                    $auditLogType = 'INS';
                } else {
                    if (array_key_exists('USR_NEW_PASS', $form) && $form['USR_NEW_PASS'] == '') {
                        unset($form['USR_NEW_PASS']);
                    }

                    $results = $user->update($form['USR_UID'], $form, $_SESSION['USER_LOGGED']);
                    $userUid = $form['USR_UID'];
                    $arrayUserData = $user->getUserRecordByPk($userUid, [], false);
                    $auditLogType = 'UPD';
                }

                $user->auditLog($auditLogType,
                    array_merge(['USR_UID' => $userUid, 'USR_USERNAME' => $arrayUserData['USR_USERNAME']], $form));
                /* Saving preferences */
                $def_lang = isset($form['PREF_DEFAULT_LANG']) ? $form['PREF_DEFAULT_LANG'] : '';
                $def_menu = isset($form['PREF_DEFAULT_MENUSELECTED']) ? $form['PREF_DEFAULT_MENUSELECTED'] : '';
                $def_cases_menu = isset($form['PREF_DEFAULT_CASES_MENUSELECTED']) ? $form['PREF_DEFAULT_CASES_MENUSELECTED'] : '';
                $configuration = new Configurations();
                $configuration->aConfig = [
                    'DEFAULT_LANG' => $def_lang,
                    'DEFAULT_MENU' => $def_menu,
                    'DEFAULT_CASES_MENU' => $def_cases_menu
                ];
                $configuration->saveConfig('USER_PREFERENCES', '', '', $userUid);

                if ($user->checkPermission($userUid, 'PM_EDIT_USER_PROFILE_PHOTO')) {
                    try {
                        $user->uploadImage($userUid);
                    } catch (Exception $e) {
                        $result->success = false;
                        $result->fileError = true;

                        echo G::json_encode($result);
                        exit(0);
                    }
                }

                if ($_SESSION['USER_LOGGED'] == $form['USR_UID']) {
                    /* UPDATING SESSION VARIABLES */
                    $userInfo = $RBAC->userObj->load($_SESSION['USER_LOGGED']);
                    $_SESSION['USR_FULLNAME'] = $userInfo['USR_FIRSTNAME'] . ' ' . $userInfo['USR_LASTNAME'];
                }

                $result->success = true;
                print(G::json_encode($result));
            } catch (Exception $e) {
                $result->success = false;
                $result->error = $e->getMessage();
                print(G::json_encode($result));
            }
            break;
        case 'userData':
            // Check if the user logged has the correct permission
            if (($_POST['USR_UID'] !== $_SESSION['USER_LOGGED']) && ($RBAC->userCanAccess('PM_USERS') !== 1)) {
                throw new Exception(G::LoadTranslation('ID_USER_NOT_HAVE_PERMISSION', [$_SESSION['USER_LOGGED']]));
            }

            $_SESSION['CURRENT_USER'] = $_POST['USR_UID'];
            $user = new Users();
            $fields = $user->loadDetailed($_POST['USR_UID']);

            //Load Calendar options and falue for this user
            $calendar = new Calendar();
            $calendarInfo = $calendar->getCalendarFor($_POST['USR_UID'], $_POST['USR_UID'], $_POST['USR_UID']);
            //If the function returns a DEFAULT calendar it means that this object doesn't have assigned any calendar
            $fields['USR_CALENDAR'] = $calendarInfo['CALENDAR_APPLIED'] != 'DEFAULT' ? $calendarInfo['CALENDAR_UID'] : "";
            $fields['CALENDAR_NAME'] = $calendarInfo['CALENDAR_NAME'];

            //verifying if it has any preferences on the configurations table
            $configuration = new Configurations();
            $configuration->loadConfig($x, 'USER_PREFERENCES', '', '', $fields['USR_UID'], '');

            $fields['PREF_DEFAULT_MENUSELECTED'] = '';
            $fields['PREF_DEFAULT_CASES_MENUSELECTED'] = '';
            $fields['PREF_DEFAULT_LANG'] = isset($configuration->aConfig['DEFAULT_LANG']) ? $configuration->aConfig['DEFAULT_LANG'] : SYS_LANG;

            if (isset($configuration->aConfig['DEFAULT_MENU'])) {
                $fields['PREF_DEFAULT_MENUSELECTED'] = $configuration->aConfig['DEFAULT_MENU'];
            } else {
                switch ($RBAC->aUserInfo['PROCESSMAKER']['ROLE']['ROL_CODE']) {
                    case 'PROCESSMAKER_ADMIN':
                        $fields['PREF_DEFAULT_MENUSELECTED'] = 'PM_SETUP';
                        break;
                    case 'PROCESSMAKER_OPERATOR':
                        $fields['PREF_DEFAULT_MENUSELECTED'] = 'PM_CASES';
                        break;
                }
            }

            $fields['PREF_DEFAULT_CASES_MENUSELECTED'] = isset($configuration->aConfig['DEFAULT_CASES_MENU']) ? $configuration->aConfig['DEFAULT_CASES_MENU'] : '';

            if ($fields['USR_REPLACED_BY'] != '') {
                $user = new Users();
                $u = $user->load($fields['USR_REPLACED_BY']);
                if ($u['USR_STATUS'] == 'CLOSED') {
                    $replaced_by = '';
                    $fields['USR_REPLACED_BY'] = '';
                } else {
                    $c = new Configurations();
                    $arrayConfFormat = $c->getFormats();

                    $replaced_by = G::getFormatUserList($arrayConfFormat['format'], $u);
                }
            } else {
                $replaced_by = '';
            }

            $fields['REPLACED_NAME'] = $replaced_by;

            $menuSelected = '';

            if ($fields['PREF_DEFAULT_MENUSELECTED'] != '') {
                foreach ($RBAC->aUserInfo['PROCESSMAKER']['PERMISSIONS'] as $permission) {
                    if ($fields['PREF_DEFAULT_MENUSELECTED'] == $permission['PER_CODE']) {
                        switch ($permission['PER_CODE']) {
                            case 'PM_USERS':
                            case 'PM_SETUP':
                                $menuSelected = strtoupper(G::LoadTranslation('ID_SETUP'));
                                break;
                            case 'PM_CASES':
                                $menuSelected = strtoupper(G::LoadTranslation('ID_CASES'));
                                break;
                            case 'PM_FACTORY':
                                $menuSelected = strtoupper(G::LoadTranslation('ID_APPLICATIONS'));
                                break;
                            case 'PM_DASHBOARD':
                                $menuSelected = strtoupper(G::LoadTranslation('ID_DASHBOARD'));
                                break;
                        }
                    } else {
                        if ($fields['PREF_DEFAULT_MENUSELECTED'] == 'PM_STRATEGIC_DASHBOARD') {
                            $menuSelected = strtoupper(G::LoadTranslation('ID_STRATEGIC_DASHBOARD'));
                        }
                    }
                }
            }

            $fields['MENUSELECTED_NAME'] = $menuSelected;

            $menu = new Menu();
            $menu->load('cases');
            $casesMenuSelected = '';

            if ($fields['PREF_DEFAULT_CASES_MENUSELECTED'] != '') {
                foreach ($menu->Id as $i => $item) {
                    if ($fields['PREF_DEFAULT_CASES_MENUSELECTED'] == $item) {
                        $casesMenuSelected = $menu->Labels[$i];
                    }
                }
            }

            $user = new Users();
            $userLog = $user->loadDetailed($_SESSION['USER_LOGGED']);
            $fields['USER_LOGGED_NAME'] = $userLog['USR_USERNAME'];
            $fields['USER_LOGGED_ROLE'] = $userLog['USR_ROLE'];

            $fields['CASES_MENUSELECTED_NAME'] = $casesMenuSelected;

            $userProperties = new UsersProperties();
            $properties = $userProperties->loadOrCreateIfNotExists($fields['USR_UID'],
                ['USR_PASSWORD_HISTORY' => serialize([$user->getUsrPassword()])]);
            $fields['USR_LOGGED_NEXT_TIME'] = $properties['USR_LOGGED_NEXT_TIME'];

            if (array_key_exists('USR_PASSWORD', $fields)) {
                unset($fields['USR_PASSWORD']);
            }

            $userPermissions = new BmUser();
            $permissions = $userPermissions->loadDetailedPermissions($fields);

            $result->success = true;
            $result->user = $fields;
            $result->permission = $permissions;

            print(G::json_encode($result));
            break;
        case 'defaultMainMenuOptionList':
            $rows = [];
            foreach ($RBAC->aUserInfo['PROCESSMAKER']['PERMISSIONS'] as $permission) {
                switch ($permission['PER_CODE']) {
                    case 'PM_USERS':
                    case 'PM_SETUP':
                        $rows[] = [
                            'id' => 'PM_SETUP',
                            'name' => strtoupper(G::LoadTranslation('ID_SETUP'))
                        ];
                        break;
                    case 'PM_CASES':
                        $rows[] = [
                            'id' => 'PM_CASES',
                            'name' => strtoupper(G::LoadTranslation('ID_CASES'))
                        ];
                        break;
                    case 'PM_FACTORY':
                        $rows[] = [
                            'id' => 'PM_FACTORY',
                            'name' => strtoupper(G::LoadTranslation('ID_APPLICATIONS'))
                        ];
                        break;
                    case 'PM_DASHBOARD':
                        $rows[] = [
                            'id' => 'PM_DASHBOARD',
                            'name' => strtoupper(G::LoadTranslation('ID_DASHBOARD'))
                        ];
                        break;
                }
            }
            print(G::json_encode($rows));
            break;
        case 'defaultCasesMenuOptionList':
            $menu = new Menu();
            $menu->load('cases');

            foreach ($menu->Id as $i => $item) {
                if ($menu->Types[$i] != 'blockHeader') {
                    $rowsCasesMenu[] = ['id' => $item, 'name' => $menu->Labels[$i]];
                }
            }
            print(G::json_encode($rowsCasesMenu));
            break;
        case 'testPassword':
            $userProperty = new UsersProperties();

            $fields = [];
            $color = '';
            $img = '';
            $dateNow = date('Y-m-d H:i:s');
            $errorInPassword = $userProperty->validatePassword($_POST['PASSWORD_TEXT'], $dateNow, 0);

            if (!empty($errorInPassword)) {
                $img = '/images/delete.png';
                $color = 'red';
                if (!defined('NO_DISPLAY_USERNAME')) {
                    define('NO_DISPLAY_USERNAME', 1);
                }
                $fields = $userProperty->getMessageValidatePassword($errorInPassword);
                $fields['STATUS'] = false;
            } else {
                $color = 'green';
                $img = '/images/dialog-ok-apply.png';
                $fields['DESCRIPTION'] = G::LoadTranslation('ID_PASSWORD_COMPLIES_POLICIES') . '</span>';
                $fields['STATUS'] = true;
            }
            $span = '<span style="color: ' . $color . '; font: 9px tahoma,arial,helvetica,sans-serif;">';
            $gif = '<img width="13" height="13" border="0" src="' . $img . '">';
            $fields['DESCRIPTION'] = $span . $gif . $fields['DESCRIPTION'];
            print(G::json_encode($fields));
            break;
        case 'testUsername':
            $_POST['NEW_USERNAME'] = trim($_POST['NEW_USERNAME']);
            $usrUid = isset($_POST['USR_UID']) ? $_POST['USR_UID'] : '';

            $response = ["success" => true];

            $criteria = new Criteria();
            $criteria->addSelectColumn(UsersPeer::USR_USERNAME);

            $criteria->add(UsersPeer::USR_USERNAME, utf8_encode($_POST['NEW_USERNAME']));
            if ($usrUid != '') {
                $criteria->add(UsersPeer::USR_UID, [$_POST['USR_UID']], Criteria::NOT_IN);
            }
            $dataSet = UsersPeer::doSelectRS($criteria);
            $dataSet->setFetchmode(ResultSet::FETCHMODE_ASSOC);
            $dataSet->next();
            $row = $dataSet->getRow();

            if (is_array($row) || $_POST['NEW_USERNAME'] == '') {
                $color = 'red';
                $img = '/images/delete.png';
                $dataVar = ['USER_ID' => $_POST['NEW_USERNAME']];
                $text = G::LoadTranslation('ID_USERNAME_ALREADY_EXISTS', $dataVar);
                $text = ($_POST['NEW_USERNAME'] == '') ? G::LoadTranslation('ID_MSG_ERROR_USR_USERNAME') : $text;
                $response['exists'] = true;
            } else {
                $color = 'green';
                $img = '/images/dialog-ok-apply.png';
                $text = G::LoadTranslation('ID_USERNAME_CORRECT');
                $response['exists'] = false;
            }

            $span = '<span style="color: ' . $color . '; font: 9px tahoma,arial,helvetica,sans-serif;">';
            $gif = '<img width="13" height="13" border="0" src="' . $img . '">';
            $response['descriptionText'] = $span . $gif . $text . '</span>';
            echo G::json_encode($response);
            break;
        case "passwordValidate":
            $messageResultLogin = "";
            $password = $_POST["password"];
            $resultLogin = $RBAC->VerifyLogin($_SESSION["USR_USERNAME"], $password);

            if ($resultLogin == $_SESSION["USER_LOGGED"]) {
                $messageResultLogin = "OK";
            } else {
                $messageResultLogin = "ERROR";
            }

            $response = [];
            $response["result"] = $messageResultLogin;
            echo G::json_encode($response);
            break;
    }
} catch (Exception $e) {
    $result->success = false;
    $result->error = $e->getMessage();
    echo G::json_encode($result);
}
