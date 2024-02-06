<?php

/**
 * casesListExtJs.php
 *
 * Prepare the value of variables required for show the cases list and advanced search
 */

use ProcessMaker\Core\System;
use ProcessMaker\Plugins\PluginRegistry;

unset($_SESSION['APPLICATION']);

//get the action from GET or POST, default is todo
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'todo');
$openApplicationUid = (isset($_GET['openApplicationUid']))? $_GET['openApplicationUid'] : null;


//fix a previous inconsistency
$urlProxy = 'proxyCasesList';
if ($action == 'selfservice') {
    $action = 'unassigned';
}


$headPublisher = headPublisher::getSingleton();

//get the configuration for this action
$conf = new Configurations();
try {
    // the setup for search is the same as the Sent (participated)
    $confCasesList = $conf->getConfiguration('casesList', ($action == 'search' || $action == 'simple_search') ? 'search' : $action);

    $table = null;
    if (isset($confCasesList['PMTable'])) {
        $aditionalTable = new AdditionalTables();
        $table = $aditionalTable->load($confCasesList['PMTable']);
    }
    $confCasesList = ($table != null) ? $confCasesList : [];

    $generalConfCasesList = $conf->getConfiguration('ENVIRONMENT_SETTINGS', '');
} catch (Exception $e) {
    $confCasesList = [];
    $generalConfCasesList = [];
}

// reassign header configuration
$confReassignList = getReassignList();

// evaluates an action and the configuration for the list that will be rendered
$config = getAdditionalFields($action, $confCasesList);

$columns = $config['caseColumns'];
$readerFields = $config['caseReaderFields'];
$reassignColumns = $confReassignList['caseColumns'];
$reassignReaderFields = $confReassignList['caseReaderFields'];

// if the general settings has been set the pagesize values are extracted from that record
if (isset($generalConfCasesList['casesListRowNumber']) && ! empty($generalConfCasesList['casesListRowNumber'])) {
    $pageSize = intval($generalConfCasesList['casesListRowNumber']);
} else {
    $pageSize = intval($config['rowsperpage']);
}

// if the general settings has been set the dateFormat values are extracted from that record
if (isset($generalConfCasesList['casesListDateFormat']) && ! empty($generalConfCasesList['casesListDateFormat'])) {
    $dateFormat = $generalConfCasesList['casesListDateFormat'];
} else {
    $dateFormat = $config['dateformat'];
}

if ($action == 'selfservice') {
    array_unshift($columns, ['header' => '','width' => 50,'sortable' => false,'id' => 'viewLink']);
}

$userUid = (isset($_SESSION['USER_LOGGED']) && $_SESSION['USER_LOGGED'] != '') ? $_SESSION['USER_LOGGED'] : null;

$solrEnabled = 0;
if ($action == "todo" || $action == "draft" || $action == "sent" || $action == "selfservice" ||
    $action == "unassigned" || $action == "search") {
    $solrConfigured = ($solrConf = System::solrEnv()) !== false ? 1 : 0;
    if ($solrConfigured == 1) {
        $applicationSolrIndex = new AppSolr(
            $solrConf['solr_enabled'],
            $solrConf['solr_host'],
            $solrConf['solr_instance']
        );
        if ($applicationSolrIndex->isSolrEnabled()) {
            $solrEnabled = 1;
        }
    }
}

//Get values for the comboBoxes
$processes = [];
$processes[] = ['', G::LoadTranslation('ID_ALL_PROCESS')];
$status = getStatusArray($action);
$category = getCategoryArray();
$columnToSearch = getColumnsSearchArray();
$headPublisher->assign('reassignReaderFields', $reassignReaderFields); //sending the fields to get from proxy
$headPublisher->addExtJsScript('cases/reassignList', false);
$enableEnterprise = false;
if (class_exists('enterprisePlugin')) {
    $enableEnterprise = true;
    $headPublisher->addExtJsScript(PATH_PLUGINS . "enterprise" . PATH_SEP . "advancedTools" . PATH_SEP, false, true);
}

//Get user preferences
$filters = $conf->getUserPreferences('FILTERS', $userUid);

$headPublisher->assign('pageSize', $pageSize); //sending the page size
$headPublisher->assign('columns', $columns); //sending the columns to display in grid
$headPublisher->assign('readerFields', $readerFields); //sending the fields to get from proxy
$headPublisher->assign('reassignColumns', $reassignColumns); //sending the columns to display in grid
$headPublisher->assign('action', $action); //sending the action to make
$headPublisher->assign('urlProxy', $urlProxy); //sending the urlProxy to make
$headPublisher->assign('PMDateFormat', $dateFormat); //sending the fields to get from proxy
$headPublisher->assign('statusValues', $status); //Sending the listing of status
$headPublisher->assign('processValues', $processes); //Sending the listing of processes
$headPublisher->assign('categoryValues', $category); //Sending the listing of categories
$headPublisher->assign('solrEnabled', $solrEnabled); //Sending the status of solar
$headPublisher->assign('enableEnterprise', $enableEnterprise); //sending the page size
$headPublisher->assign('columnSearchValues', $columnToSearch); //Sending the list of column for search: caseTitle, caseNumber, tasTitle
$headPublisher->assign('filtersValues', $filters); //Sending filters defined
$headPublisher->assign('workspace', config('system.workspace'));


/** Define actions menu in the cases list */
$reassignCase = ($RBAC->userCanAccess('PM_REASSIGNCASE') == 1) ? 'true' : 'false';
$reassignCaseSup = ($RBAC->userCanAccess('PM_REASSIGNCASE_SUPERVISOR') == 1) ? 'true' : 'false';
$headPublisher->assign('varReassignCase', $reassignCase);
$headPublisher->assign('varReassignCaseSupervisor', $reassignCaseSup);

$deleteCase = ($RBAC->userCanAccess('PM_DELETECASE') == 1) ? 'true' : 'false';
$headPublisher->assign('varDeleteCase', $deleteCase);

$c = new Configurations();
$headPublisher->addExtJsScript('app/main', true);
$headPublisher->addExtJsScript('cases/casesList', false); //adding a javascript file .js
$headPublisher->addContent('cases/casesListExtJs'); //adding a html file  .html.
$headPublisher->assign('FORMATS', $c->getFormats());
$headPublisher->assign('userUid', $userUid);
$headPublisher->assign('isIE', Bootstrap::isIE());
$headPublisher->assign('__OPEN_APPLICATION_UID__', $openApplicationUid);

$pluginRegistry = PluginRegistry::loadSingleton();
$fromPlugin = $pluginRegistry->getOpenReassignCallback();
$jsFunction = false;
if (sizeof($fromPlugin)) {
    /** @var \ProcessMaker\Plugins\Interfaces\OpenReassignCallback $jsFile */
    foreach ($fromPlugin as $jsFile) {
        $jsFile = $jsFile->getCallBackFile();
        if (is_file($jsFile)) {
            $jsFile = file_get_contents($jsFile);
            if (!empty($jsFile)) {
                $jsFunction[] = $jsFile;
            }
        }
    }
}
$headPublisher->assign('openReassignCallback', $jsFunction);
G::RenderPage('publish', 'extJs');

/**
 * Return the list of categories
 *
 * @return array
*/
function getCategoryArray()
{
    $category = [];
    $category[] = ["", G::LoadTranslation("ID_ALL_CATEGORIES")];

    $criteria = new Criteria('workflow');
    $criteria->addSelectColumn(ProcessCategoryPeer::CATEGORY_UID);
    $criteria->addSelectColumn(ProcessCategoryPeer::CATEGORY_NAME);
    $criteria->addAscendingOrderByColumn(ProcessCategoryPeer::CATEGORY_NAME);

    $dataset = ProcessCategoryPeer::doSelectRS($criteria);
    $dataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
    $dataset->next();

    while ($row = $dataset->getRow()) {
        $category[] = [$row['CATEGORY_UID'], $row['CATEGORY_NAME']];
        $dataset->next();
    }

    return $category;
}

/**
 * Return the list of task status
 *
 * @return array
 */
function getTaskStatus()
{
    $taskStatus = [];
    $taskStatus[] = ['', G::LoadTranslation('ID_ALL_STATUS')];
    $taskStatus[] = ['ON_TIME', G::LoadTranslation('ID_ON_TIME')];
    $taskStatus[] = ['AT_RISK', G::LoadTranslation('ID_AT_RISK')];
    $taskStatus[] = ['OVERDUE', G::LoadTranslation('ID_TASK_OVERDUE')];

    return $taskStatus;
}

/**
 * Return the list of status
 *
 * @param string $action
 *
 * @return array
 */
function getStatusArray($action)
{
    $status = [];
    $statusValues = Application::$app_status_values;
    $status[] = ['', G::LoadTranslation('ID_ALL_STATUS')];
    foreach ($statusValues as $key => $value) {
        if ($action == 'search') {
            $status[] = [$value, G::LoadTranslation('ID_CASES_STATUS_' . $key)];
        } else {
            $status[] = [$key, G::LoadTranslation('ID_CASES_STATUS_' . $key)];
        }
    }
    return $status;
}

/**
 * Get the list configuration headers of the cases checked for reassign, for the reassign cases list.
 *
 * @return array
 */
function getReassignList()
{
    $caseColumns = [];
    $caseColumns[] = ['header' => '#', 'dataIndex' => 'APP_NUMBER', 'width' => 40];
    $caseColumns[] = [
        'header' => G::LoadTranslation('ID_SUMMARY'),
        'dataIndex' => 'CASE_SUMMARY',
        'width' => 45,
        'hidden' => true
    ];
    $caseColumns[] = [
        'header' => G::LoadTranslation('ID_CASES_NOTES'),
        'dataIndex' => 'CASE_NOTES_COUNT',
        'width' => 45,
        'hidden' => true
    ];
    $caseColumns[] = [
        'header' => G::LoadTranslation('ID_CASE'),
        'dataIndex' => 'APP_TITLE',
        'width' => 100,
        'hidden' => true
    ];
    $caseColumns[] = [
        'header' => 'CaseId',
        'dataIndex' => 'APP_UID',
        'width' => 200,
        'hidden' => true,
        'hideable' => false
    ];
    $caseColumns[] = [
        'header' => 'User',
        'dataIndex' => 'USR_UID',
        'width' => 200,
        'hidden' => true,
        'hideable' => false
    ];
    $caseColumns[] = [
        'header' => G::LoadTranslation('ID_TASK'),
        'dataIndex' => 'APP_TAS_TITLE',
        'width' => 120
    ];
    $caseColumns[] = [
        'header' => G::LoadTranslation('ID_PROCESS'),
        'dataIndex' => 'APP_PRO_TITLE',
        'width' => 120
    ];
    $caseColumns[] = [
        'header' => 'Reassigned Uid',
        'dataIndex' => 'APP_REASSIGN_USER_UID',
        'width' => 120,
        'hidden' => true,
        'hideable' => false
    ];
    $caseColumns[] = [
        'header' => 'Reassigned Uid',
        'dataIndex' => 'TAS_UID',
        'width' => 120,
        'hidden' => true,
        'hideable' => false
    ];
    $caseColumns[] = [
        'header' => G::LoadTranslation('ID_ASSIGNED_TO'),
        'dataIndex' => 'APP_CURRENT_USER',
        'width' => 170
    ];
    $caseColumns[] = [
        'header' => G::LoadTranslation('ID_REASSIGNED_TO'),
        'dataIndex' => 'APP_REASSIGN_USER',
        'width' => 170
    ];
    $caseColumns[] = [
        'header' => G::LoadTranslation('ID_REASON'),
        'dataIndex' => 'NOTE_REASON',
        'width' => 170
    ];
    $caseColumns[] = [
        'header' => G::LoadTranslation('ID_NOTIFY'),
        'dataIndex' => 'NOTIFY_REASSIGN',
        'width' => 100
    ];

    $caseReaderFields = [];
    $caseReaderFields[] = ['name' => 'APP_NUMBER'];
    $caseReaderFields[] = ['name' => 'APP_TITLE'];
    $caseReaderFields[] = ['name' => 'APP_UID'];
    $caseReaderFields[] = ['name' => 'USR_UID'];
    $caseReaderFields[] = ['name' => 'APP_TAS_TITLE'];
    $caseReaderFields[] = ['name' => 'APP_PRO_TITLE'];
    $caseReaderFields[] = ['name' => 'APP_REASSIGN_USER_UID'];
    $caseReaderFields[] = ['name' => 'TAS_UID'];
    $caseReaderFields[] = ['name' => 'APP_REASSIGN_USER'];
    $caseReaderFields[] = ['name' => 'CASE_SUMMARY'];
    $caseReaderFields[] = ['name' => 'CASE_NOTES_COUNT'];
    $caseReaderFields[] = ['name' => 'APP_CURRENT_USER'];

    return [
        'caseColumns' => $caseColumns,
        'caseReaderFields' => $caseReaderFields,
        'rowsperpage' => 20,
        'dateformat' => 'M d, Y'
    ];
}

/**
 * Get the list of users for reassign
 *
 * @return array
*/
function getReassignUsersList()
{
    $caseColumns = [];

    $caseReaderFields = [];
    $caseReaderFields[] = ['name' => 'userUid'];
    $caseReaderFields[] = ['name' => 'userFullname'];

    return [
        'caseColumns' => $caseColumns,
        'caseReaderFields' => $caseReaderFields,
        'rowsperpage' => 20,
        'dateformat' => 'M d, Y'
    ];
}

/**
 * Load the PM Table field list from the database based in an action parameter
 * then assemble the List of fields with these data, for the configuration in cases list.
 *
 * @param string $action
 * @param array $confCasesList
 *
 * @return array
 *
 */
function getAdditionalFields($action, $confCasesList = [])
{
    $config = new Configurations();
    $arrayConfig = $config->casesListDefaultFieldsAndConfig($action);

    if (is_array($confCasesList) && count($confCasesList) > 0 && isset($confCasesList["second"]) && count($confCasesList["second"]["data"]) > 0) {
        //For the case list builder in the enterprise plugin
        $caseColumns = [];
        $caseReaderFields = [];
        $caseReaderFieldsAux = [];

        foreach ($confCasesList["second"]["data"] as $index1 => $value1) {
            $arrayField = $value1;

            if ($arrayField["fieldType"] != "key" && $arrayField["name"] != "USR_UID" && $arrayField["name"] != "PREVIOUS_USR_UID") {
                $arrayAux = [];

                foreach ($arrayField as $index2 => $value2) {
                    if ($index2 != "gridIndex" && $index2 != "fieldType") {
                        $indexAux = $index2;
                        $valueAux = $value2;

                        switch ($index2) {
                            case "name":
                                $indexAux = "dataIndex";
                                break;
                            case "label":
                                $indexAux = "header";

                                if (preg_match("/^\*\*(.+)\*\*$/", $value2, $arrayMatch)) {
                                    $valueAux = G::LoadTranslation($arrayMatch[1]);
                                }
                                break;
                        }
                        $arrayAux[$indexAux] = $valueAux;
                    }
                }

                $caseColumns[] = $arrayAux;
                $caseReaderFields[] = ["name" => $arrayField["name"]];

                $caseReaderFieldsAux[] = $arrayField["name"];
            }
        }
        foreach ($arrayConfig["caseReaderFields"] as $index => $value) {
            if (!in_array($value["name"], $caseReaderFieldsAux)) {
                $caseReaderFields[] = $value;
            }
        }

        $arrayConfig = [
            "caseColumns" => $caseColumns,
            "caseReaderFields" => $caseReaderFields,
            "rowsperpage" => $confCasesList["rowsperpage"],
            "dateformat" => $confCasesList["dateformat"]
        ];
    }

    return $arrayConfig;
}

/**
 * This function define the possibles columns for apply the specific search
 *
 * @return array $filters values of the dropdown
 */
function getColumnsSearchArray()
{
    $filters = [];
    $filters[] = ['APP_TITLE', G::LoadTranslation('ID_CASE_TITLE')];
    $filters[] = ['APP_NUMBER', G::LoadTranslation('ID_CASE_NUMBER')];
    $filters[] = ['TAS_TITLE', G::LoadTranslation('ID_TASK')];

    return $filters;
}

