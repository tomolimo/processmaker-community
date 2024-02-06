<?php

use ProcessMaker\BusinessModel\Role;
use ProcessMaker\Exception\RBACException;
use ProcessMaker\Model\User;
use ProcessMaker\Model\UserExtendedAttributes;

// Include global object RBAC
global $RBAC;

// Check if the current user have the correct permissions to access to this resource, if not throws a RBAC Exception with code 403
if ($RBAC->userCanAccess('PM_USERS') !== 1) {
    throw new RBACException('ID_ACCESS_DENIED', 403);
}

global $G_PUBLISH;
$G_PUBLISH = new Publisher();
try {
    $option = empty($_REQUEST["option"]) ? '' : $_REQUEST["option"];
    switch ($option) {
        case "list":
            $rbacRoles = Role::getAllRoles();
            $orders = [
                "attributeName" => "UEA_NAME",
                "attribute" => "UEA_ATTRIBUTE_ID",
                "role" => "UEA_ROLES",
                "dateCreated" => "UEA_DATE_CREATE"
            ];
            $query = empty($_REQUEST["query"]) ? "" : $_REQUEST["query"];
            $limit = empty($_REQUEST["limit"]) ? 10 : $_REQUEST["limit"];
            $start = empty($_REQUEST["start"]) ? 0 : $_REQUEST["start"];
            $ascending = !isset($_REQUEST["ascending"]) ? "1" : $_REQUEST["ascending"];
            $orderBy = $orders["attributeName"];
            if (!empty($_REQUEST["orderBy"]) && !empty($orders[$_REQUEST["orderBy"]])) {
                $orderBy = $orders[$_REQUEST["orderBy"]];
            }

            $count = UserExtendedAttributes::join('USERS', 'USERS.USR_ID', '=', 'USER_EXTENDED_ATTRIBUTES.UEA_OWNER')
                    ->count();
            $userExtendedAttributes = UserExtendedAttributes::query()
                    ->join('USERS', 'USERS.USR_ID', '=', 'USER_EXTENDED_ATTRIBUTES.UEA_OWNER')
                    ->where('UEA_NAME', 'LIKE', "%{$query}%")
                    ->orWhere('UEA_ATTRIBUTE_ID', 'LIKE', "%{$query}%")
                    ->orWhere('UEA_ROLES', 'LIKE', "%{$query}%")
                    ->orWhere('UEA_DATE_CREATE', 'LIKE', "%{$query}%")
                    ->orderBy($orderBy, $ascending === "1" ? "asc" : "desc")
                    ->offset($start)
                    ->limit($limit)
                    ->get()
                    ->toArray();
            //change key names
            array_walk($userExtendedAttributes, function (&$row) use($rbacRoles) {
                foreach ($row as $key => $value) {
                    $string = $key;
                    $string = strtolower($string);
                    $string = str_replace(["uea_"], "", $string);
                    $string = str_replace("_", " ", $string);
                    $string = ucwords($string);
                    $string = lcfirst($string);
                    $string = str_replace(" ", "", $string);
                    $row[$string] = $row[$key];
                    unset($row[$key]);
                }
                //format owner
                $row["owner"] = $row["usrUsername"];
                //format roles
                $rolesLabel = [];
                if (is_array($rbacRoles)) {
                    $roles = G::json_decode($row["roles"]);
                    foreach ($roles as $rol) {
                        foreach ($rbacRoles as $item) {
                            if (isset($item->ROL_CODE) && $rol === $item->ROL_CODE) {
                                $rolesLabel[] = $item->ROL_NAME;
                            }
                        }
                    }
                }
                $row["rolesLabel"] = $rolesLabel;
            });
            $result = [
                "data" => $userExtendedAttributes,
                "count" => $count
            ];
            echo G::json_encode($result);
            break;
        case "userExtendedAttributesList":
            $roleCode = empty($_REQUEST["roleCode"]) ? "" : $_REQUEST["roleCode"];
            $userExtendedAttributes = UserExtendedAttributes::query()
                    ->where("UEA_ROLES", "like", "%\"{$roleCode}\"%")
                    ->orWhere("UEA_OPTION", "=", "allUser")
                    ->orderBy("UEA_NAME")
                    ->get()
                    ->toArray();
            //change key names
            array_walk($userExtendedAttributes, function (&$row) {
                foreach ($row as $key => $value) {
                    $string = $key;
                    $string = strtolower($string);
                    $string = str_replace(["uea_"], "", $string);
                    $string = str_replace("_", " ", $string);
                    $string = ucwords($string);
                    $string = lcfirst($string);
                    $string = str_replace(" ", "", $string);
                    $row[$string] = $row[$key];
                    unset($row[$key]);
                }
            });
            $result = [
                "data" => $userExtendedAttributes
            ];
            echo json_encode($result);
            break;
        case "listByRol":
            $rolCode = empty($_REQUEST["rolCode"]) ? "" : $_REQUEST["rolCode"];
            $userExtendedAttributes = UserExtendedAttributes::query()
                    ->Where("UEA_ROLES", 'LIKE', "%{$rolCode}%")
                    ->orderBy("UEA_NAME", "asc")
                    ->get()
                    ->toArray();
            $default = [
                ["value" => "USR_FIRSTNAME", "text" => "USR_FIRSTNAME", "extended" => false],
                ["value" => "USR_LASTNAME", "text" => "USR_LASTNAME", "extended" => false],
                ["value" => "USR_EMAIL", "text" => "USR_EMAIL", "extended" => false],
                ["value" => "USR_DUE_DATE", "text" => "USR_DUE_DATE", "extended" => false],
                ["value" => "USR_STATUS", "text" => "USR_STATUS", "extended" => false],
                ["value" => "USR_STATUS_ID", "text" => "USR_STATUS_ID", "extended" => false],
                ["value" => "USR_ADDRESS", "text" => "USR_ADDRESS", "extended" => false],
                ["value" => "USR_PHONE", "text" => "USR_PHONE", "extended" => false],
                ["value" => "USR_FAX", "text" => "USR_FAX", "extended" => false],
                ["value" => "USR_CELLULAR", "text" => "USR_CELLULAR", "extended" => false],
                ["value" => "USR_ZIP_CODE", "text" => "USR_ZIP_CODE", "extended" => false],
                ["value" => "USR_POSITION", "text" => "USR_POSITION", "extended" => false],
                ["value" => "USR_BIRTHDAY", "text" => "USR_BIRTHDAY", "extended" => false],
                ["value" => "USR_COST_BY_HOUR", "text" => "USR_COST_BY_HOUR", "extended" => false],
                ["value" => "USR_UNIT_COST", "text" => "USR_UNIT_COST", "extended" => false],
                ["value" => "USR_PMDRIVE_FOLDER_UID", "text" => "USR_PMDRIVE_FOLDER_UID", "extended" => false],
                ["value" => "USR_BOOKMARK_START_CASES", "text" => "USR_BOOKMARK_START_CASES", "extended" => false],
                ["value" => "USR_TIME_ZONE", "text" => "USR_TIME_ZONE", "extended" => false],
                ["value" => "USR_DEFAULT_LANG", "text" => "USR_DEFAULT_LANG", "extended" => false],
                ["value" => "USR_LAST_LOGIN", "text" => "USR_LAST_LOGIN", "extended" => false]
            ];
            $data = [];
            foreach ($userExtendedAttributes as $key => $value) {
                $data[] = [
                    "value" => $value["UEA_ATTRIBUTE_ID"],
                    "text" => $value["UEA_NAME"],
                    "extended" => true
                ];
            }
            $data = array_merge($data, $default);
            $result = [
                "data" => $data
            ];
            echo G::json_encode($result);
            break;
        case "save":
            $userUid = empty($_SESSION['USER_LOGGED']) ? RBAC::ADMIN_USER_UID : $_SESSION['USER_LOGGED'];
            $user = new Users();
            $user = $user->load($userUid);
            $userId = empty($user['USR_ID']) ? null : $user['USR_ID'];
            $id = empty($_REQUEST["UEA_ID"]) ? "" : $_REQUEST["UEA_ID"];
            $name = empty($_REQUEST["UEA_NAME"]) ? "" : $_REQUEST["UEA_NAME"];
            $attributeId = empty($_REQUEST["UEA_ATTRIBUTE_ID"]) ? "" : $_REQUEST["UEA_ATTRIBUTE_ID"];
            $hidden = empty($_REQUEST["UEA_HIDDEN"]) ? "" : $_REQUEST["UEA_HIDDEN"];
            $required = empty($_REQUEST["UEA_REQUIRED"]) ? "" : $_REQUEST["UEA_REQUIRED"];
            $password = empty($_REQUEST["UEA_PASSWORD"]) ? "" : $_REQUEST["UEA_PASSWORD"];
            $option = empty($_REQUEST["UEA_OPTION"]) ? "" : $_REQUEST["UEA_OPTION"];
            $roles = empty($_REQUEST["UEA_ROLES"]) ? "" : $_REQUEST["UEA_ROLES"];
            $owner = empty($_REQUEST["UEA_OWNER"]) ? $userId : $_REQUEST["UEA_OWNER"];
            $dateCreate = empty($_REQUEST["UEA_DATE_CREATE"]) ? date("Y-m-d H:i:s") : $_REQUEST["UEA_DATE_CREATE"];

            $userExtendedAttributes = UserExtendedAttributes::where('UEA_ID', '=', $id)
                    ->first();
            if (empty($userExtendedAttributes)) {
                $userExtendedAttributes = new UserExtendedAttributes();
            }
            $userExtendedAttributes->UEA_NAME = trim($name);
            $userExtendedAttributes->UEA_ATTRIBUTE_ID = trim($attributeId);
            $userExtendedAttributes->UEA_HIDDEN = $hidden;
            $userExtendedAttributes->UEA_REQUIRED = $required;
            $userExtendedAttributes->UEA_PASSWORD = $password;
            $userExtendedAttributes->UEA_OPTION = $option;
            $userExtendedAttributes->UEA_ROLES = $roles;
            $userExtendedAttributes->UEA_OWNER = $owner;
            $userExtendedAttributes->UEA_DATE_CREATE = $dateCreate;
            $userExtendedAttributes->save();

            echo G::json_encode($userExtendedAttributes);
            break;
        case "delete":
            $id = empty($_REQUEST["id"]) ? "" : $_REQUEST["id"];
            if (!empty($id)) {
                $id = UserExtendedAttributes::where('UEA_ID', '=', $id)
                        ->delete();
            }
            echo G::json_encode($id);
            break;
        case "verifyName":
            $id = empty($_REQUEST["id"]) ? "" : $_REQUEST["id"];
            $name = empty($_REQUEST["name"]) ? "" : $_REQUEST["name"];
            $userExtendedAttributes = UserExtendedAttributes::query()
                    ->where('UEA_NAME', '=', trim($name))
                    ->where('UEA_ID', '<>', $id)
                    ->first();
            $result = [
                "valid" => empty($userExtendedAttributes),
                "message" => empty($userExtendedAttributes) ? "" : G::loadTranslation("ID_NAME_EXISTS")
            ];
            echo G::json_encode($result);
            break;
        case "verifyAttributeId":
            $id = empty($_REQUEST["id"]) ? "" : $_REQUEST["id"];
            $attributeId = empty($_REQUEST["attributeId"]) ? "" : $_REQUEST["attributeId"];
            $userExtendedAttributes = UserExtendedAttributes::query()
                    ->where('UEA_ATTRIBUTE_ID', '=', trim($attributeId))
                    ->where('UEA_ID', '<>', $id)
                    ->first();
            $result = [
                "valid" => empty($userExtendedAttributes),
                "message" => empty($userExtendedAttributes) ? "" : G::loadTranslation("ID_EXIST")
            ];
            echo G::json_encode($result);
            break;
        case "verifyAttributeUse":
            $name = empty($_REQUEST["name"]) ? "" : $_REQUEST["name"];
            $attributeId = empty($_REQUEST["attributeId"]) ? "" : $_REQUEST["attributeId"];
            $user = User::query()
                    ->where("USR_EXTENDED_ATTRIBUTES_DATA", "LIKE", "%\"{$attributeId}\"%")
                    ->first();
            $isUsed = false;
            $message = "";
            if (!empty($user)) {
                $isUsed = true;
                $message = G::loadTranslation("ID_THE_ATTRIBUTE_HAS_ALREADY_INFORMATION_STORED_FOR_USERS_PLEASE_CONFIRM_THE_DELETE", [$name]);
            }
            $result = [
                "isUsed" => $isUsed,
                "message" => $message
            ];
            echo G::json_encode($result);
            break;
        default:
            $conf = new Configurations();
            $pageSize = $conf->getEnvSetting('casesListRowNumber');
            $pageSize = empty($pageSize) ? 25 : $pageSize;
            $lang = defined("SYS_LANG") ? SYS_LANG : "en";

            $html = file_get_contents(PATH_HTML . "lib/userExtendedAttributes/index.html");
            $html = str_replace("var pageSize=10;", "var pageSize={$pageSize};", $html);
            $html = str_replace("translation.en.js", "translation.{$lang}.js", $html);
            echo $html;
            break;
    }
} catch (Exception $e) {
    $message = [
        'MESSAGE' => $e->getMessage()
    ];
    $G_PUBLISH->AddContent('xmlform', 'xmlform', 'login/showMessage', '', $message);
    G::RenderPage('publish', 'blank');
}