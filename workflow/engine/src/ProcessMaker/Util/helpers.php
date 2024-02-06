<?php

use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ProcessMaker\Model\User;

/**
 * We will send a case note in the actions by email
 * @param object $httpData
 * @return void
 */
function postNote($httpData)
{
    $appUid = (isset($httpData->appUid)) ? $httpData->appUid : '';
    $usrUid = (isset($httpData->usrUid)) ? $httpData->usrUid : '';
    $delIndex = (isset($httpData->delIndex)) ? $httpData->delIndex : 0;
    $appNotes = new AppNotes();
    $noteContent = addslashes($httpData->noteText);
    $result = $appNotes->postNewNote($appUid, $usrUid, $noteContent, false);

    //send the response to client
    @ini_set('implicit_flush', 1);
    ob_start();
    @ob_flush();
    @flush();
    @ob_end_flush();
    ob_implicit_flush(true);

    //send notification in background
    $noteRecipientsList = array();
    $oCase = new Cases();
    $p = $oCase->getUsersParticipatedInCase($appUid);
    foreach ($p['array'] as $key => $userParticipated) {
        $noteRecipientsList[] = $key;
    }

    $noteRecipients = implode(",", $noteRecipientsList);
    $appNotes->sendNoteNotification($appUid, $usrUid, $noteContent, $noteRecipients, '', $delIndex);
}

/**
 * We will get to the abeRequest data from actions by email
 * @param string $AbeRequestsUid
 * @return array $abeRequests
 */
function loadAbeRequest($AbeRequestsUid)
{
    $criteria = new Criteria();
    $criteria->add(AbeRequestsPeer::ABE_REQ_UID, $AbeRequestsUid);
    $resultRequests = AbeRequestsPeer::doSelectRS($criteria);
    $resultRequests->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $resultRequests->next();
    $abeRequests = $resultRequests->getRow();

    return $abeRequests;
}

/**
 * We will get the AbeConfiguration by actions by email
 * @param string $AbeConfigurationUid
 * @return array $abeConfiguration
 */
function loadAbeConfiguration($AbeConfigurationUid)
{
    $criteria = new Criteria();
    $criteria->add(AbeConfigurationPeer::ABE_UID, $AbeConfigurationUid);
    $result = AbeConfigurationPeer::doSelectRS($criteria);
    $result->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $result->next();
    $abeConfiguration = $result->getRow();

    return $abeConfiguration;
}

/**
 * We will update the request by actions by email
 * @param array $data
 * @return void
 * @throws Exception
 */
function uploadAbeRequest($data)
{
    try {
        $abeRequestsInstance = new AbeRequests();
        $abeRequestsInstance->createOrUpdate($data);
    } catch (Exception $error) {
        throw $error;
    }
}

/**
 * Function getDynaformsVars
 *
 * @access public
 * @param eter string $sProcessUID
 * @param eter boolean $bSystemVars
 * @return array
 */
function getDynaformsVars($sProcessUID, $typeVars = 'all', $bIncMulSelFields = 0)
{
    $aFields = array();
    $aFieldsNames = array();
    if ($typeVars == 'system' || $typeVars == 'all') {
        $aAux = G::getSystemConstants();
        foreach ($aAux as $sName => $sValue) {
            $aFields[] = array('sName' => $sName, 'sType' => 'system', 'sLabel' => G::LoadTranslation('ID_TINY_SYSTEM_VARIABLES'));
        }
        //we're adding the pin variable to the system list
        $aFields[] = array('sName' => 'PIN', 'sType' => 'system', 'sLabel' => G::LoadTranslation('ID_TINY_SYSTEM_VARIABLES'));

        //we're adding the app_number variable to the system list
        $aFields[] = array('sName' => 'APP_NUMBER', 'sType' => 'system', 'sLabel' => G::LoadTranslation('ID_TINY_SYSTEM_VARIABLE'), 'sUid' => '');
    }

    $aInvalidTypes = array("title", "subtitle", "file", "button", "reset", "submit", "javascript", "pmconnection");
    $aMultipleSelectionFields = array("listbox", "checkgroup");

    if ($bIncMulSelFields != 0) {
        $aInvalidTypes = array_merge($aInvalidTypes, $aMultipleSelectionFields);
    }
    // getting bpmn projects
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(BpmnProjectPeer::PRJ_UID);
    $oCriteria->add(BpmnProjectPeer::PRJ_UID, $sProcessUID);
    $oDataset = ProcessPeer::doSelectRS($oCriteria, Propel::getDbConnection('workflow_ro'));
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    $row = $oDataset->getRow();
    if (isset($row["PRJ_UID"])) {
        if ($typeVars == 'process' || $typeVars == 'all') {
            $oCriteria = new Criteria('workflow');
            $oCriteria->addSelectColumn(ProcessVariablesPeer::VAR_UID);
            $oCriteria->addSelectColumn(ProcessVariablesPeer::VAR_NAME);
            $oCriteria->addSelectColumn(ProcessVariablesPeer::VAR_FIELD_TYPE);
            $oCriteria->add(ProcessVariablesPeer::PRJ_UID, $sProcessUID);
            $oDataset = ProcessVariablesPeer::doSelectRS($oCriteria);
            $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
            while ($oDataset->next()) {
                $row = $oDataset->getRow();
                array_push($aFields, array(
                    "sName" => $row["VAR_NAME"],
                    "sType" => $row["VAR_FIELD_TYPE"],
                    "sLabel" => $row["VAR_FIELD_TYPE"]
                ));
            }
        }
        if ($typeVars == 'grid' || $typeVars == 'all') {
            $oC = new Criteria('workflow');
            $oC->addSelectColumn(DynaformPeer::DYN_CONTENT);
            $oC->add(DynaformPeer::PRO_UID, $sProcessUID);
            $oC->add(DynaformPeer::DYN_TYPE, 'xmlform');
            $oData = DynaformPeer::doSelectRS($oC);
            $oData->setFetchmode(ResultSet::FETCHMODE_ASSOC);
            $oData->next();
            while ($aRowd = $oData->getRow()) {
                $dynaform = G::json_decode($aRowd['DYN_CONTENT'], true);
                if (is_array($dynaform) && sizeof($dynaform)) {
                    $items = $dynaform['items'][0]['items'];
                    foreach ($items as $key => $val) {
                        if (isset($val[0]['type']) && $val[0]['type'] == 'grid') {
                            if (sizeof($val[0]['columns'])) {
                                $columns = $val[0]['columns'];
                                foreach ($columns as $column) {
                                    array_push($aFields, array(
                                        "sName" => $column['name'],
                                        "sType" => $column['type'],
                                        "sLabel" => $column['type']
                                    ));
                                }
                            }
                        }
                    }
                }
                $oData->next();
            }
        }

    } else {
        require_once 'classes/model/Dynaform.php';
        $oCriteria = new Criteria('workflow');
        $oCriteria->addSelectColumn(DynaformPeer::DYN_FILENAME);
        $oCriteria->add(DynaformPeer::PRO_UID, $sProcessUID);
        $oCriteria->add(DynaformPeer::DYN_TYPE, 'xmlform');
        $oDataset = DynaformPeer::doSelectRS($oCriteria);
        $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $oDataset->next();
        while ($aRow = $oDataset->getRow()) {
            if (file_exists(PATH_DYNAFORM . PATH_SEP . $aRow['DYN_FILENAME'] . '.xml')) {
                $G_FORM = new Form($aRow['DYN_FILENAME'], PATH_DYNAFORM, SYS_LANG);
                if (($G_FORM->type == 'xmlform') || ($G_FORM->type == '')) {
                    foreach ($G_FORM->fields as $k => $v) {
                        if (!in_array($v->type, $aInvalidTypes)) {
                            if (!in_array($k, $aFieldsNames)) {
                                $aFields[] = array('sName' => $k, 'sType' => $v->type, 'sLabel' => ($v->type != 'grid' ? $v->label : '[ ' . G::LoadTranslation('ID_GRID') . ' ]')
                                );
                                $aFieldsNames[] = $k;
                            }
                        }
                    }
                }
            }
            $oDataset->next();
        }
    }
    return $aFields;
}

/**
 * Function getGridsVars
 *
 * @access public
 * @param eter string $sProcessUID
 * @return array
 */
function getGridsVars($sProcessUID)
{
    $aFields = array();
    $aFieldsNames = array();

    require_once 'classes/model/Dynaform.php';
    $oCriteria = new Criteria('workflow');
    $oCriteria->addSelectColumn(DynaformPeer::DYN_FILENAME);
    $oCriteria->add(DynaformPeer::PRO_UID, $sProcessUID);
    $oDataset = DynaformPeer::doSelectRS($oCriteria);
    $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $oDataset->next();
    while ($aRow = $oDataset->getRow()) {
        $G_FORM = new Form($aRow['DYN_FILENAME'], PATH_DYNAFORM, SYS_LANG);
        if ($G_FORM->type == 'xmlform') {
            foreach ($G_FORM->fields as $k => $v) {
                if ($v->type == 'grid') {
                    if (!in_array($k, $aFieldsNames)) {
                        $aFields[] = array('sName' => $k, 'sXmlForm' => str_replace($sProcessUID . '/', '', $v->xmlGrid));
                        $aFieldsNames[] = $k;
                    }
                }
            }
        }
        $oDataset->next();
    }
    return $aFields;
}

/**
 * Function getVarsGrid returns all variables of Grid
 *
 * @access public
 * @param string proUid process ID
 * @param string dynUid dynaform ID
 * @return array
 */

function getVarsGrid($proUid, $dynUid)
{
    G::LoadClass('dynaformhandler');
    G::LoadClass('AppSolr');

    $dynaformFields = array();

    if (is_file(PATH_DATA . '/sites/' . config("system.workspace") . '/xmlForms/' . $proUid . '/' . $dynUid . '.xml') && filesize(PATH_DATA . '/sites/' . config("system.workspace") . '/xmlForms/' . $proUid . '/' . $dynUid . '.xml') > 0) {
        $dyn = new dynaFormHandler(PATH_DATA . '/sites/' . config("system.workspace") . '/xmlForms/' . $proUid . '/' . $dynUid . '.xml');
        $dynaformFields[] = $dyn->getFields();
    }

    $dynaformFieldTypes = array();

    foreach ($dynaformFields as $aDynFormFields) {
        foreach ($aDynFormFields as $field) {

            if ($field->getAttribute('validate') == 'Int') {
                $dynaformFieldTypes[$field->nodeName] = 'Int';
            } elseif ($field->getAttribute('validate') == 'Real') {
                $dynaformFieldTypes[$field->nodeName] = 'Real';
            } else {
                $dynaformFieldTypes[$field->nodeName] = $field->getAttribute('type');
            }
        }
    }
    return $dynaformFieldTypes;
}

/**
 * eprint
 *
 * @param string $s default value ''
 * @param string $c default value null
 *
 * @return void
 */
function eprint ($s = "", $c = null)
{
    if (G::isHttpRequest()) {
        if (isset( $c )) {
            echo "<pre style='color:$c'>$s</pre>";
        } else {
            echo "<pre>$s</pre>";
        }
    } else {
        if (isset( $c )) {
            switch ($c) {
                case 'green':
                    printf( "\033[0;35;32m$s\033[0m" );
                    return;
                    break;
                case 'red':
                    printf( "\033[0;35;31m$s\033[0m" );
                    return;
                    break;
                case 'blue':
                    printf( "\033[0;35;34m$s\033[0m" );
                    return;
                    break;
                default:
                    print "$s";
            }
        } else {
            print "$s";
        }
    }
}

/**
 * println
 *
 * @param string $s
 *
 * @return eprintln($s)
 */
function println ($s)
{
    return eprintln( $s );
}

/**
 * eprintln
 *
 * @param string $s
 * @param string $c
 *
 * @return void
 */
function eprintln ($s = "", $c = null)
{
    if (G::isHttpRequest()) {
        if (isset( $c )) {
            echo "<pre style='color:$c'>$s</pre>";
        } else {
            echo "<pre>$s</pre>";
        }
    } else {
        if (isset( $c ) && (PHP_OS != 'WINNT')) {
            switch ($c) {
                case 'green':
                    printf( "\033[0;35;32m$s\033[0m\n" );
                    return;
                    break;
                case 'red':
                    printf( "\033[0;35;31m$s\033[0m\n" );
                    return;
                    break;
                case 'blue':
                    printf( "\033[0;35;34m$s\033[0m\n" );
                    return;
                    break;
            }
        }
        print "$s\n";
    }
}

/**
 * Initialize the user logged session
 */
function initUserSession($usrUid, $usrName)
{
    $_SESSION['USER_LOGGED'] = $usrUid;
    $_SESSION['USR_USERNAME'] = $usrName;
    $_SESSION['USR_CSRF_TOKEN'] = Str::random(40);
}

/**
 * Verify token for an incoming request.
 *
 * @param type $request
 * @throws TokenMismatchException
 */
function verifyCsrfToken($request)
{
    $headers = getallheaders();
    $token = isset($request['_token'])
        ? $request['_token']
        : (isset($headers['X-CSRF-TOKEN'])
        ? $headers['X-CSRF-TOKEN']
        : null);
    $match = is_string($_SESSION['USR_CSRF_TOKEN'])
        && is_string($token)
        && !empty($_SESSION['USR_CSRF_TOKEN'])
        && hash_equals($_SESSION['USR_CSRF_TOKEN'], $token);
    if (!$match) {
        throw new TokenMismatchException();
    }
}

/**
 * Get the difference between to arrays
 * If the element is an array we will to keep the value from $array1
 * If the element is an object we will to keep the value from $array1
 *
 * @param array $array1
 * @param array $array2
 *
 * @return array
 */
function getDiffBetweenModifiedVariables(array $array1, array $array2)
{
    $difference = [];
    foreach ($array1 as $key => $value) {
        if (is_array($value)) {
            if ($value !== $array2[$key]) {
                $difference[$key] = $value;
            }
        } elseif (is_object($value)) {
            // When using ===, it means object variables are identical and they refer to the same instance of the same class.
            if ($value != $array2[$key]) {
                $difference[$key] = $value;
            }
        } elseif (!isset($array2[$key]) || $array2[$key] != $value) {
            $difference[$key] = $value;
        }
    }

    return $difference;
}

/**
 * Replace all supported variables prefixes to the prefix sent
 *
 * @param string $outDocFilename
 * @param string $prefix
 *
 * @return string
 *
 * @see cases_Step.php
 * @see \ProcessMaker\BusinessModel\Cases\OutputDocument::addCasesOutputDocument()
 * @link https://wiki.processmaker.com/3.2/Triggers#Typing_rules_for_Case_Variables
 */
function replacePrefixes($outDocFilename, $prefix = '@=')
{
    $outDocFile = str_replace(['@@', '@#', '@=', '@%', '@?', '@$', '@&', '@Q', '@q', '@!'], $prefix, $outDocFilename);

    return $outDocFile;
}

/**
 * Change the abbreviation of directives used in the php.ini configuration
 *
 * @param string $size
 *
 * @return string
 */
function changeAbbreviationOfDirectives($size)
{
    $sizeValue = (int)$size;

    switch (substr($size, -1)) {
        case 'K':
            return $sizeValue . 'KB';
        case 'M':
            return $sizeValue . 'MB';
        case 'G':
            return $sizeValue . 'GB';
        default:
            return $sizeValue . 'Bytes';
    }
}

/**
 * Remove reserved characters for file names, this value will be used in the headers for stream the file
 *
 * @param string $fileName
 * @param string $replacement
 *
 * @return string
 *
 * @see workflow/engine/methods/cases/cases_ShowOutputDocument.php
 *
 * @link https://docs.microsoft.com/en-us/windows/win32/fileio/naming-a-file?redirectedfrom=MSDN#file-and-directory-names
 * @link https://en.wikipedia.org/wiki/Filename#Comparison_of_filename_limitations
 */
function fixContentDispositionFilename($fileName, $replacement = '_')
{
    // The reserved characters vary depending on the S.O., but this list covers the more important
    $invalidCharacters = [
        "<", //(less than)
        ">", //(greater than)
        ":", //(colon)
        "\"", //(double quote)
        "/", //(forward slash)
        "\\", //(backslash)
        "|", //(vertical bar or pipe)
        "?", //(question mark)
        "*", //(asterisk)
    ];

    // Replace the reserved characters
    $fileName = str_replace($invalidCharacters, $replacement, $fileName);;

    // We need to encode the string in order to preserve some characters like "%"
    $fileName = rawurlencode($fileName);

    return $fileName;
}

/**
 * Get the current user CSRF token.
 *
 * @return string
 */
function csrfToken()
{
    return isset($_SESSION['USR_CSRF_TOKEN']) ? $_SESSION['USR_CSRF_TOKEN'] : '';
}

// Methods deleted in PHP 7.x, added in this file in order to keep compatibility with old libraries included/used in ProcessMaker
if (!function_exists('set_magic_quotes_runtime')) {
    function set_magic_quotes_runtime($value) {
        // This method always return false, because this method doesn't set anything from PHP version 5.3
        // http://www.php.net/manual/en/function.set-magic-quotes-runtime.php
        return false;
    }
}

/**
 * Update the USER table with the last login date
 *
 * @param array $userLog
 * @return int
 * @throws Exception
 *
 * @see workflow/engine/methods/login/authentication.php
 */
function updateUserLastLogin($userLog, $keyLastLogin = 'LOG_INIT_DATE')
{
    try {
        $filters = [];
        $filters['USR_UID'] = $userLog['USR_UID'];

        $user = User::query();
        $user->userFilters($filters);
        $res = $user->update(['USR_LAST_LOGIN' => $userLog[$keyLastLogin]]);

        return $res;
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

/**
 * Return raw query with the bindings replaced
 *
 * @param \Illuminate\Database\Eloquent\Builder $queryObject
 * @return string
 */
function toSqlWithBindings(Illuminate\Database\Eloquent\Builder $queryObject) {
    // Get some values from the object
    $bindings = $queryObject->getBindings();
    $originalQuery = $queryObject->toSql();

    // If not exist bindings, return the original query
    if (empty($bindings)) {
        return $originalQuery;
    }

    // Initializing another variables
    $queryParts = explode('?', $originalQuery);
    $pdo = $queryObject->getConnection()->getPdo();
    $query = '';

    // Walking the parts of the query replacing the bindings
    foreach ($queryParts as $index => $part) {
        if ($index < count($queryParts) - 1) {
            $query .= $part . $pdo->quote($bindings[$index]);
        }
    }

    // Return query
    return $query;
}

/**
 * Get the version of the mysql
 * 
 * @return string
 */
function getMysqlVersion()
{
    $results = DB::select(DB::raw("select version()"));
    $mysqlVersion = $results[0]->{'version()'};

    return $mysqlVersion;
}

/**
 * Get the version of the mysql
 *
 * @param string $date in the format <Y-m-d H:m:d>
 * @param string $mask
 * @param bool $caseListSetting
 *
 * @return string
 */
function applyMaskDateEnvironment($date, $mask = '', $caseListSetting = true)
{
    $result = '';
    if (empty($mask)) {
        $systemConf = new Configurations();
        $systemConf->loadConfig($obj, 'ENVIRONMENT_SETTINGS', '');
        if ($caseListSetting) {
            // Format defined in Cases list: Date Format
            $mask = isset($systemConf->aConfig['casesListDateFormat']) ? $systemConf->aConfig['casesListDateFormat'] : '';
        } else {
            // Format defined in Regional Settings: Global Date Format
            $mask = isset($systemConf->aConfig['dateFormat']) ? $systemConf->aConfig['dateFormat'] : '';
        }
    }
    if (!empty($date) && !empty($mask)) {
        $date = new DateTime($date);
        $result = $date->format($mask);
    } else {
        $result = $date;
    }

    return $result;
}

/**
 * Get the difference between two dates
 *
 * @param string $startDate
 * @param string $endDate
 *
 * @return string
 */
function getDiffBetweenDates(string $startDate, string $endDate)
{
    $result = '';
    if (!empty($startDate) && !empty($endDate)) {
        $initDate = new DateTime($startDate);
        $finishDate = new DateTime($endDate);
        $diff = $initDate->diff($finishDate);
        $format = ' %a ' . G::LoadTranslation('ID_DAY_DAYS');
        $format .= ' %H ' . G::LoadTranslation('ID_HOUR_ABBREVIATE');
        $format .= ' %I ' . G::LoadTranslation('ID_MINUTE_ABBREVIATE');
        $format .= ' %S ' . G::LoadTranslation('ID_SECOND_ABBREVIATE');
        $result = $diff->format($format);
    }

    return $result;
}

/**
 * Move the uploaded file to the documents folder
 * 
 * @param array $file
 * @param string $appUid
 * @param string $appDocUid
 * @param int $version
 * @param bool $upload
 * 
 * @return string
 */
function saveAppDocument($file, $appUid, $appDocUid, $version = 1, $upload = true)
{
    try {
        $info = pathinfo($file["name"]);
        $extension = ((isset($info["extension"])) ? $info["extension"] : "");
        $fileName = $appDocUid . "_" . $version . "." . $extension;
        $pathCase = PATH_DATA_SITE . 'files' . PATH_SEP . G::getPathFromUID($appUid) . PATH_SEP;
        $pathFile = $pathCase . $fileName;

        if ($upload) {
            G::uploadFile(
                $file["tmp_name"],
                $pathCase,
                $fileName
            );
        } else {
            G::verifyPath($pathCase, true);
            if (!copy($file["tmp_name"], $pathCase . $fileName)) {
                $pathFile = '';
            }
        }

        return $pathFile;
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * Add a specific date minutes, hours or days
 *
 * @param string $iniDate
 * @param string $timeUnit
 * @param int $time
 *
 * @return string
 *
 * @link https://www.php.net/manual/en/datetime.modify.php
 */
function calculateDate($iniDate, $timeUnit, $time)
{

    $datetime = new DateTime($iniDate);
    switch ($timeUnit) {
        case 'DAYS':
            $datetime->modify('+' . $time . ' day');
            break;
        case 'HOURS':
            $datetime->modify('+' . $time . ' hour');
            break;
        case 'MINUTES':
            $datetime->modify('+' . $time . ' minutes');
            break;
    }

    return $datetime->format('Y-m-d H:i:s');
}

/**
 * Get the constant value.
 * @param string $name
 * @param mixed $default
 * @return mixed
 */
function getConstant(string $name, $default = '')
{
    return defined($name) === true ? constant($name) : $default;
}
