<?php

namespace ProcessMaker\BusinessModel;


use Application;
use DateTime;
use Department;
use Exception;
use G;
use Process;
use ProcessMaker\Util\DateTime as UtilDateTime;
use ProcessCategory;
use Triggers;
use Users;

/**
 * Validator fields
 *
 * @protected
 */
class Validator
{
    /**
     * Validate dep_uid
     * 
     * @param string $dep_uid . Uid for Departament
     * @param string $nameField . Name of field for message
     *
     * @access public
     *
     * @return string
     */
    static public function depUid($dep_uid, $nameField = 'dep_uid')
    {
        $dep_uid = trim($dep_uid);
        if (empty($dep_uid)) {
            throw new Exception(G::LoadTranslation("ID_DEPARTMENT_NOT_EXIST", [$nameField, '']));
        }
        $department = new Department();
        if (!($department->existsDepartment($dep_uid))) {
            throw new Exception(G::LoadTranslation("ID_DEPARTMENT_NOT_EXIST", [$nameField, $dep_uid]));
        }
        return $dep_uid;
    }

    /**
     * Validate dep_status
     * 
     * @param string $dep_uid . Uid for Departament
     * @param string $nameField . Name of field for message
     *
     * @access public
     *
     * @return string
     */
    static public function depStatus($dep_status)
    {
        $dep_status = trim($dep_status);
        $values = ['ACTIVE', 'INACTIVE'];
        if (!in_array($dep_status, $values)) {
            throw new Exception(G::LoadTranslation("ID_DEPARTMENT_NOT_EXIST", ['dep_status', $dep_status]));
        }
        return $dep_status;
    }

    /**
     * Validate usr_uid
     *
     * @param string $usr_uid , Uid for user
     * @param string $nameField . Name of field for message
     *
     * @access public
     *
     * @return string
     */
    static public function usrUid($usr_uid, $nameField = 'usr_uid')
    {
        $usr_uid = trim($usr_uid);
        if (empty($usr_uid)) {
            throw new Exception(G::LoadTranslation("ID_USER_NOT_EXIST", [$nameField, '']));
        }
        $users = new Users();
        if (!($users->userExists($usr_uid))) {
            throw new Exception(G::LoadTranslation("ID_USER_NOT_EXIST", [$nameField, $usr_uid]));
        }
        return $usr_uid;
    }

    /**
     * Validate app_uid
     *
     * @param string $app_uid , Uid for application
     * @param string $nameField , Name of field for message
     *
     * @access public
     *
     * @return string
     */
    static public function appUid($app_uid, $nameField = 'app_uid')
    {
        $app_uid = trim($app_uid);
        if (empty($app_uid)) {
            throw new Exception(G::LoadTranslation("ID_APPLICATION_NOT_EXIST", [$nameField, '']));
        }
        if (strlen($app_uid) !== 32) {
            throw new Exception(G::LoadTranslation("ID_CASE_NOT_EXISTS"));
        }
        $application = new Application();
        if (!($application->exists($app_uid))) {
            throw new Exception(G::LoadTranslation("ID_APPLICATION_NOT_EXIST", [$nameField, $app_uid]));
        }
        return $app_uid;
    }

    /**
     * Validate app_uid
     *
     * @param string $tri_uid , Uid for trigger
     * @param string $nameField . Name of field for message
     *
     * @access public
     *
     * @return string
     */
    static public function triUid($tri_uid, $nameField = 'tri_uid')
    {
        $tri_uid = trim($tri_uid);
        if (empty($tri_uid)) {
            throw new Exception(G::LoadTranslation("ID_TRIGGER_NOT_EXIST", [$nameField, '']));
        }
        $triggers = new Triggers();
        if (!($triggers->TriggerExists($tri_uid))) {
            throw new Exception(G::LoadTranslation("ID_TRIGGER_NOT_EXIST", [$nameField, $tri_uid]));
        }
        return $tri_uid;
    }

    /**
     * Validate pro_uid
     *
     * @param string $proUid , Uid for process
     * @param string $nameField . Name of field for message
     *
     * @access public
     *
     * @return int
     */
    static public function proUid($proUid, $nameField = 'pro_uid')
    {
        $proUid = trim($proUid);
        if (empty($proUid)) {
            throw new Exception(G::LoadTranslation("ID_PROCESS_NOT_EXIST", [$nameField, '']));
        }
        $process = new Process();
        $proId = 0;
        if (!($process->exists($proUid))) {
            throw new Exception(G::LoadTranslation("ID_PROCESS_NOT_EXIST", [$nameField, $proUid]));
        } else {
            $proId = $process->load($proUid)['PRO_ID'];
        }

        return $proId;
    }

    /**
     * Validate cat_uid
     *
     * @param string $cat_uid , Uid for category
     * @param string $nameField . Name of field for message
     *
     * @access public
     *
     * @return string
     */
    static public function catUid($cat_uid, $nameField = 'cat_uid')
    {
        $cat_uid = trim($cat_uid);
        if (empty($cat_uid)) {
            throw new Exception(G::LoadTranslation("ID_CATEGORY_NOT_EXIST", [$nameField, '']));
        }
        $category = new ProcessCategory();
        if (!($category->exists($cat_uid))) {
            throw new Exception(G::LoadTranslation("ID_CATEGORY_NOT_EXIST", [$nameField, $cat_uid]));
        }
        return $cat_uid;
    }

    /**
     * Validate date
     *
     * @param string $date , Date for validate
     * @param string $format
     * @param string $nameField . Name of field for message
     *
     * @access public
     *
     * @return string
     */
    static public function isDate($date, $format = 'Y-m-d H:i:s', $nameField = 'app_uid')
    {
        $date = trim($date);
        if (empty($date)) {
            throw new Exception(G::LoadTranslation("ID_DATE_NOT_VALID", ['', $format]));
        }
        $d = DateTime::createFromFormat($format, $date);
        if (!($d && $d->format($format) == $date)) {
            throw new Exception(G::LoadTranslation("ID_DATE_NOT_VALID", [$date, $format]));
        }
        return $date;
    }

    /**
     * Validate is array
     * 
     * @param string $field
     * @param string $nameField
     *
     * @access public
     *
     * @return void
     */
    static public function isArray($field, $nameField)
    {
        if (!is_array($field)) {
            throw new Exception(G::LoadTranslation("ID_INVALID_VALUE_ARRAY", [$nameField]));
        }
    }

    /**
     * Validate is string
     * 
     * @param string $field
     * @param string $nameField
     *
     * @access public
     *
     * @return void
     */
    static public function isString($field, $nameField)
    {
        if (!is_string($field)) {
            throw new Exception(G::LoadTranslation("ID_INVALID_VALUE_STRING", [$nameField]));
        }
    }

    /**
     * Validate is integer
     * 
     * @param string $field
     * @param string $nameField
     *
     * @access public
     *
     * @return void
     */
    static public function isInteger($field, $nameField)
    {
        if (!is_integer($field)) {
            throw new Exception(G::LoadTranslation("ID_INVALID_VALUE_INTEGER", [$nameField]));
        }
    }

    /**
     * Validate is boolean
     * 
     * @param string $field
     * @param string $nameField
     *
     * @access public
     *
     * @return void
     */
    static public function isBoolean($field, $nameField)
    {
        if (!is_bool($field)) {
            throw new Exception(G::LoadTranslation("ID_INVALID_VALUE_BOOLEAN", [$nameField]));
        }
    }

    /**
     * Validate is empty
     * 
     * @param string $field
     * @param string $nameField
     *
     * @access public
     *
     * @return void
     */
    static public function isNotEmpty($field, $nameField)
    {
        if (empty($field)) {
            throw new Exception(G::LoadTranslation("ID_INVALID_VALUE_IS_EMPTY", [$nameField]));
        }
    }

    /**
     * Validate a variable name
     * 
     * @param $nameField
     * 
     * @return void
     */
    static public function isValidVariableName($nameField)
    {
        $resp = preg_match(config('constants.validation.pmVariable.regEx'), $nameField, $matches);
        if (isset($resp) && $resp === 0) {
            throw new Exception(G::LoadTranslation("ID_INVALID_NAME", [$nameField]));
        }
    }

    /**
     * Verify if data is array
     *
     * @param string $data Data
     * @param string $dataNameForException Data name for the exception
     *
     * return void Throw exception if data is not array
     */
    static public function throwExceptionIfDataIsNotArray($data, $dataNameForException)
    {
        try {
            if (!is_array($data)) {
                throw new Exception(G::LoadTranslation("ID_INVALID_VALUE_THIS_MUST_BE_ARRAY", [$dataNameForException]));
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Verify if data is empty
     *
     * @param string $data Data
     * @param string $dataNameForException Data name for the exception
     *
     * return void Throw exception if data is empty
     */
    static public function throwExceptionIfDataIsEmpty($data, $dataNameForException)
    {
        try {
            if (empty($data)) {
                throw new Exception(G::LoadTranslation("ID_INVALID_VALUE_CAN_NOT_BE_EMPTY", [$dataNameForException]));
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate data by ISO 8601 format
     *
     * @param mixed $data Data
     * @param mixed $field Fields
     *
     * @return void Throw exception if data has an invalid value
     */
    public static function throwExceptionIfDataNotMetIso8601Format($data, $field = null)
    {
        try {
            if (!(isset($_SESSION['__SYSTEM_UTC_TIME_ZONE__']) && $_SESSION['__SYSTEM_UTC_TIME_ZONE__'])) {
                return;
            }

            $regexpDate = UtilDateTime::REGEXPDATE;
            $regexpTime = UtilDateTime::REGEXPTIME;

            $regexpIso8601 = $regexpDate . 'T' . $regexpTime . '[\+\-]\d{2}:\d{2}';

            switch (gettype($data)) {
                case 'string':
                    if (trim($data) != '' && !preg_match('/^' . $regexpIso8601 . '$/', $data)) {
                        throw new Exception(G::LoadTranslation('ID_ISO8601_INVALID_FORMAT', [(!is_null($field) && is_string($field)) ? $field : $data]));
                    }
                    break;
                case 'array':
                    if (!is_null($field) && is_array($field)) {
                        foreach ($field as $value) {
                            $fieldName = $value;

                            $fieldName = (isset($data[strtoupper($fieldName)])) ? strtoupper($fieldName) : $fieldName;
                            $fieldName = (isset($data[strtolower($fieldName)])) ? strtolower($fieldName) : $fieldName;

                            if (isset($data[$fieldName]) && trim($data[$fieldName]) != '' && !preg_match('/^' . $regexpIso8601 . '$/', $data[$fieldName])) {
                                throw new Exception(G::LoadTranslation('ID_ISO8601_INVALID_FORMAT', [$fieldName]));
                            }
                        }
                    }
                    break;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate pager data
     *
     * @param array $arrayData Data
     * @param array $arrayVariableNameForException Variable name for exception
     *
     * @return mixed Returns TRUE when pager data is valid, Message Error otherwise
     */
    public static function validatePagerDataByPagerDefinition($arrayPagerData, $arrayVariableNameForException)
    {
        try {
            foreach ($arrayPagerData as $key => $value) {
                $nameForException = (isset($arrayVariableNameForException[$key])) ?
                    $arrayVariableNameForException[$key] : $key;

                if (!is_null($value) &&
                    (
                        (string)($value) == '' ||
                        !preg_match('/^(?:\+|\-)?(?:0|[1-9]\d*)$/', $value . '') ||
                        (int)($value) < 0
                    )
                ) {
                    return G::LoadTranslation('ID_INVALID_VALUE_EXPECTING_POSITIVE_INTEGER', [$nameForException]);
                }
            }

            //Return
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
}

