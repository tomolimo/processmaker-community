<?php

namespace ProcessMaker\BusinessModel;

use AppCacheView;
use AppCacheViewPeer;
use AppDelay;
use AppDelayPeer;
use AppDelegation;
use AppDelegationPeer;
use AppDocument;
use AppDocumentPeer;
use AppHistoryPeer;
use Application;
use ApplicationPeer;
use Applications;
use AppNotes;
use AppNotesPeer;
use AppSolr;
use AppTimeoutActionExecuted;
use BasePeer;
use Bootstrap;
use BpmnEngineServicesSearchIndex;
use Cases as ClassesCases;
use CasesPeer;
use Configurations;
use CreoleTypes;
use Criteria;
use DateTime;
use DBAdapter;
use EntitySolrRequestData;
use Exception;
use G;
use Groups;
use GroupUserPeer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InputDocument;
use InvalidIndexSearchTextException;
use Luracast\Restler\RestException;
use PmDynaform;
use PmTable;
use ProcessMaker\BusinessModel\Cases as BmCases;
use ProcessMaker\BusinessModel\ProcessSupervisor as BmProcessSupervisor;
use ProcessMaker\BusinessModel\Task as BmTask;
use ProcessMaker\BusinessModel\User as BmUser;
use ProcessMaker\Core\System;
use ProcessMaker\Exception\UploadException;
use ProcessMaker\Exception\CaseNoteUploadFile;
use ProcessMaker\Model\AppDelay as Delay;
use ProcessMaker\Model\Application as ModelApplication;
use ProcessMaker\Model\AppNotes as Notes;
use ProcessMaker\Model\AppTimeoutAction;
use ProcessMaker\Model\Delegation;
use ProcessMaker\Model\Documents;
use ProcessMaker\Model\Groupwf;
use ProcessMaker\Model\GroupUser;
use ProcessMaker\Model\ListUnassigned;
use ProcessMaker\Model\Triggers;
use ProcessMaker\Model\ProcessUser;
use ProcessMaker\Model\StepSupervisor;
use ProcessMaker\Model\Task;
use ProcessMaker\Model\User;
use ProcessMaker\Plugins\PluginRegistry;
use ProcessMaker\Services\Api;
use ProcessMaker\Services\OAuth2\Server;
use ProcessMaker\Util\DateTime as UtilDateTime;
use ProcessMaker\Validation\ExceptionRestApi;
use ProcessMaker\Validation\ValidationUploadedFiles;
use ProcessMaker\Validation\Validator as FileValidator;
use ProcessPeer;
use ProcessUserPeer;
use RBAC;
use ResultSet;
use SubApplication;
use Task as ModelTask;
use TaskPeer;
use Tasks as ClassesTasks;
use TaskUserPeer;
use uploadDocumentData;
use Users as ModelUsers;
use UsersPeer;
use WsBase;

class Cases
{
    private $formatFieldNameInUppercase = true;
    private $messageResponse = [];
    private $solr = null;
    private $solrEnv = null;
    const MB_IN_KB = 1024;
    const UNIT_MB = 'MB';

    /**
     * Set the format of the fields name (uppercase, lowercase)
     *
     * @param bool $flag Value that set the format
     *
     * @return void
     * @throws Exception
     */
    public function setFormatFieldNameInUppercase($flag)
    {
        try {
            $this->formatFieldNameInUppercase = $flag;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get the name of the field according to the format
     *
     * @param string $fieldName Field name
     *
     * @return string, the field name according the format
     * @throws Exception
     */
    public function getFieldNameByFormatFieldName($fieldName)
    {
        try {
            return ($this->formatFieldNameInUppercase) ? strtoupper($fieldName) : strtolower($fieldName);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Throw the exception "The Case doesn't exist"
     *
     * @param string $applicationUid Unique id of Case
     * @param string $fieldNameForException Field name for the exception
     *
     * @return void
     * @throws Exception
     */
    private function throwExceptionCaseDoesNotExist($applicationUid, $fieldNameForException)
    {
        throw new Exception(G::LoadTranslation(
            'ID_CASE_DOES_NOT_EXIST2', [$fieldNameForException, $applicationUid]
        ));
    }

    /**
     * Verify if does not exist the Case in table APPLICATION
     *
     * @param string $applicationUid Unique id of Case
     * @param string $delIndex Delegation index
     * @param string $fieldNameForException Field name for the exception
     *
     * @return void
     * @throws Exception, Throw exception if does not exist the Case in table APPLICATION
     */
    public function throwExceptionIfNotExistsCase($applicationUid, $delIndex, $fieldNameForException)
    {
        try {
            $obj = ApplicationPeer::retrieveByPK($applicationUid);

            $flag = is_null($obj);

            if (!$flag && $delIndex > 0) {
                $obj = AppDelegationPeer::retrieveByPK($applicationUid, $delIndex);

                $flag = is_null($obj);
            }

            if ($flag) {
                $this->throwExceptionCaseDoesNotExist($applicationUid, $fieldNameForException);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Application record
     *
     * @param string $applicationUid Unique id of Case
     * @param array $arrayVariableNameForException Variable name for exception
     * @param bool $throwException Flag to throw the exception if the main parameters are invalid or do not exist
     *                               (TRUE: throw the exception; FALSE: returns FALSE)
     *
     * @return array, an array with Application record
     * @throws Exception, ThrowTheException/FALSE otherwise
     */
    public function getApplicationRecordByPk(
        $applicationUid,
        array $arrayVariableNameForException,
        $throwException = true
    ) {
        try {
            $obj = ApplicationPeer::retrieveByPK($applicationUid);

            if (is_null($obj)) {
                if ($throwException) {
                    $this->throwExceptionCaseDoesNotExist(
                        $applicationUid, $arrayVariableNameForException['$applicationUid']
                    );
                } else {
                    return false;
                }
            }

            //Return
            return $obj->toArray(BasePeer::TYPE_FIELDNAME);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get AppDelegation record
     *
     * @param string $applicationUid Unique id of Case
     * @param int $delIndex Delegation index
     * @param array $arrayVariableNameForException Variable name for exception
     * @param bool $throwException Flag to throw the exception if the main parameters are invalid or do not exist
     *                               (TRUE: throw the exception; FALSE: returns FALSE)
     *
     * @return array, an array with AppDelegation record
     * @throws Exception, ThrowTheException/FALSE otherwise
     */
    public function getAppDelegationRecordByPk(
        $applicationUid,
        $delIndex,
        array $arrayVariableNameForException,
        $throwException = true
    ) {
        try {
            $obj = AppDelegationPeer::retrieveByPK($applicationUid, $delIndex);

            if (is_null($obj)) {
                if ($throwException) {
                    throw new Exception(G::LoadTranslation(
                        'ID_CASE_DEL_INDEX_DOES_NOT_EXIST',
                        [
                            $arrayVariableNameForException['$applicationUid'],
                            $applicationUid,
                            $arrayVariableNameForException['$delIndex'],
                            $delIndex
                        ]
                    ));
                } else {
                    return false;
                }
            }

            //Return
            return $obj->toArray(BasePeer::TYPE_FIELDNAME);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get list counters
     *
     * @param string $userUid Unique id of User
     * @param array $arrayType Type lists
     *
     * @return array, the list counters
     * @throws Exception
     */
    public function getListCounters($userUid, array $arrayType)
    {
        try {
            $appCacheView = new AppCacheView();

            if ($this->isSolrEnabled()) {
                $arrayListCounter = array_merge(
                    $this->solr->getCasesCount($userUid),
                    $appCacheView->getAllCounters(['completed', 'cancelled'], $userUid)
                );
            } else {
                $arrayListCounter = $appCacheView->getAllCounters($arrayType, $userUid);
            }

            //Return
            return $arrayListCounter;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get list of cases from: todo, draft, unassigned
     * Get list of cases for the following REST endpoints:
     * /light/todo
     * /light/draft
     * /light/participated
     * /light/paused
     * /light/unassigned
     *
     * @access public
     * @param array $dataList , Data for list
     * @return array $response
     */
    public function getList($dataList = array())
    {
        Validator::isArray($dataList, '$dataList');
        if (!isset($dataList["userId"])) {
            $dataList["userId"] = null;
        }

        //We need to use the USR_UID for the cases in the list
        $userUid = isset($dataList["userUid"]) ? $dataList["userUid"] : $dataList["userId"];
        $callback = isset($dataList["callback"]) ? $dataList["callback"] : "stcCallback1001";
        $dir = isset($dataList["dir"]) ? $dataList["dir"] : "DESC";
        $sort = isset($dataList["sort"]) ? $dataList["sort"] : "APPLICATION.APP_NUMBER";
        if ($sort === 'APP_CACHE_VIEW.APP_NUMBER') {
            $sort = "APPLICATION.APP_NUMBER";
        }
        $start = isset($dataList["start"]) ? $dataList["start"] : "0";
        $limit = isset($dataList["limit"]) ? $dataList["limit"] : "";
        $filter = isset($dataList["filter"]) ? $dataList["filter"] : "";
        $process = isset($dataList["process"]) ? $dataList["process"] : "";
        $category = isset($dataList["category"]) ? $dataList["category"] : "";
        $status = isset($dataList["status"]) ? strtoupper($dataList["status"]) : "";
        $search = isset($dataList["search"]) ? $dataList["search"] : "";
        $action = isset($dataList["action"]) ? $dataList["action"] : "todo";
        $paged = isset($dataList["paged"]) ? $dataList["paged"] : true;
        $type = "extjs";
        $dateFrom = (!empty($dataList["dateFrom"])) ? substr($dataList["dateFrom"], 0, 10) : "";
        $dateTo = (!empty($dataList["dateTo"])) ? substr($dataList["dateTo"], 0, 10) : "";
        $newerThan = (!empty($dataList['newerThan'])) ? $dataList['newerThan'] : '';
        $oldestThan = (!empty($dataList['oldestthan'])) ? $dataList['oldestthan'] : '';

        $apps = new Applications();
        $response = $apps->getAll(
            $userUid,
            $start,
            $limit,
            $action,
            $filter,
            $search,
            $process,
            $status,
            $type,
            $dateFrom,
            $dateTo,
            $callback,
            $dir,
            (strpos($sort, ".") !== false) ? $sort : "APP_CACHE_VIEW." . $sort,
            $category,
            true,
            $paged,
            $newerThan,
            $oldestThan
        );
        if (!empty($response['data'])) {
            foreach ($response['data'] as &$value) {
                $value = array_change_key_case($value, CASE_LOWER);
            }
        }

        if ($paged) {
            $response['total'] = $response['totalCount'];
            $response['start'] = $start + 1;
            $response['limit'] = $limit;
            $response['sort'] = G::toLower($sort);
            $response['dir'] = G::toLower($dir);
            $response['cat_uid'] = $category;
            $response['pro_uid'] = $process;
            $response['search'] = $search;
        } else {
            $response = $response['data'];
        }

        return $response;
    }

    /**
     * Search cases and get list of cases
     *
     * @access public
     *
     * @param array $dataList, Data for list
     *
     * @return array
     */
    public function getCasesSearch($dataList = array())
    {
        Validator::isArray($dataList, '$dataList');
        if (!isset($dataList["userId"])) {
            $dataList["userId"] = null;
        }

        //We need to user the USR_ID for performance
        $userId = $dataList["userId"];
        $dir = isset($dataList["dir"]) ? $dataList["dir"] : "DESC";
        $sort = isset($dataList["sort"]) ? $dataList["sort"] : "APPLICATION.APP_NUMBER";
        if ($sort === 'APP_CACHE_VIEW.APP_NUMBER') {
            $sort = "APPLICATION.APP_NUMBER";
        }
        $start = !empty($dataList["start"]) ? $dataList["start"] : 0;
        $limit = !empty($dataList["limit"]) ? $dataList["limit"] : 15;
        $process = isset($dataList["process"]) ? $dataList["process"] : "";
        $category = isset($dataList["category"]) ? $dataList["category"] : "";
        $status = isset($dataList["status"]) ? strtoupper($dataList["status"]) : "";
        $user = isset($dataList["user"]) ? $dataList["user"] : "";
        $search = isset($dataList["search"]) ? $dataList["search"] : "";
        $dateFrom = (!empty($dataList["dateFrom"])) ? substr($dataList["dateFrom"], 0, 10) : "";
        $dateTo = (!empty($dataList["dateTo"])) ? substr($dataList["dateTo"], 0, 10) : "";
        $filterStatus = isset($dataList["filterStatus"]) ? strtoupper($dataList["filterStatus"]) : "";

        $apps = new Applications();
        $response = $apps->searchAll(
            $userId,
            $start,
            $limit,
            $search,
            $process,
            $filterStatus,
            $dir,
            $sort,
            $category,
            $dateFrom,
            $dateTo
        );

        $response['total'] = 0;
        $response['start'] = $start + 1;
        $response['limit'] = $limit;
        $response['sort'] = G::toLower($sort);
        $response['dir'] = G::toLower($dir);
        $response['cat_uid'] = $category;
        $response['pro_uid'] = $process;
        $response['search'] = $search;
        $response['app_status'] = G::toLower($status);
        $response['usr_uid'] = $user;
        $response['date_from'] = $dateFrom;
        $response['date_to'] = $dateTo;

        return $response;
    }

    /**
     * Verify if Solr is Enabled
     *
     * @return bool
     */
    private function isSolrEnabled()
    {
        $solrEnabled = false;
        $this->solrEnv = !empty($this->solrEnv) ? $this->solrEnv : System::solrEnv();
        if ($this->solrEnv !== false) {
            $this->solr = !empty($this->solr) ? $this->solr : new AppSolr(
                $this->solrEnv['solr_enabled'],
                $this->solrEnv['solr_host'],
                $this->solrEnv['solr_instance']
            );
            if ($this->solr->isSolrEnabled() && $this->solrEnv["solr_enabled"] == true) {
                $solrEnabled = true;
            }
        }
        return $solrEnabled;
    }

    /**
     * Get data of a Case
     *
     * @param string $applicationUid Unique id of Case
     * @param string $userUid Unique id of User
     *
     * @return object
     * @throws Exception
     */
    public function getCaseInfo($applicationUid, $userUid)
    {
        try {
            if ($this->isSolrEnabled()) {
                try {
                    //Check if there are missing records to reindex and reindex them
                    $this->solr->synchronizePendingApplications();

                    $arrayData = array();
                    $delegationIndexes = array();
                    $columsToInclude = array("APP_UID");
                    $solrSearchText = null;
                    //Todo
                    $solrSearchText = $solrSearchText . (($solrSearchText != null) ? " OR " : null) . "(APP_STATUS:TO_DO AND APP_ASSIGNED_USERS:" . $userUid . ")";
                    $delegationIndexes[] = "APP_ASSIGNED_USER_DEL_INDEX_" . $userUid . "_txt";
                    //Draft
                    $solrSearchText = $solrSearchText . (($solrSearchText != null) ? " OR " : null) . "(APP_STATUS:DRAFT AND APP_DRAFT_USER:" . $userUid . ")";
                    //Index is allways 1
                    $solrSearchText = "($solrSearchText)";
                    //Add del_index dynamic fields to list of resulting columns
                    $columsToIncludeFinal = array_merge($columsToInclude, $delegationIndexes);
                    $solrRequestData = EntitySolrRequestData::createForRequestPagination(
                        array(
                            "workspace" => $this->solrEnv["solr_instance"],
                            "startAfter" => 0,
                            "pageSize" => 1000,
                            "searchText" => $solrSearchText,
                            "numSortingCols" => 1,
                            "sortCols" => array("APP_NUMBER"),
                            "sortDir" => array(strtolower("DESC")),
                            "includeCols" => $columsToIncludeFinal,
                            "resultFormat" => "json"
                        )
                    );
                    //Use search index to return list of cases
                    $searchIndex = new BpmnEngineServicesSearchIndex($this->solr->isSolrEnabled(), $this->solrEnv["solr_host"]);
                    //Execute query
                    $solrQueryResult = $searchIndex->getDataTablePaginatedList($solrRequestData);
                    //Get the missing data from database
                    $arrayApplicationUid = array();
                    foreach ($solrQueryResult->aaData as $i => $data) {
                        $arrayApplicationUid[] = $data["APP_UID"];
                    }
                    $aaappsDBData = $this->solr->getListApplicationDelegationData($arrayApplicationUid);
                    foreach ($solrQueryResult->aaData as $i => $data) {
                        //Initialize array
                        $delIndexes = array(); //Store all the delegation indexes
                        //Complete empty values
                        $applicationUid = $data["APP_UID"]; //APP_UID
                        //Get all the indexes returned by Solr as columns
                        for ($i = count($columsToInclude); $i <= count($data) - 1; $i++) {
                            if (is_array($data[$columsToIncludeFinal[$i]])) {
                                foreach ($data[$columsToIncludeFinal[$i]] as $delIndex) {
                                    $delIndexes[] = $delIndex;
                                }
                            }
                        }
                        //Verify if the delindex is an array
                        //if is not check different types of repositories
                        //the delegation index must always be defined.
                        if (count($delIndexes) == 0) {
                            $delIndexes[] = 1; // the first default index
                        }
                        //Remove duplicated
                        $delIndexes = array_unique($delIndexes);
                        //Get records
                        foreach ($delIndexes as $delIndex) {
                            $aRow = array();
                            //Copy result values to new row from Solr server
                            $aRow["APP_UID"] = $data["APP_UID"];
                            //Get delegation data from DB
                            //Filter data from db
                            $indexes = $this->solr->aaSearchRecords($aaappsDBData, array(
                                "APP_UID" => $applicationUid,
                                "DEL_INDEX" => $delIndex
                            ));
                            foreach ($indexes as $index) {
                                $row = $aaappsDBData[$index];
                            }
                            if (!isset($row)) {
                                continue;
                            }
                            $ws = new WsBase();
                            $fields = $ws->getCaseInfo($applicationUid, $row["DEL_INDEX"]);
                            $array = json_decode(json_encode($fields), true);
                            if ($array ["status_code"] != 0) {
                                throw (new Exception($array ["message"]));
                            } else {
                                $array['app_uid'] = $array['caseId'];
                                $array['app_number'] = $array['caseNumber'];
                                $array['app_name'] = $array['caseName'];
                                $array['app_status'] = $array['caseStatus'];
                                $array['app_init_usr_uid'] = $array['caseCreatorUser'];
                                $array['app_init_usr_username'] = trim($array['caseCreatorUserName']);
                                $array['pro_uid'] = $array['processId'];
                                $array['pro_name'] = $array['processName'];
                                $array['app_create_date'] = $array['createDate'];
                                $array['app_update_date'] = $array['updateDate'];
                                $array['current_task'] = $array['currentUsers'];
                                for ($i = 0; $i <= count($array['current_task']) - 1; $i++) {
                                    $current_task = $array['current_task'][$i];
                                    $current_task['usr_uid'] = $current_task['userId'];
                                    $current_task['usr_name'] = trim($current_task['userName']);
                                    $current_task['tas_uid'] = $current_task['taskId'];
                                    $current_task['tas_title'] = $current_task['taskName'];
                                    $current_task['del_index'] = $current_task['delIndex'];
                                    $current_task['del_thread'] = $current_task['delThread'];
                                    $current_task['del_thread_status'] = $current_task['delThreadStatus'];
                                    unset($current_task['userId']);
                                    unset($current_task['userName']);
                                    unset($current_task['taskId']);
                                    unset($current_task['taskName']);
                                    unset($current_task['delIndex']);
                                    unset($current_task['delThread']);
                                    unset($current_task['delThreadStatus']);
                                    $aCurrent_task[] = $current_task;
                                }
                                unset($array['status_code']);
                                unset($array['message']);
                                unset($array['timestamp']);
                                unset($array['caseParalell']);
                                unset($array['caseId']);
                                unset($array['caseNumber']);
                                unset($array['caseName']);
                                unset($array['caseStatus']);
                                unset($array['caseCreatorUser']);
                                unset($array['caseCreatorUserName']);
                                unset($array['processId']);
                                unset($array['processName']);
                                unset($array['createDate']);
                                unset($array['updateDate']);
                                unset($array['currentUsers']);
                                $current_task = json_decode(json_encode($aCurrent_task), false);
                                $oResponse = json_decode(json_encode($array), false);
                                $oResponse->current_task = $current_task;
                            }

                            //Return
                            return $oResponse;
                        }
                    }
                } catch (InvalidIndexSearchTextException $e) {
                    $arrayData = array();
                    $arrayData[] = array(
                        "app_uid" => $e->getMessage(),
                        "app_name" => $e->getMessage(),
                        "del_index" => $e->getMessage(),
                        "pro_uid" => $e->getMessage()
                    );
                    throw (new Exception($arrayData));
                }
            } else {
                $ws = new WsBase();
                $fields = $ws->getCaseInfo($applicationUid, 0);
                $array = json_decode(json_encode($fields), true);

                if ($array ["status_code"] != 0) {
                    throw (new Exception($array ["message"]));
                } else {
                    $array['app_uid'] = $array['caseId'];
                    $array['app_number'] = $array['caseNumber'];
                    $array['app_name'] = $array['caseName'];
                    $array["app_status"] = $array["caseStatus"];
                    $array['app_init_usr_uid'] = $array['caseCreatorUser'];
                    $array['app_init_usr_username'] = trim($array['caseCreatorUserName']);
                    $array['pro_uid'] = $array['processId'];
                    $array['pro_name'] = $array['processName'];
                    $array['app_create_date'] = $array['createDate'];
                    $array['app_update_date'] = $array['updateDate'];
                    $array['current_task'] = $array['currentUsers'];

                    $aCurrent_task = array();

                    for ($i = 0; $i <= count($array['current_task']) - 1; $i++) {
                        $current_task = $array['current_task'][$i];
                        $current_task['usr_uid'] = $current_task['userId'];
                        $current_task['usr_name'] = trim($current_task['userName']);
                        $current_task['tas_uid'] = $current_task['taskId'];
                        $current_task['tas_title'] = $current_task['taskName'];
                        $current_task['del_index'] = $current_task['delIndex'];
                        $current_task['del_thread'] = $current_task['delThread'];
                        $current_task['del_thread_status'] = $current_task['delThreadStatus'];
                        $current_task["del_init_date"] = $current_task["delInitDate"] . "";
                        $current_task["del_task_due_date"] = $current_task["delTaskDueDate"];
                        unset($current_task['userId']);
                        unset($current_task['userName']);
                        unset($current_task['taskId']);
                        unset($current_task['taskName']);
                        unset($current_task['delIndex']);
                        unset($current_task['delThread']);
                        unset($current_task['delThreadStatus']);
                        $aCurrent_task[] = $current_task;
                    }
                    unset($array['status_code']);
                    unset($array['message']);
                    unset($array['timestamp']);
                    unset($array['caseParalell']);
                    unset($array['caseId']);
                    unset($array['caseNumber']);
                    unset($array['caseName']);
                    unset($array['caseStatus']);
                    unset($array['caseCreatorUser']);
                    unset($array['caseCreatorUserName']);
                    unset($array['processId']);
                    unset($array['processName']);
                    unset($array['createDate']);
                    unset($array['updateDate']);
                    unset($array['currentUsers']);
                }
                $current_task = json_decode(json_encode($aCurrent_task), false);
                $oResponse = json_decode(json_encode($array), false);
                $oResponse->current_task = $current_task;

                //Return
                return $oResponse;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data of a sub-process case
     *
     * @param string $applicationUid Unique Case Id
     * @param string $userUid Unique User Id
     * 
     * @return array Return an array with information of Cases
     * @throws Exception
     */
    public function getCaseInfoSubProcess($applicationUid, $userUid)
    {

        try {
            $response = [];
            $subApplication = new SubApplication();
            $data = $subApplication->loadByAppUidParent($applicationUid);
            if (!empty($data)) {
                foreach ($data as $item) {
                    $response[] = $this->getCaseInfo($item['APP_UID'], $userUid);
                }
            } else {
                throw new Exception(G::LoadTranslation("ID_CASE_DOES_NOT_EXIST", [$applicationUid]));
            }

            return $response;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get data Task Case
     *
     * @param string $applicationUid Unique id of Case
     * @param string $userUid Unique id of User
     *
     * @return array, an array with Task Case
     * @throws Exception
     */
    public function getTaskCase($applicationUid, $userUid)
    {
        try {
            //Verify data
            $this->throwExceptionIfNotExistsCase($applicationUid, 0, $this->getFieldNameByFormatFieldName("APP_UID"));

            $criteria = new Criteria("workflow");

            $criteria->addSelectColumn(ApplicationPeer::APP_UID);

            $criteria->add(ApplicationPeer::APP_UID, $applicationUid, Criteria::EQUAL);
            $criteria->add(ApplicationPeer::APP_STATUS, "COMPLETED", Criteria::EQUAL);

            $rsCriteria = ApplicationPeer::doSelectRS($criteria);

            if ($rsCriteria->next()) {
                throw new Exception(G::LoadTranslation("ID_CASE_NO_CURRENT_TASKS_BECAUSE_CASE_ITS_COMPLETED",
                    array($this->getFieldNameByFormatFieldName("APP_UID"), $applicationUid)));
            }

            //Get data
            $result = array();

            $oCriteria = new Criteria('workflow');
            $del = DBAdapter::getStringDelimiter();
            $oCriteria->addSelectColumn(AppDelegationPeer::DEL_INDEX);
            $oCriteria->addSelectColumn(AppDelegationPeer::TAS_UID);
            $oCriteria->addSelectColumn(AppDelegationPeer::DEL_INIT_DATE);
            $oCriteria->addSelectColumn(AppDelegationPeer::DEL_TASK_DUE_DATE);
            $oCriteria->addSelectColumn(TaskPeer::TAS_TITLE);
            $oCriteria->addJoin(AppDelegationPeer::TAS_UID, TaskPeer::TAS_UID);
            $oCriteria->add(AppDelegationPeer::APP_UID, $applicationUid);
            $oCriteria->add(AppDelegationPeer::USR_UID, $userUid);
            $oCriteria->add(AppDelegationPeer::DEL_THREAD_STATUS, 'OPEN');
            $oCriteria->add(AppDelegationPeer::DEL_FINISH_DATE, null, Criteria::ISNULL);
            $oDataset = AppDelegationPeer::doSelectRS($oCriteria);
            $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
            $oDataset->next();
            while ($aRow = $oDataset->getRow()) {
                $result = array(
                    'tas_uid' => $aRow['TAS_UID'],
                    'tas_title' => $aRow['TAS_TITLE'],
                    'del_index' => $aRow['DEL_INDEX'],
                    "del_init_date" => $aRow["DEL_INIT_DATE"] . "",
                    "del_task_due_date" => $aRow["DEL_TASK_DUE_DATE"]
                );
                $oDataset->next();
            }
            //Return
            if (empty($result)) {
                throw new Exception(G::LoadTranslation("ID_CASES_INCORRECT_INFORMATION", array($applicationUid)));
            } else {
                return $result;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Add New Case
     *
     * @param string $processUid Unique id of Project
     * @param string $taskUid Unique id of Activity (task)
     * @param string $userUid Unique id of Case
     * @param array $variables
     *
     * @return object
     * @throws Exception
     */
    public function addCase($processUid, $taskUid, $userUid, $variables)
    {
        try {

            $ws = new WsBase();
            if ($variables) {
                $variables = array_shift($variables);
            }
            Validator::proUid($processUid, '$pro_uid');
            $oTask = new ModelTask();
            if (!$oTask->taskExists($taskUid)) {
                throw new Exception(G::LoadTranslation("ID_INVALID_VALUE_FOR", array('tas_uid')));
            }
            $fields = $ws->newCase($processUid, $userUid, $taskUid, $variables);
            $array = json_decode(json_encode($fields), true);
            if ($array ["status_code"] != 0) {
                throw (new Exception($array ["message"]));
            } else {
                $array['app_uid'] = $array['caseId'];
                $array['app_number'] = $array['caseNumber'];
                unset($array['status_code']);
                unset($array['message']);
                unset($array['timestamp']);
                unset($array['caseId']);
                unset($array['caseNumber']);
            }
            $oResponse = json_decode(json_encode($array), false);

            //Return
            return $oResponse;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Add New Case Impersonate
     *
     * @param string $processUid Unique id of Project
     * @param string $userUid Unique id of User
     * @param string $taskUid Unique id of Case
     * @param array $variables
     *
     * @return object
     * @throws Exception
     */
    public function addCaseImpersonate($processUid, $userUid, $taskUid, $variables)
    {
        try {

            $ws = new WsBase();
            if ($variables) {
                $variables = array_shift($variables);
            } elseif ($variables == null) {
                $variables = array(array());
            }
            Validator::proUid($processUid, '$pro_uid');
            $user = new ModelUsers();
            if (!$user->userExists($userUid)) {
                throw new Exception(G::LoadTranslation("ID_INVALID_VALUE_FOR", array('usr_uid')));
            }
            $fields = $ws->newCaseImpersonate($processUid, $userUid, $variables, $taskUid);
            $array = json_decode(json_encode($fields), true);
            if ($array ["status_code"] != 0) {
                if ($array ["status_code"] == 12) {
                    throw (new Exception(G::loadTranslation('ID_NO_STARTING_TASK') . '. tas_uid.'));
                } elseif ($array ["status_code"] == 13) {
                    throw (new Exception(G::loadTranslation('ID_MULTIPLE_STARTING_TASKS') . '. tas_uid.'));
                }
                throw (new Exception($array ["message"]));
            } else {
                $array['app_uid'] = $array['caseId'];
                $array['app_number'] = $array['caseNumber'];
                unset($array['status_code']);
                unset($array['message']);
                unset($array['timestamp']);
                unset($array['caseId']);
                unset($array['caseNumber']);
            }
            $oResponse = json_decode(json_encode($array), false);

            //Return
            return $oResponse;
        } catch (Exception $e) {
            throw $e;
        }
    }
    /**
     * This function check if some user has participation over the case
     *
     * @param string $usrUid
     * @param int $caseNumber
     * @param int $index
     *
     * @return bool
    */
    public function participation($usrUid, $caseNumber, $index)
    {
        $userId = User::getId($usrUid);
        $query = Delegation::query()->select(['APP_NUMBER'])->case($caseNumber)->index($index)->openAndPause();
        $query1 = clone $query;
        $result = $query->userId($userId)->limit(1)->get()->values()->toArray();
        $permission = empty($result) ? false : true;
        // Review if the user is supervisor
        if (empty($result)) {
            $processes = ProcessUser::getProcessesOfSupervisor($usrUid);
            $query1->processInList($processes);
            $result = $query1->get()->values()->toArray();
            $permission = empty($result) ? false : true;
        }

        return $permission;
    }

    /**
     * Review if the user is supervisor
     *
     * @param string $usrUid
     * @param int $caseNumber
     *
     * @return bool
    */
    public function isSupervisor(string $usrUid, int $caseNumber)
    {
        $result = [];
        $user = new BmUser();
        if ($user->checkPermission($usrUid, 'PM_SUPERVISOR')) {
            $processes = ProcessUser::getProcessesOfSupervisor($usrUid);
            $query = Delegation::query()->select(['APP_NUMBER'])->case($caseNumber)->processInList($processes);
            $result = $query->get()->values()->toArray();
        }
        return !empty($result);
    }

    /**
     * Reassign Case
     *
     * @param string $appUid Unique id of Case
     * @param string $usrUid Unique id of User
     * @param int $delIndex
     * @param string $userSource Unique id of User Source
     * @param string $userTarget id of User Target
     * @param string $reason
     * @param boolean $sendMail
     *
     * @return void
     * @throws Exception
     */
    public function updateReassignCase($appUid, $usrUid, $delIndex, $userSource, $userTarget, $reason = '', $sendMail = false)
    {
        try {
            if (!$delIndex) {
                $delIndex = AppDelegation::getCurrentIndex($appUid);
            }

            /** Reassign case */
            $ws = new WsBase();
            $result = $ws->reassignCase($usrUid, $appUid, $delIndex, $userSource, $userTarget);
            $result = (object)$result;
            if (isset($result->status_code)) {
                if ($result->status_code !== 0) {
                    throw new Exception($result->message);
                }
            } else {
                throw new Exception(G::LoadTranslation("ID_CASES_INCORRECT_INFORMATION", [$appUid]));
            }

            /** Add the note */
            if (!empty($reason)) {
                $this->sendMail($appUid, $usrUid, $reason, $sendMail, $userTarget);
            }

            // Log
            $message = 'Reassign case';
            $context = $data = [
                "appUid" => $appUid,
                "usrUidSupervisor" => $usrUid,
                "userSource" => $userSource,
                "userTarget" => $userTarget,
                "reason" => $reason,
                "delIndex" => $delIndex
            ];
            Log::channel(':ReassignCase')->info($message, Bootstrap::context($context));
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Put cancel case
     *
     * @access public
     * @param string $appUid, Uid for case
     * @param string $usrUid, Uid for user
     * @param interger $delIndex
     *
     * @return void
     * @throws Exception
     */
    public function putCancelCase($appUid, $usrUid, $delIndex = null, $reason = '', $sendMail = false)
    {
        Validator::isString($appUid, '$app_uid');
        Validator::appUid($appUid, '$app_uid');
        Validator::isString($usrUid, '$usr_uid');
        Validator::usrUid($usrUid, '$usr_uid');

        $case = new ClassesCases();
        $fields = $case->loadCase($appUid);
        $supervisor = new BmProcessSupervisor();
        $isSupervisor = $supervisor->isUserProcessSupervisor($fields['PRO_UID'], $usrUid);

        if (is_null($delIndex)) {
            $u = new ModelUsers();
            $usrId = $u->load($usrUid)['USR_ID'];

            if ($isSupervisor) {
                //Get the last index open
                $delIndex = AppDelegation::getLastIndexByStatus($fields['APP_NUMBER']);
            } else {
                //Get the last index open related to the user
                $delIndex = AppDelegation::getLastIndexByUserAndStatus($fields['APP_NUMBER'], $usrId);
            }

            //We will to validate when the case is TO_DO and the user does not have a index OPEN
            //The scenarios with COMPLETED, CANCELLED and DRAFT is considered in the WsBase::cancelCase
            if ($fields['APP_STATUS'] === 'TO_DO' && $delIndex === 0) {
                $invalidText = $_SERVER['HTTP_AUTHORIZATION'] ?? $usrUid;
                throw (new Exception(G::LoadTranslation("ID_CASE_USER_INVALID_CANCEL_CASE", [$invalidText])));
            }
        }
        Validator::isInteger($delIndex, '$del_index');

        /** Cancel case */
        $ws = new WsBase();
        $result = $ws->cancelCase($appUid, $delIndex, $usrUid);
        $result = (object)$result;
        if ($result->status_code !== 0) {
            throw new Exception($result->message);
        }
        /** Add the note */
        if (!empty($reason)) {
            $noteContent = $reason;
            // Define the Case for register a case note
            $cases = new BmCases();
            $response = $cases->addNote($appUid, $usrUid, $noteContent, $sendMail);
        }
    }

    /**
     * Put pause case
     *
     * @access public
     * @param string $appUid , Uid for case
     * @param string $usrUid , Uid for user
     * @param bool|string $index
     * @param null|string $date , Date for unpaused
     * @param string $time , Time for unpaused
     * @param string $reason
     * @param bool $sendMail
     *
     * @return void
     * @throws Exception
     */
    public function putPauseCase($appUid, $usrUid, $index = 0, $date = null, $time = '00:00', $reason = '', $sendMail = false)
    {
        Validator::isString($appUid, '$app_uid');
        Validator::isString($usrUid, '$usr_uid');
        Validator::appUid($appUid, '$app_uid');
        Validator::usrUid($usrUid, '$usr_uid');
        Validator::isInteger($index, '$del_index');
        // Get the last index
        if ($index === 0) {
            $index = AppDelegation::getCurrentIndex($appUid);
        }
        // Get the case status
        $case = new ClassesCases();
        $fields = $case->loadCase($appUid);
        $caseNumber = $fields['APP_NUMBER'];
        if ($fields['APP_STATUS'] == 'CANCELLED') {
            throw new Exception(G::LoadTranslation("ID_CASE_IS_CANCELED", [$appUid]));
        }
        // Check if the case was not paused
        $delay = new AppDelay();
        if ($delay->isPaused($appUid, $index)) {
            throw new Exception(G::LoadTranslation("ID_CASE_PAUSED", [$appUid]));
        }
        // Review if the user has participation or is supervisor
        $permission = $this->participation($usrUid, $caseNumber, $index);
        if (!$permission) {
            $invalidText = $_SERVER['HTTP_AUTHORIZATION'] ?? $usrUid;
            throw new Exception(G::LoadTranslation("ID_CASE_USER_INVALID_PAUSED_CASE", [$invalidText]));
        }

        if ($date != null) {
            Validator::isDate($date, 'Y-m-d', '$unpaused_date');
        }

        // Check if the case is unassigned
        $classCases = new ClassesCases();
        if ($classCases->isUnassignedPauseCase($appUid, $index)) {
            throw new Exception(G::LoadTranslation("ID_CASE_NOT_PAUSED", [G::LoadTranslation("ID_UNASSIGNED_STATUS")]));
        }

        /** Pause case */
        $case->pauseCase($appUid, $index, $usrUid, $date . ' ' . $time);

        /** Add the note */
        if (!empty($reason)) {
            $noteContent = $reason;
            // Define the Case for register a case note
            $cases = new BmCases();
            $response = $cases->addNote($appUid, $usrUid, $noteContent, $sendMail);
        }
    }

    /**
     * Put unpause case
     *
     * @access public
     * @param string $app_uid , Uid for case
     * @param string $usr_uid , Uid for user
     * @param int $del_index
     *
     * @return void
     * @throws Exception
     */
    public function putUnpauseCase($appUid, $usrUid, $index = 0)
    {
        Validator::isString($appUid, '$app_uid');
        Validator::isString($usrUid, '$usr_uid');
        Validator::appUid($appUid, '$app_uid');
        Validator::usrUid($usrUid, '$usr_uid');

        if ($index === 0) {
            $index = AppDelegation::getCurrentIndex($appUid);
        }
        Validator::isInteger($index, '$del_index');

        $delay = new AppDelay();
        if (!$delay->isPaused($appUid, $index)) {
            throw new Exception(G::LoadTranslation("ID_CASE_NOT_PAUSED", [$appUid]));
        }

        // Review if the user has participation or is supervisor
        $caseNumber = ModelApplication::getCaseNumber($appUid);
        $permission = $this->participation($usrUid, $caseNumber, $index);
        if (!$permission) {
            $invalidText = $_SERVER['HTTP_AUTHORIZATION'] ?? $usrUid;
            throw new Exception(G::LoadTranslation("ID_CASE_USER_INVALID_UNPAUSE_CASE", [$invalidText]));
        }

        /** Unpause case */
        $case = new ClassesCases();
        $case->unpauseCase($appUid, $index, $usrUid);
    }

    /**
     * Put claim case
     *
     * @param string $appUid
     * @param integer $index
     * @param string $userUid
     * @param string $action
     * @param string $reason
     *
     * @return void
     * @throws Exception
     *
     * @access public
     */
    public function putClaimCase($appUid, $index, $userUid, $action, $reason = '')
    {
        // Validate the parameters
        Validator::isString($appUid, '$appUid');
        Validator::isString($userUid, '$userUid');
        Validator::isInteger($index, '$index');
        Validator::appUid($appUid, '$appUid');
        Validator::usrUid($userUid, '$userUid');

        // Review if the user can claim the case
        $appDelegation = new AppDelegation();
        $delegation = $appDelegation->load($appUid, $index);
        if (empty($delegation['USR_UID'])) {
            $classesCase = new ClassesCases();
            $case = $classesCase->loadCase($appUid);

            //Review if the user can be claim the case
            if (!$classesCase->isSelfService($userUid, $delegation['TAS_UID'], $appUid)) {
                if (!$this->isSupervisor($userUid, $case['APP_NUMBER'])){
                    $message = preg_replace("#<br\s*/?>#i", "", G::LoadTranslation("ID_NO_PERMISSION_NO_PARTICIPATED"));
                    throw new Exception($message);
                }
            }
            $classesCase->setCatchUser($appUid, $index, $userUid);
        } else {
            $invalidText = $_SERVER['HTTP_AUTHORIZATION'] ?? $userUid;
            throw new Exception(G::LoadTranslation("ID_CASE_USER_INVALID_CLAIM_CASE", [$invalidText]));
        }

        $usrUidSupervisor = (Server::getUserId() === $userUid) ? '' : Server::getUserId();

        // Log
        $message = $action . ' case';
        $context = $data = [
            "appUid" => $appUid,
            "usrUidSupervisor" => $usrUidSupervisor,
            "userTarget" => $userUid,
            "reason" => $reason,
            "delIndex" => $index
        ];
        Log::channel(':' . $action . 'Case')->info($message, Bootstrap::context($context));
    }

    /**
     * Put execute trigger case
     *
     * @access public
     * @param string $appUid , Uid for case
     * @param string $triUid , Uid for trigger
     * @param string $userUid , Uid for user
     * @param bool|string $delIndex
     *
     * @return void
     * @throws Exception
     */
    public function putExecuteTriggerCase($appUid, $triUid, $userUid, $delIndex = false)
    {
        Validator::isString($appUid, '$appUid');
        Validator::isString($triUid, '$triUid');
        Validator::isString($userUid, '$userUid');

        Validator::appUid($appUid, '$appUid');
        Validator::triUid($triUid, '$triUid');
        Validator::usrUid($userUid, '$userUid');

        if ($delIndex === false) {
            //We need to find the last delIndex open related to the user $usr_uid
            $delIndex = (integer)$this->getLastParticipatedByUser($appUid, $userUid, 'OPEN');
            //If the is assigned another user the function will be return 0
            if ($delIndex === 0) {
                throw new Exception(G::loadTranslation('ID_CASE_ASSIGNED_ANOTHER_USER'));
            }
        }
        Validator::isInteger($delIndex, '$del_index');

        global $RBAC;
        if (!method_exists($RBAC, 'initRBAC')) {
            $RBAC = RBAC::getSingleton(PATH_DATA, session_id());
            $RBAC->sSystem = 'PROCESSMAKER';
        }

        $case = new WsBase();
        $result = $case->executeTrigger($userUid, $appUid, $triUid, $delIndex);

        if ($result->status_code != 0) {
            throw new Exception($result->message);
        }
    }

    /**
     * Delete case
     *
     * @access public
     * @param string $appUid, Uid for case
     * @param string $usrUid, Uid user
     *
     * @return void
     * @throws Exception
     */
    public function deleteCase($appUid, $usrUid)
    {
        Validator::isString($appUid, '$app_uid');
        Validator::appUid($appUid, '$app_uid');

        // Review the status and owner
        $caseInfo = ModelApplication::getCase($appUid);
        if (!empty($caseInfo)) {
            // Check if the requester is the owner
            if ($caseInfo['APP_INIT_USER'] !== $usrUid) {
                global $RBAC;
                // If no we need to review if have the permission
                if ($RBAC->userCanAccess('PM_DELETECASE') != 1) {
                    throw new Exception(G::LoadTranslation('ID_NOT_ABLE_DELETE_CASES'));
                }
            }

            // Review the status
            if ($caseInfo['APP_STATUS'] != 'DRAFT') {
                throw new Exception(G::LoadTranslation("ID_DELETE_CASE_NO_STATUS"));
            }

            $case = new ClassesCases();
            $case->removeCase($appUid);
        }
    }

    /**
     * Route Case
     *
     * @param string $applicationUid Unique id of Case
     * @param string $userUid Unique id of User
     * @param string $delIndex
     * @param boolean $executeTriggersBeforeAssignment
     *
     * @return void
     * @throws Exception
     */
    public function updateRouteCase($applicationUid, $userUid, $delIndex, $executeTriggersBeforeAssignment)
    {
        try {
            if (!$delIndex) {
                $delIndex = AppDelegation::getCurrentIndex($applicationUid);
                //Check if the next task is a subprocess SYNCHRONOUS with a thread Open
                $subAppData = new SubApplication();
                $caseSubprocessPending = $subAppData->isSubProcessWithCasePending($applicationUid, $delIndex);
                if ($caseSubprocessPending) {
                    throw (new Exception(G::LoadTranslation("ID_CASE_ALREADY_DERIVATED")));
                }
            }

            $ws = new WsBase();
            $fields = $ws->derivateCase($userUid, $applicationUid, $delIndex, $executeTriggersBeforeAssignment);
            $array = json_decode(json_encode($fields), true);
            if ($array ["status_code"] != 0) {
                throw (new Exception($array ["message"]));
            } else {
                unset($array['status_code']);
                unset($array['message']);
                unset($array['timestamp']);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * get all upload document that they have send it
     *
     * @param string $sProcessUID Unique id of Process
     * @param string $sApplicationUID Unique id of Case
     * @param string $sTasKUID Unique id of Activity
     * @param string $sUserUID Unique id of User
     *
     * @return object
     * @throws Exception
     */
    public function getAllUploadedDocumentsCriteria($sProcessUID, $sApplicationUID, $sTasKUID, $sUserUID)
    {

        $conf = new Configurations();
        $confEnvSetting = $conf->getFormats();

        $cases = new ClassesCases();

        $listing = false;
        $oPluginRegistry = PluginRegistry::loadSingleton();
        if ($oPluginRegistry->existsTrigger(PM_CASE_DOCUMENT_LIST)) {
            $folderData = new \folderData(null, null, $sApplicationUID, null, $sUserUID);
            $folderData->PMType = "INPUT";
            $folderData->returnList = true;
            $listing = $oPluginRegistry->executeTriggers(PM_CASE_DOCUMENT_LIST, $folderData);
        }
        $aObjectPermissions = $cases->getAllObjects($sProcessUID, $sApplicationUID, $sTasKUID, $sUserUID);
        if (!is_array($aObjectPermissions)) {
            $aObjectPermissions = array(
                'DYNAFORMS' => array(-1),
                'INPUT_DOCUMENTS' => array(-1),
                'OUTPUT_DOCUMENTS' => array(-1)
            );
        }
        if (!isset($aObjectPermissions['DYNAFORMS'])) {
            $aObjectPermissions['DYNAFORMS'] = array(-1);
        } else {
            if (!is_array($aObjectPermissions['DYNAFORMS'])) {
                $aObjectPermissions['DYNAFORMS'] = array(-1);
            }
        }
        if (!isset($aObjectPermissions['INPUT_DOCUMENTS'])) {
            $aObjectPermissions['INPUT_DOCUMENTS'] = array(-1);
        } else {
            if (!is_array($aObjectPermissions['INPUT_DOCUMENTS'])) {
                $aObjectPermissions['INPUT_DOCUMENTS'] = array(-1);
            }
        }
        if (!isset($aObjectPermissions['OUTPUT_DOCUMENTS'])) {
            $aObjectPermissions['OUTPUT_DOCUMENTS'] = array(-1);
        } else {
            if (!is_array($aObjectPermissions['OUTPUT_DOCUMENTS'])) {
                $aObjectPermissions['OUTPUT_DOCUMENTS'] = array(-1);
            }
        }
        $aDelete = $cases->getAllObjectsFrom($sProcessUID, $sApplicationUID, $sTasKUID, $sUserUID, 'DELETE');
        $oAppDocument = new AppDocument();
        $oCriteria = new Criteria('workflow');
        $oCriteria->add(AppDocumentPeer::APP_UID, $sApplicationUID);
        $oCriteria->add(AppDocumentPeer::APP_DOC_TYPE, array('INPUT'), Criteria::IN);
        $oCriteria->add(AppDocumentPeer::APP_DOC_STATUS, array('ACTIVE'), Criteria::IN);
        //$oCriteria->add(AppDocumentPeer::APP_DOC_UID, $aObjectPermissions['INPUT_DOCUMENTS'], Criteria::IN);
        $oCriteria->add(
            $oCriteria->getNewCriterion(
                AppDocumentPeer::APP_DOC_UID, $aObjectPermissions['INPUT_DOCUMENTS'], Criteria::IN)->
            addOr($oCriteria->getNewCriterion(AppDocumentPeer::USR_UID, array($sUserUID, '-1'), Criteria::IN))
        );
        $aConditions = array();
        $aConditions[] = array(AppDocumentPeer::APP_UID, AppDelegationPeer::APP_UID);
        $aConditions[] = array(AppDocumentPeer::DEL_INDEX, AppDelegationPeer::DEL_INDEX);
        $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
        $oCriteria->add(AppDelegationPeer::PRO_UID, $sProcessUID);
        $oCriteria->addAscendingOrderByColumn(AppDocumentPeer::APP_DOC_INDEX);
        $oDataset = AppDocumentPeer::doSelectRS($oCriteria);
        $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $oDataset->next();
        $aInputDocuments = array();
        $aInputDocuments[] = array(
            'APP_DOC_UID' => 'char',
            'DOC_UID' => 'char',
            'APP_DOC_COMMENT' => 'char',
            'APP_DOC_FILENAME' => 'char',
            'APP_DOC_INDEX' => 'integer'
        );
        $oUser = new ModelUsers();
        while ($aRow = $oDataset->getRow()) {
            $oCriteria2 = new Criteria('workflow');
            $oCriteria2->add(AppDelegationPeer::APP_UID, $sApplicationUID);
            $oCriteria2->add(AppDelegationPeer::DEL_INDEX, $aRow['DEL_INDEX']);
            $oDataset2 = AppDelegationPeer::doSelectRS($oCriteria2);
            $oDataset2->setFetchmode(ResultSet::FETCHMODE_ASSOC);
            $oDataset2->next();
            $aRow2 = $oDataset2->getRow();
            $oTask = new ModelTask();
            if ($oTask->taskExists($aRow2['TAS_UID'])) {
                $aTask = $oTask->load($aRow2['TAS_UID']);
            } else {
                $aTask = array('TAS_TITLE' => '(TASK DELETED)');
            }
            $aAux = $oAppDocument->load($aRow['APP_DOC_UID'], $aRow['DOC_VERSION']);
            $lastVersion = $oAppDocument->getLastAppDocVersion($aRow['APP_DOC_UID'], $sApplicationUID);

            if ($aAux['USR_UID'] !== "-1") {
                try {
                    $aAux1 = $oUser->load($aAux['USR_UID']);

                    $sUser = $conf->usersNameFormatBySetParameters($confEnvSetting["format"], $aAux1["USR_USERNAME"],
                        $aAux1["USR_FIRSTNAME"], $aAux1["USR_LASTNAME"]);
                } catch (Exception $oException) {
                    $sUser = '***';
                }
            } else {
                $sUser = '***';
            }
            $aFields = array(
                'APP_DOC_UID' => $aAux['APP_DOC_UID'],
                'DOC_UID' => $aAux['DOC_UID'],
                'APP_DOC_COMMENT' => $aAux['APP_DOC_COMMENT'],
                'APP_DOC_FILENAME' => $aAux['APP_DOC_FILENAME'],
                'APP_DOC_INDEX' => $aAux['APP_DOC_INDEX'],
                'TYPE' => $aAux['APP_DOC_TYPE'],
                'ORIGIN' => $aTask['TAS_TITLE'],
                'CREATE_DATE' => $aAux['APP_DOC_CREATE_DATE'],
                'CREATED_BY' => $sUser
            );
            if ($aFields['APP_DOC_FILENAME'] != '') {
                $aFields['TITLE'] = $aFields['APP_DOC_FILENAME'];
            } else {
                $aFields['TITLE'] = $aFields['APP_DOC_COMMENT'];
            }
            //$aFields['POSITION'] = $_SESSION['STEP_POSITION'];
            $aFields['CONFIRM'] = G::LoadTranslation('ID_CONFIRM_DELETE_ELEMENT');
            if (in_array($aRow['APP_DOC_UID'], $aDelete['INPUT_DOCUMENTS'])) {
                $aFields['ID_DELETE'] = G::LoadTranslation('ID_DELETE');
            }
            $aFields['DOWNLOAD_LABEL'] = G::LoadTranslation('ID_DOWNLOAD');
            $aFields['DOWNLOAD_LINK'] = "cases/cases_ShowDocument?a=" . $aRow['APP_DOC_UID'] . "&v=" . $aRow['DOC_VERSION'];
            $aFields['DOC_VERSION'] = $aRow['DOC_VERSION'];
            if (is_array($listing)) {
                foreach ($listing as $folderitem) {
                    if ($folderitem->filename == $aRow['APP_DOC_UID']) {
                        $aFields['DOWNLOAD_LABEL'] = G::LoadTranslation('ID_GET_EXTERNAL_FILE');
                        $aFields['DOWNLOAD_LINK'] = $folderitem->downloadScript;
                        continue;
                    }
                }
            }
            if ($lastVersion == $aRow['DOC_VERSION']) {
                //Show only last version
                $aInputDocuments[] = $aFields;
            }
            $oDataset->next();
        }
        $oAppDocument = new AppDocument();
        $oCriteria = new Criteria('workflow');
        $oCriteria->add(AppDocumentPeer::APP_UID, $sApplicationUID);
        $oCriteria->add(AppDocumentPeer::APP_DOC_TYPE, array('ATTACHED'), Criteria::IN);
        $oCriteria->add(AppDocumentPeer::APP_DOC_STATUS, array('ACTIVE'), Criteria::IN);
        $oCriteria->add(
            $oCriteria->getNewCriterion(
                AppDocumentPeer::APP_DOC_UID, $aObjectPermissions['INPUT_DOCUMENTS'], Criteria::IN
            )->
            addOr($oCriteria->getNewCriterion(AppDocumentPeer::USR_UID, array($sUserUID, '-1'), Criteria::IN)));
        $aConditions = array();
        $aConditions[] = array(AppDocumentPeer::APP_UID, AppDelegationPeer::APP_UID);
        $aConditions[] = array(AppDocumentPeer::DEL_INDEX, AppDelegationPeer::DEL_INDEX);
        $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
        $oCriteria->add(AppDelegationPeer::PRO_UID, $sProcessUID);
        $oCriteria->addAscendingOrderByColumn(AppDocumentPeer::APP_DOC_INDEX);
        $oDataset = AppDocumentPeer::doSelectRS($oCriteria);
        $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $oDataset->next();
        while ($aRow = $oDataset->getRow()) {
            $oCriteria2 = new Criteria('workflow');
            $oCriteria2->add(AppDelegationPeer::APP_UID, $sApplicationUID);
            $oCriteria2->add(AppDelegationPeer::DEL_INDEX, $aRow['DEL_INDEX']);
            $oDataset2 = AppDelegationPeer::doSelectRS($oCriteria2);
            $oDataset2->setFetchmode(ResultSet::FETCHMODE_ASSOC);
            $oDataset2->next();
            $aRow2 = $oDataset2->getRow();
            $oTask = new ModelTask();
            if ($oTask->taskExists($aRow2['TAS_UID'])) {
                $aTask = $oTask->load($aRow2['TAS_UID']);
            } else {
                $aTask = array('TAS_TITLE' => '(TASK DELETED)');
            }
            $aAux = $oAppDocument->load($aRow['APP_DOC_UID'], $aRow['DOC_VERSION']);
            $lastVersion = $oAppDocument->getLastAppDocVersion($aRow['APP_DOC_UID'], $sApplicationUID);
            try {
                $aAux1 = $oUser->load($aAux['USR_UID']);

                $sUser = $conf->usersNameFormatBySetParameters($confEnvSetting["format"], $aAux1["USR_USERNAME"],
                    $aAux1["USR_FIRSTNAME"], $aAux1["USR_LASTNAME"]);
            } catch (Exception $oException) {
                $sUser = '***';
            }
            $aFields = array(
                'APP_DOC_UID' => $aAux['APP_DOC_UID'],
                'DOC_UID' => $aAux['DOC_UID'],
                'APP_DOC_COMMENT' => $aAux['APP_DOC_COMMENT'],
                'APP_DOC_FILENAME' => $aAux['APP_DOC_FILENAME'],
                'APP_DOC_INDEX' => $aAux['APP_DOC_INDEX'],
                'TYPE' => $aAux['APP_DOC_TYPE'],
                'ORIGIN' => $aTask['TAS_TITLE'],
                'CREATE_DATE' => $aAux['APP_DOC_CREATE_DATE'],
                'CREATED_BY' => $sUser
            );
            if ($aFields['APP_DOC_FILENAME'] != '') {
                $aFields['TITLE'] = $aFields['APP_DOC_FILENAME'];
            } else {
                $aFields['TITLE'] = $aFields['APP_DOC_COMMENT'];
            }
            //$aFields['POSITION'] = $_SESSION['STEP_POSITION'];
            $aFields['CONFIRM'] = G::LoadTranslation('ID_CONFIRM_DELETE_ELEMENT');
            if (in_array($aRow['APP_DOC_UID'], $aDelete['INPUT_DOCUMENTS'])) {
                $aFields['ID_DELETE'] = G::LoadTranslation('ID_DELETE');
            }
            $aFields['DOWNLOAD_LABEL'] = G::LoadTranslation('ID_DOWNLOAD');
            $aFields['DOWNLOAD_LINK'] = "cases/cases_ShowDocument?a=" . $aRow['APP_DOC_UID'];

            if ($lastVersion == $aRow['DOC_VERSION']) {
                //Show only last version
                $aInputDocuments[] = $aFields;
            }
            $oDataset->next();
        }
        // Get input documents added/modified by a supervisor - Begin
        $oAppDocument = new AppDocument();
        $oCriteria = new Criteria('workflow');
        $oCriteria->add(AppDocumentPeer::APP_UID, $sApplicationUID);
        $oCriteria->add(AppDocumentPeer::APP_DOC_TYPE, array('INPUT'), Criteria::IN);
        $oCriteria->add(AppDocumentPeer::APP_DOC_STATUS, array('ACTIVE'), Criteria::IN);
        $oCriteria->add(AppDocumentPeer::DEL_INDEX, 100000);
        $oCriteria->addJoin(AppDocumentPeer::APP_UID, ApplicationPeer::APP_UID, Criteria::LEFT_JOIN);
        $oCriteria->add(ApplicationPeer::PRO_UID, $sProcessUID);
        $oCriteria->addAscendingOrderByColumn(AppDocumentPeer::APP_DOC_INDEX);
        $oDataset = AppDocumentPeer::doSelectRS($oCriteria);
        $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $oDataset->next();
        $oUser = new ModelUsers();
        while ($aRow = $oDataset->getRow()) {
            $aTask = array('TAS_TITLE' => '[ ' . G::LoadTranslation('ID_SUPERVISOR') . ' ]');
            $aAux = $oAppDocument->load($aRow['APP_DOC_UID'], $aRow['DOC_VERSION']);
            $lastVersion = $oAppDocument->getLastAppDocVersion($aRow['APP_DOC_UID'], $sApplicationUID);
            try {
                $aAux1 = $oUser->load($aAux['USR_UID']);
                $sUser = $conf->usersNameFormatBySetParameters($confEnvSetting["format"], $aAux1["USR_USERNAME"],
                    $aAux1["USR_FIRSTNAME"], $aAux1["USR_LASTNAME"]);
            } catch (Exception $oException) {
                $sUser = '***';
            }
            $aFields = array(
                'APP_DOC_UID' => $aAux['APP_DOC_UID'],
                'DOC_UID' => $aAux['DOC_UID'],
                'APP_DOC_COMMENT' => $aAux['APP_DOC_COMMENT'],
                'APP_DOC_FILENAME' => $aAux['APP_DOC_FILENAME'],
                'APP_DOC_INDEX' => $aAux['APP_DOC_INDEX'],
                'TYPE' => $aAux['APP_DOC_TYPE'],
                'ORIGIN' => $aTask['TAS_TITLE'],
                'CREATE_DATE' => $aAux['APP_DOC_CREATE_DATE'],
                'CREATED_BY' => $sUser
            );
            if ($aFields['APP_DOC_FILENAME'] != '') {
                $aFields['TITLE'] = $aFields['APP_DOC_FILENAME'];
            } else {
                $aFields['TITLE'] = $aFields['APP_DOC_COMMENT'];
            }
            //$aFields['POSITION'] = $_SESSION['STEP_POSITION'];
            $aFields['CONFIRM'] = G::LoadTranslation('ID_CONFIRM_DELETE_ELEMENT');
            if (in_array($aRow['APP_DOC_UID'], $aDelete['INPUT_DOCUMENTS'])) {
                $aFields['ID_DELETE'] = G::LoadTranslation('ID_DELETE');
            }
            $aFields['DOWNLOAD_LABEL'] = G::LoadTranslation('ID_DOWNLOAD');
            $aFields['DOWNLOAD_LINK'] = "cases_ShowDocument?a=" . $aRow['APP_DOC_UID'] . "&v=" . $aRow['DOC_VERSION'];
            $aFields['DOC_VERSION'] = $aRow['DOC_VERSION'];
            if (is_array($listing)) {
                foreach ($listing as $folderitem) {
                    if ($folderitem->filename == $aRow['APP_DOC_UID']) {
                        $aFields['DOWNLOAD_LABEL'] = G::LoadTranslation('ID_GET_EXTERNAL_FILE');
                        $aFields['DOWNLOAD_LINK'] = $folderitem->downloadScript;
                        continue;
                    }
                }
            }

            if ($lastVersion == $aRow['DOC_VERSION']) {
                //Show only last version
                $aInputDocuments[] = $aFields;
            }
            $oDataset->next();
        }
        // Get input documents added/modified by a supervisor - End
        global $_DBArray;
        $_DBArray['inputDocuments'] = $aInputDocuments;

        $oCriteria = new Criteria('dbarray');
        $oCriteria->setDBArrayTable('inputDocuments');
        $oCriteria->addDescendingOrderByColumn('CREATE_DATE');

        return $oCriteria;
    }

    /**
     * get all generate document
     *
     * @name getAllGeneratedDocumentsCriteria
     * @param string $sProcessUID
     * @param string $sApplicationUID
     * @param string $sTasKUID
     * @param string $sUserUID
     *
     * @return object
     * @throws Exception
     */
    public function getAllGeneratedDocumentsCriteria($sProcessUID, $sApplicationUID, $sTasKUID, $sUserUID)
    {

        $conf = new Configurations();
        $confEnvSetting = $conf->getFormats();

        $cases = new ClassesCases();

        $listing = false;
        $oPluginRegistry = PluginRegistry::loadSingleton();
        if ($oPluginRegistry->existsTrigger(PM_CASE_DOCUMENT_LIST)) {
            $folderData = new \folderData(null, null, $sApplicationUID, null, $sUserUID);
            $folderData->PMType = "OUTPUT";
            $folderData->returnList = true;
            $listing = $oPluginRegistry->executeTriggers(PM_CASE_DOCUMENT_LIST, $folderData);
        }
        $aObjectPermissions = $cases->getAllObjects($sProcessUID, $sApplicationUID, $sTasKUID, $sUserUID);
        if (!is_array($aObjectPermissions)) {
            $aObjectPermissions = array(
                'DYNAFORMS' => array(-1),
                'INPUT_DOCUMENTS' => array(-1),
                'OUTPUT_DOCUMENTS' => array(-1)
            );
        }
        if (!isset($aObjectPermissions['DYNAFORMS'])) {
            $aObjectPermissions['DYNAFORMS'] = array(-1);
        } else {
            if (!is_array($aObjectPermissions['DYNAFORMS'])) {
                $aObjectPermissions['DYNAFORMS'] = array(-1);
            }
        }
        if (!isset($aObjectPermissions['INPUT_DOCUMENTS'])) {
            $aObjectPermissions['INPUT_DOCUMENTS'] = array(-1);
        } else {
            if (!is_array($aObjectPermissions['INPUT_DOCUMENTS'])) {
                $aObjectPermissions['INPUT_DOCUMENTS'] = array(-1);
            }
        }
        if (!isset($aObjectPermissions['OUTPUT_DOCUMENTS'])) {
            $aObjectPermissions['OUTPUT_DOCUMENTS'] = array(-1);
        } else {
            if (!is_array($aObjectPermissions['OUTPUT_DOCUMENTS'])) {
                $aObjectPermissions['OUTPUT_DOCUMENTS'] = array(-1);
            }
        }
        $aDelete = $cases->getAllObjectsFrom($sProcessUID, $sApplicationUID, $sTasKUID, $sUserUID, 'DELETE');
        $oAppDocument = new AppDocument();
        $oCriteria = new Criteria('workflow');
        $oCriteria->add(AppDocumentPeer::APP_UID, $sApplicationUID);
        $oCriteria->add(AppDocumentPeer::APP_DOC_TYPE, 'OUTPUT');
        $oCriteria->add(AppDocumentPeer::APP_DOC_STATUS, array('ACTIVE'), Criteria::IN);
        //$oCriteria->add(AppDocumentPeer::APP_DOC_UID, $aObjectPermissions['OUTPUT_DOCUMENTS'], Criteria::IN);
        $oCriteria->add(
            $oCriteria->getNewCriterion(
                AppDocumentPeer::APP_DOC_UID, $aObjectPermissions['OUTPUT_DOCUMENTS'],
                Criteria::IN)->addOr($oCriteria->getNewCriterion(AppDocumentPeer::USR_UID, $sUserUID, Criteria::EQUAL))
        );
        $aConditions = array();
        $aConditions[] = array(AppDocumentPeer::APP_UID, AppDelegationPeer::APP_UID);
        $aConditions[] = array(AppDocumentPeer::DEL_INDEX, AppDelegationPeer::DEL_INDEX);
        $oCriteria->addJoinMC($aConditions, Criteria::LEFT_JOIN);
        $oCriteria->add(AppDelegationPeer::PRO_UID, $sProcessUID);
        $oCriteria->addAscendingOrderByColumn(AppDocumentPeer::APP_DOC_INDEX);
        $oDataset = AppDocumentPeer::doSelectRS($oCriteria);
        $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $oDataset->next();
        $aOutputDocuments = array();
        $aOutputDocuments[] = array(
            'APP_DOC_UID' => 'char',
            'DOC_UID' => 'char',
            'APP_DOC_COMMENT' => 'char',
            'APP_DOC_FILENAME' => 'char',
            'APP_DOC_INDEX' => 'integer'
        );
        $oUser = new ModelUsers();
        while ($aRow = $oDataset->getRow()) {
            $oCriteria2 = new Criteria('workflow');
            $oCriteria2->add(AppDelegationPeer::APP_UID, $sApplicationUID);
            $oCriteria2->add(AppDelegationPeer::DEL_INDEX, $aRow['DEL_INDEX']);
            $oDataset2 = AppDelegationPeer::doSelectRS($oCriteria2);
            $oDataset2->setFetchmode(ResultSet::FETCHMODE_ASSOC);
            $oDataset2->next();
            $aRow2 = $oDataset2->getRow();
            $oTask = new ModelTask();
            if ($oTask->taskExists($aRow2['TAS_UID'])) {
                $aTask = $oTask->load($aRow2['TAS_UID']);
            } else {
                $aTask = array('TAS_TITLE' => '(TASK DELETED)');
            }
            $lastVersion = $oAppDocument->getLastDocVersion($aRow['DOC_UID'], $sApplicationUID);
            if ($lastVersion == $aRow['DOC_VERSION']) {
                //Only show last document Version
                $aAux = $oAppDocument->load($aRow['APP_DOC_UID'], $aRow['DOC_VERSION']);
                //Get output Document information
                $oOutputDocument = new \OutputDocument();
                $aGields = $oOutputDocument->load($aRow['DOC_UID']);
                //OUTPUTDOCUMENT
                $outDocTitle = $aGields['OUT_DOC_TITLE'];
                switch ($aGields['OUT_DOC_GENERATE']) {
                    //G::LoadTranslation(ID_DOWNLOAD)
                    case "PDF":
                        $fileDoc = 'javascript:alert("NO DOC")';
                        $fileDocLabel = " ";
                        $filePdf = 'cases/cases_ShowOutputDocument?a=' .
                            $aRow['APP_DOC_UID'] . '&v=' . $aRow['DOC_VERSION'] . '&ext=pdf&random=' . rand();
                        $filePdfLabel = ".pdf";
                        if (is_array($listing)) {
                            foreach ($listing as $folderitem) {
                                if (($folderitem->filename == $aRow['APP_DOC_UID']) && ($folderitem->type == "PDF")) {
                                    $filePdfLabel = G::LoadTranslation('ID_GET_EXTERNAL_FILE') . " .pdf";
                                    $filePdf = $folderitem->downloadScript;
                                    continue;
                                }
                            }
                        }
                        break;
                    case "DOC":
                        $fileDoc = 'cases/cases_ShowOutputDocument?a=' .
                            $aRow['APP_DOC_UID'] . '&v=' . $aRow['DOC_VERSION'] . '&ext=doc&random=' . rand();
                        $fileDocLabel = ".doc";
                        $filePdf = 'javascript:alert("NO PDF")';
                        $filePdfLabel = " ";
                        if (is_array($listing)) {
                            foreach ($listing as $folderitem) {
                                if (($folderitem->filename == $aRow['APP_DOC_UID']) && ($folderitem->type == "DOC")) {
                                    $fileDocLabel = G::LoadTranslation('ID_GET_EXTERNAL_FILE') . " .doc";
                                    $fileDoc = $folderitem->downloadScript;
                                    continue;
                                }
                            }
                        }
                        break;
                    case "BOTH":
                        $fileDoc = 'cases/cases_ShowOutputDocument?a=' .
                            $aRow['APP_DOC_UID'] . '&v=' . $aRow['DOC_VERSION'] . '&ext=doc&random=' . rand();
                        $fileDocLabel = ".doc";
                        if (is_array($listing)) {
                            foreach ($listing as $folderitem) {
                                if (($folderitem->filename == $aRow['APP_DOC_UID']) && ($folderitem->type == "DOC")) {
                                    $fileDocLabel = G::LoadTranslation('ID_GET_EXTERNAL_FILE') . " .doc";
                                    $fileDoc = $folderitem->downloadScript;
                                    continue;
                                }
                            }
                        }
                        $filePdf = 'cases/cases_ShowOutputDocument?a=' .
                            $aRow['APP_DOC_UID'] . '&v=' . $aRow['DOC_VERSION'] . '&ext=pdf&random=' . rand();
                        $filePdfLabel = ".pdf";

                        if (is_array($listing)) {
                            foreach ($listing as $folderitem) {
                                if (($folderitem->filename == $aRow['APP_DOC_UID']) && ($folderitem->type == "PDF")) {
                                    $filePdfLabel = G::LoadTranslation('ID_GET_EXTERNAL_FILE') . " .pdf";
                                    $filePdf = $folderitem->downloadScript;
                                    continue;
                                }
                            }
                        }
                        break;
                }
                try {
                    $aAux1 = $oUser->load($aAux['USR_UID']);
                    $sUser = $conf->usersNameFormatBySetParameters($confEnvSetting["format"], $aAux1["USR_USERNAME"],
                        $aAux1["USR_FIRSTNAME"], $aAux1["USR_LASTNAME"]);
                } catch (Exception $oException) {
                    $sUser = '(USER DELETED)';
                }
                //if both documents were generated, we choose the pdf one, only if doc was
                //generate then choose the doc file.
                $firstDocLink = $filePdf;
                $firstDocLabel = $filePdfLabel;
                if ($aGields['OUT_DOC_GENERATE'] == 'DOC') {
                    $firstDocLink = $fileDoc;
                    $firstDocLabel = $fileDocLabel;
                }
                $aFields = array(
                    'APP_DOC_UID' => $aAux['APP_DOC_UID'],
                    'DOC_UID' => $aAux['DOC_UID'],
                    'APP_DOC_COMMENT' => $aAux['APP_DOC_COMMENT'],
                    'APP_DOC_FILENAME' => $aAux['APP_DOC_FILENAME'],
                    'APP_DOC_INDEX' => $aAux['APP_DOC_INDEX'],
                    'ORIGIN' => $aTask['TAS_TITLE'],
                    'CREATE_DATE' => $aAux['APP_DOC_CREATE_DATE'],
                    'CREATED_BY' => $sUser,
                    'FILEDOC' => $fileDoc,
                    'FILEPDF' => $filePdf,
                    'OUTDOCTITLE' => $outDocTitle,
                    'DOC_VERSION' => $aAux['DOC_VERSION'],
                    'TYPE' => $aAux['APP_DOC_TYPE'] . ' ' . $aGields['OUT_DOC_GENERATE'],
                    'DOWNLOAD_LINK' => $firstDocLink,
                    'DOWNLOAD_FILE' => $aAux['APP_DOC_FILENAME'] . $firstDocLabel
                );
                if (trim($fileDocLabel) != '') {
                    $aFields['FILEDOCLABEL'] = $fileDocLabel;
                }
                if (trim($filePdfLabel) != '') {
                    $aFields['FILEPDFLABEL'] = $filePdfLabel;
                }
                if ($aFields['APP_DOC_FILENAME'] != '') {
                    $aFields['TITLE'] = $aFields['APP_DOC_FILENAME'];
                } else {
                    $aFields['TITLE'] = $aFields['APP_DOC_COMMENT'];
                }
                //$aFields['POSITION'] = $_SESSION['STEP_POSITION'];
                $aFields['CONFIRM'] = G::LoadTranslation('ID_CONFIRM_DELETE_ELEMENT');
                if (in_array($aRow['APP_DOC_UID'], $aObjectPermissions['OUTPUT_DOCUMENTS'])) {
                    if (in_array($aRow['APP_DOC_UID'], $aDelete['OUTPUT_DOCUMENTS'])) {
                        $aFields['ID_DELETE'] = G::LoadTranslation('ID_DELETE');
                    }
                }
                $aOutputDocuments[] = $aFields;
            }
            $oDataset->next();
        }
        global $_DBArray;
        $_DBArray['outputDocuments'] = $aOutputDocuments;

        $oCriteria = new Criteria('dbarray');
        $oCriteria->setDBArrayTable('outputDocuments');
        $oCriteria->addDescendingOrderByColumn('CREATE_DATE');

        return $oCriteria;
    }

    /**
     * Get fields and values by DynaForm
     *
     * @param array $form
     * @param array $appData
     * @param array $caseVariable
     *
     * @return array
     * @throws Exception
     */
    private function getFieldsAndValuesByDynaFormAndAppData(array $form, array $appData, array $caseVariable)
    {
        try {
            foreach ($form['items'] as $value) {
                foreach ($value as $field) {
                    if (isset($field['type'])) {
                        if ($field['type'] != 'form') {
                            foreach ($field as $key => $val) {
                                if (is_string($val) && in_array(substr($val, 0, 2), PmDynaform::$prefixs)) {
                                    $field[$key] = substr($val, 2);
                                }
                            }
                            foreach ($appData as $key => $val) {
                                if (in_array($key, $field, true) != false) {
                                    $caseVariable[$key] = $this->getFieldValue($field, $appData[$key]);
                                    if (isset($appData[$key . '_label'])) {
                                        $caseVariable[$key . '_label'] = $appData[$key . '_label'];
                                    }
                                }
                            }
                        } else {
                            $caseVariableAux = $this->getFieldsAndValuesByDynaFormAndAppData($field, $appData,
                                $caseVariable);
                            $caseVariable = array_merge($caseVariable, $caseVariableAux);
                        }
                    }
                }
            }

            return $caseVariable;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Return the field value to be used in the front-end client.
     *
     * @param type $field
     * @param type $value
     *
     * @return string
     */
    private function getFieldValue($field, $value)
    {
        switch ($field['type']) {
            case 'file':
                return $field['data']['app_doc_uid'];
            default:
                return $value;
        }
    }

    /**
     * Get Case Variables
     *
     * @access public
     * @param string $app_uid , Uid for case
     * @param string $usr_uid , Uid for user
     * @param string $dynaFormUid , Uid for dynaform
     *
     * @return array
     */
    public function getCaseVariables(
        $app_uid,
        $usr_uid,
        $dynaFormUid = null,
        $pro_uid = null,
        $act_uid = null,
        $app_index = null
    ) {
        Validator::isString($app_uid, '$app_uid');
        Validator::appUid($app_uid, '$app_uid');
        Validator::isString($usr_uid, '$usr_uid');
        Validator::usrUid($usr_uid, '$usr_uid');

        $case = new ClassesCases();
        $fields = $case->loadCase($app_uid);

        $arrayCaseVariable = [];

        if (!is_null($dynaFormUid)) {
            $data = [];
            $data["APP_DATA"] = $fields['APP_DATA'];
            $data["CURRENT_DYNAFORM"] = $dynaFormUid;
            $pmDynaForm = new PmDynaform($data);
            $arrayDynaFormData = $pmDynaForm->getDynaform();
            $arrayDynContent = G::json_decode($arrayDynaFormData['DYN_CONTENT']);
            $pmDynaForm->jsonr($arrayDynContent);

            $arrayDynContent = G::json_decode(G::json_encode($arrayDynContent), true);

            $arrayAppData = $fields['APP_DATA'];

            $arrayCaseVariable = $this->getFieldsAndValuesByDynaFormAndAppData(
                $arrayDynContent['items'][0], $arrayAppData, $arrayCaseVariable
            );
        } else {
            $arrayCaseVariable = $fields['APP_DATA'];
        }

        //Get historyDate for Dynaform
        if (!is_null($pro_uid) && !is_null($act_uid) && !is_null($app_index)) {
            $oCriteriaAppHistory = new Criteria("workflow");
            $oCriteriaAppHistory->addSelectColumn(AppHistoryPeer::HISTORY_DATE);
            $oCriteriaAppHistory->add(AppHistoryPeer::APP_UID, $app_uid, Criteria::EQUAL);
            $oCriteriaAppHistory->add(AppHistoryPeer::DEL_INDEX, $app_index, Criteria::EQUAL);
            $oCriteriaAppHistory->add(AppHistoryPeer::PRO_UID, $pro_uid, Criteria::EQUAL);
            $oCriteriaAppHistory->add(AppHistoryPeer::TAS_UID, $act_uid, Criteria::EQUAL);
            $oCriteriaAppHistory->add(AppHistoryPeer::USR_UID, $usr_uid, Criteria::EQUAL);
            if (!is_null($dynaFormUid)) {
                $oCriteriaAppHistory->add(AppHistoryPeer::DYN_UID, $dynaFormUid, Criteria::EQUAL);
            }
            $oCriteriaAppHistory->addDescendingOrderByColumn('HISTORY_DATE');
            $oCriteriaAppHistory->setLimit(1);
            $oDataset = AppDocumentPeer::doSelectRS($oCriteriaAppHistory);
            $oDataset->setFetchmode(ResultSet::FETCHMODE_ASSOC);
            $oDataset->next();
            if ($aRow = $oDataset->getRow()) {
                $dateHistory['SYS_VAR_UPDATE_DATE'] = $aRow['HISTORY_DATE'];
            } else {
                $dateHistory['SYS_VAR_UPDATE_DATE'] = null;
            }
            $arrayCaseVariable = array_merge($arrayCaseVariable, $dateHistory);
        }

        // Get the SYS_LANG defined, it can be updated
        if (defined('SYS_LANG')) {
            $arrayCaseVariable['SYS_LANG'] = SYS_LANG;
        }

        return $arrayCaseVariable;
    }

    /**
     * Put Set Case Variables
     *
     * @access public
     * @param string $app_uid , Uid for case
     * @param array $app_data , Data for case variables
     * @param string $dyn_uid , Uid for dynaform
     * @param string $del_index , Index for case
     * @param string $usr_uid , Uid for user
     *
     * @return void
     * @throws Exception
     */
    public function setCaseVariables($app_uid, $app_data, $dyn_uid = null, $usr_uid = '', $del_index = 0)
    {
        Validator::isString($app_uid, '$app_uid');
        Validator::appUid($app_uid, '$app_uid');
        Validator::isArray($app_data, '$app_data');
        Validator::isString($usr_uid, '$usr_uid');
        Validator::usrUid($usr_uid, '$usr_uid');
        // Validate the system variables
        $systemVars = G::getSystemConstants();
        foreach ($systemVars as $key => $var) {
            if (array_key_exists($key, $app_data)) {
                throw new Exception(G::LoadTranslation("ID_CAN_NOT_CHANGE"));
            }
        }

        $arrayResult = $this->getStatusInfo($app_uid);

        if ($arrayResult["APP_STATUS"] == "CANCELLED") {
            throw new Exception(G::LoadTranslation("ID_CASE_CANCELLED", [$app_uid]));
        }

        if ($arrayResult["APP_STATUS"] == "COMPLETED") {
            throw new Exception(G::LoadTranslation("ID_CASE_IS_COMPLETED", [$app_uid]));
        }

        // Review if the user has participation or is supervisor
        $caseNumber = ModelApplication::getCaseNumber($app_uid);
        $permission = $this->participation($usr_uid, $caseNumber, $del_index);
        if (!$permission) {
            throw new Exception(G::LoadTranslation("ID_NO_PERMISSION_NO_PARTICIPATED", [$usr_uid]));
        }

        $_SESSION['APPLICATION'] = $app_uid;
        $_SESSION['USER_LOGGED'] = $usr_uid;

        $arrayVariableDocumentToDelete = [];

        if (array_key_exists('__VARIABLE_DOCUMENT_DELETE__', $app_data)) {
            if (is_array($app_data['__VARIABLE_DOCUMENT_DELETE__']) && !empty($app_data['__VARIABLE_DOCUMENT_DELETE__'])) {
                $arrayVariableDocumentToDelete = $app_data['__VARIABLE_DOCUMENT_DELETE__'];
            }

            unset($app_data['__VARIABLE_DOCUMENT_DELETE__']);
        }

        $case = new ClassesCases();
        $fields = $case->loadCase($app_uid, $del_index);
        $_POST['form'] = $app_data;

        if (!is_null($dyn_uid) && $dyn_uid != '') {
            $oDynaform = \DynaformPeer::retrieveByPK($dyn_uid);

            if ($oDynaform->getDynVersion() < 2) {
                $oForm = new \Form ($fields['PRO_UID'] . "/" . $dyn_uid, PATH_DYNAFORM);
                $oForm->validatePost();
            }
        }

        if (!is_null($dyn_uid) && $del_index > 0) {
            //save data
            $data = array();
            $data['APP_NUMBER'] = $fields['APP_NUMBER'];
            $data['APP_DATA'] = $fields['APP_DATA'];
            $data['DEL_INDEX'] = $del_index;
            $data['TAS_UID'] = $fields['TAS_UID'];;
            $data['CURRENT_DYNAFORM'] = $dyn_uid;
            $data['USER_UID'] = $usr_uid;
            $data['PRO_UID'] = $fields['PRO_UID'];
        }
        $data['APP_DATA'] = array_merge($fields['APP_DATA'], $_POST['form']);
        $case->updateCase($app_uid, $data);

        //Delete MultipleFile
        if (!empty($arrayVariableDocumentToDelete)) {
            $this->deleteMultipleFile($app_uid, $arrayVariableDocumentToDelete);
        }
    }

    /**
     * Get Case Notes
     *
     * @param string $appUid
     * @param string $usrUid
     * @param array $parameters
     *
     * @return array
     * @throws \PropelException
     * @access public
     */
    public function getCaseNotes($appUid, $usrUid, $parameters = [])
    {
        // Validate parameters
        Validator::isString($appUid, '$app_uid');
        Validator::appUid($appUid, '$app_uid');
        Validator::isString($usrUid, '$usr_uid');
        Validator::usrUid($usrUid, '$usr_uid');
        Validator::isArray($parameters, '$parameters');
        Validator::isArray($parameters, '$parameters');
        $start = isset($parameters["start"]) ? $parameters["start"] : "0";
        $limit = isset($parameters["limit"]) ? $parameters["limit"] : "";
        $sort = isset($parameters["sort"]) ? $parameters["sort"] : "NOTE_DATE";
        $dir = isset($parameters["dir"]) ? $parameters["dir"] : "DESC";
        $user = isset($parameters["user"]) ? $parameters["user"] : "";
        $dateFrom = (!empty($parameters["dateFrom"])) ? substr($parameters["dateFrom"], 0, 10) : "";
        $dateTo = (!empty($parameters["dateTo"])) ? substr($parameters["dateTo"], 0, 10) : "";
        $search = isset($parameters["search"]) ? $parameters["search"] : "";
        $paged = isset($parameters["paged"]) ? $parameters["paged"] : true;
        $files = isset($parameters["files"]) ? $parameters["files"] : false;
        if (!empty($user)) {
            Validator::usrUid($user, '$usr_uid');
        }
        if (!empty($dateFrom)) {
            Validator::isDate($dateFrom, 'Y-m-d', '$date_from');
        }
        if (!empty($dateTo)) {
            Validator::isDate($dateTo, 'Y-m-d', '$date_to');
        }
        // Review the process permissions
        $case = new ClassesCases();
        $caseLoad = $case->loadCase($appUid);
        $proUid = $caseLoad['PRO_UID'];
        $tasUid = AppDelegation::getCurrentTask($appUid);
        $respView = $case->getAllObjectsFrom($proUid, $appUid, $tasUid, $usrUid, 'VIEW');
        $respBlock = $case->getAllObjectsFrom($proUid, $appUid, $tasUid, $usrUid, 'BLOCK');
        if ($respView['CASES_NOTES'] == 0 && $respBlock['CASES_NOTES'] == 0) {
            throw new Exception(G::LoadTranslation("ID_THIS_USER_DOESNT_HAVE_PERMISSIONS_TO_SEE_CASE_NOTES"));
        }
        // Get the notes
        $appNote = new Notes();
        $notes = $appNote->getNotes($appUid, $start, $limit, $dir);
        $notes = AppNotes::applyHtmlentitiesInNotes($notes);
        // Add a the notes the files related
        $documents = new Documents();
        $iterator = 0;
        $data = [];
        foreach ($notes['notes'] as $value) {
            $data[$iterator] = array_change_key_case($value, CASE_LOWER);
            $data[$iterator]['note_date'] = UtilDateTime::convertUtcToTimeZone($value['NOTE_DATE']);
            if ($files) {
                $data[$iterator]['attachments'] = $documents->getFiles($value['NOTE_ID'], $appUid);
            }
            $iterator++;
        }
        // If is paged will add the filters used
        $filters = [];
        if ($paged) {
            $total = $appNote->getTotal($appUid);
            $filters['total'] = $total;
            $filters['start'] = $start;
            $filters['limit'] = $limit;
            $filters['sort'] = $sort;
            $filters['dir'] = $dir;
            $filters['usr_uid'] = $user;
            $filters['date_to'] = $dateTo;
            $filters['date_from'] = $dateFrom;
            $filters['search'] = $search;
        }
        // Prepare the response
        $response = [];
        if ($paged) {
            $response = $filters;
            $response['data'] = $data;
        } else {
            $response = $data;
        }

        return $response;
    }

    /**
     * Save new case note
     *
     * @access public
     * @param string $appUid, Uid for case
     * @param string $usrUid, Uid for user
     * @param string $noteContent
     * @param boolean $sendMail
     *
     * @return void
     * @throws Exception
     */
    public function saveCaseNote($appUid, $usrUid, $noteContent, $sendMail = false)
    {
        Validator::isString($appUid, '$app_uid');
        Validator::appUid($appUid, '$app_uid');
        Validator::isString($usrUid, '$usr_uid');
        Validator::usrUid($usrUid, '$usr_uid');
        Validator::isString($noteContent, '$note_content');
        if (strlen($noteContent) > 500) {
            throw (new Exception(G::LoadTranslation("ID_INVALID_MAX_PERMITTED", [$noteContent, '500'])));
        }
        Validator::isBoolean($sendMail, '$send_mail');
        // Review the process permissions
        $case = new ClassesCases();
        $caseLoad = $case->loadCase($appUid);
        $proUid = $caseLoad['PRO_UID'];
        $tasUid = AppDelegation::getCurrentTask($appUid);
        $respView = $case->getAllObjectsFrom($proUid, $appUid, $tasUid, $usrUid, 'VIEW');
        $respBlock = $case->getAllObjectsFrom($proUid, $appUid, $tasUid, $usrUid, 'BLOCK');
        if ($respView['CASES_NOTES'] == 0 && $respBlock['CASES_NOTES'] == 0) {
            throw (new Exception(G::LoadTranslation("ID_CASES_NOTES_NO_PERMISSIONS")));
        }
        // Save the notes
        $response = $this->addNote($appUid, $usrUid, $noteContent, intval($sendMail));
    }

    /**
     * Get data of a Task from a record
     *
     * @param array $record Record
     *
     * @return array Return an array with data Task
     * @throws Exception
     */
    public function getTaskDataFromRecord(array $record)
    {
        try {
            return array(
                $this->getFieldNameByFormatFieldName("TAS_UID") => $record["TAS_UID"],
                $this->getFieldNameByFormatFieldName("TAS_TITLE") => $record["TAS_TITLE"] . "",
                $this->getFieldNameByFormatFieldName("TAS_DESCRIPTION") => $record["TAS_DESCRIPTION"] . "",
                $this->getFieldNameByFormatFieldName("TAS_START") => ($record["TAS_START"] == "TRUE") ? 1 : 0,
                $this->getFieldNameByFormatFieldName("TAS_TYPE") => $record["TAS_TYPE"],
                $this->getFieldNameByFormatFieldName("TAS_DERIVATION") => $record["TAS_DERIVATION"],
                $this->getFieldNameByFormatFieldName("TAS_ASSIGN_TYPE") => $record["TAS_ASSIGN_TYPE"],
                $this->getFieldNameByFormatFieldName("USR_UID") => $record["USR_UID"] . "",
                $this->getFieldNameByFormatFieldName("USR_USERNAME") => $record["USR_USERNAME"] . "",
                $this->getFieldNameByFormatFieldName("USR_FIRSTNAME") => $record["USR_FIRSTNAME"] . "",
                $this->getFieldNameByFormatFieldName("USR_LASTNAME") => $record["USR_LASTNAME"] . ""
            );
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get all Tasks of Case
     * Based in: processmaker/workflow/engine/classes/class.processMap.php
     * Method: processMap::load()
     *
     * @param string $applicationUid Unique id of Case
     *
     * @see workflow/engine/src/ProcessMaker/Services/Api/Cases.php
     * @see workflow/engine/src/ProcessMaker/Services/Api/Light.php
     *
     * @link https://wiki.processmaker.com/3.3/REST_API_Cases/Cases#Get_Case.27s_Tasks:_GET_.2Fcases.2F.7Bapp_uid.7D.2Ftasks
     *
     * @return array Return an array with all Tasks of Case
     * @throws Exception
     */
    public function getTasks($applicationUid)
    {
        try {
            $arrayTask = array();

            //Verify data
            $this->throwExceptionIfNotExistsCase($applicationUid, 0, $this->getFieldNameByFormatFieldName("APP_UID"));

            //Set variables
            $process = new \Process();
            $application = new \Application();
            $conf = new Configurations();

            $arrayApplicationData = $application->Load($applicationUid);
            $processUid = $arrayApplicationData["PRO_UID"];

            $confEnvSetting = $conf->getFormats();

            $taskUid = "";

            //Obtain the list of tasks and their respectives users assigned to each one for an specific case
            $case = new ClassesCases();
            $rsTasks = $case->getTasksInfoForACase($applicationUid, $processUid);

            while ($rsTasks->next()) {
                $row = $rsTasks->getRow();

                //If the task is a multiple task
                if ($row["TAS_ASSIGN_TYPE"] == 'MULTIPLE_INSTANCE' || $row["TAS_ASSIGN_TYPE"] == 'MULTIPLE_INSTANCE_VALUE_BASED') {
                    $row["USR_UID"] = "";
                    $row["USR_USERNAME"] = "";
                    $row["USR_FIRSTNAME"] = "";
                    $row["USR_LASTNAME"] = "";
                }

                //Task
                if ($row["TAS_TYPE"] == "NORMAL") {
                    if (($row["TAS_TITLE"] . "" == "")) {
                        //There is no Label in Current SYS_LANG language so try to find in English - by default
                        $task = new ModelTask();
                        $task->setTasUid($row["TAS_UID"]);

                        $row["TAS_TITLE"] = $task->getTasTitle();
                    }
                } else {

                    //Get the task information when the task type is different from normal
                    $rsCriteria2 = $case->getTaskInfoForSubProcess($processUid, $row["TAS_UID"]);

                    $rsCriteria2->next();

                    $row2 = $rsCriteria2->getRow();
                    $proUid = isset($row2["PRO_UID"]) ? $row2["PRO_UID"] : '';
                    if (!empty($proUid) && $process->exists($proUid)) {
                        $row["TAS_TITLE"] = $row2["TAS_TITLE"];
                        $row["TAS_DESCRIPTION"] = $row2["TAS_DESCRIPTION"];
                    }
                }

                //Routes
                $routeType = "";
                $arrayRoute = array();

                //Get the routes of a task
                $rsCriteria2 = $case->getTaskRoutes($processUid, $row["TAS_UID"]);

                while ($rsCriteria2->next()) {
                    $row2 = $rsCriteria2->getRow();

                    $routeType = $row2["ROU_TYPE"];

                    $arrayRoute[] = array(
                        $this->getFieldNameByFormatFieldName("ROU_NUMBER") => (int)($row2["ROU_NUMBER"]),
                        $this->getFieldNameByFormatFieldName("ROU_CONDITION") => $row2["ROU_CONDITION"] . "",
                        $this->getFieldNameByFormatFieldName("TAS_UID") => $row2["TAS_UID"]
                    );
                }

                //Delegations
                $arrayAppDelegation = array();

                $rsCriteria2 = $case->getCaseDelegations($applicationUid, $row["TAS_UID"]);

                while ($rsCriteria2->next()) {
                    $row2 = $rsCriteria2->getRow();

                    $arrayAppDelegationDate = array(
                        "DEL_INIT_DATE" => array(
                            "date" => $row2["DEL_INIT_DATE"],
                            "dateFormated" => G::LoadTranslation("ID_CASE_NOT_YET_STARTED")
                        ),
                        "DEL_TASK_DUE_DATE" => array(
                            "date" => $row2["DEL_TASK_DUE_DATE"],
                            "dateFormated" => G::LoadTranslation("ID_CASE_NOT_YET_STARTED")
                        ),
                        "DEL_FINISH_DATE" => array(
                            "date" => $row2["DEL_FINISH_DATE"],
                            "dateFormated" => G::LoadTranslation("ID_NOT_FINISHED")
                        )
                    );

                    foreach ($arrayAppDelegationDate as $key => $value) {
                        $d = $value;

                        if (!empty($d["date"])) {
                            $dateTime = new \DateTime($d["date"]);
                            $arrayAppDelegationDate[$key]["dateFormated"] = $dateTime->format($confEnvSetting["dateFormat"]);
                        }
                    }

                    $appDelegationDuration = G::LoadTranslation("ID_NOT_FINISHED");

                    $date = empty($row2["DEL_INIT_DATE"]) ? $row2["DEL_DELEGATE_DATE"] : $row2["DEL_INIT_DATE"];

                    if (!empty($row2["DEL_FINISH_DATE"]) && !empty($date)) {
                        $t = strtotime($row2["DEL_FINISH_DATE"]) - strtotime($date);

                        $h = $t * (1 / 60) * (1 / 60);
                        $m = ($h - (int)($h)) * (60 / 1);
                        $s = ($m - (int)($m)) * (60 / 1);

                        $h = (int)($h);
                        $m = (int)($m);

                        $appDelegationDuration = $h . " " . (($h == 1) ? G::LoadTranslation("ID_HOUR") : G::LoadTranslation("ID_HOURS"));
                        $appDelegationDuration = $appDelegationDuration . " " . $m . " " . (($m == 1) ? G::LoadTranslation("ID_MINUTE") : G::LoadTranslation("ID_MINUTES"));
                        $appDelegationDuration = $appDelegationDuration . " " . $s . " " . (($s == 1) ? G::LoadTranslation("ID_SECOND") : G::LoadTranslation("ID_SECONDS"));
                    }

                    $arrayAppDelegation[] = array(
                        $this->getFieldNameByFormatFieldName("DEL_INDEX") => (int)($row2["DEL_INDEX"]),
                        $this->getFieldNameByFormatFieldName("DEL_INIT_DATE") => $arrayAppDelegationDate["DEL_INIT_DATE"]["dateFormated"],
                        $this->getFieldNameByFormatFieldName("DEL_TASK_DUE_DATE") => $arrayAppDelegationDate["DEL_TASK_DUE_DATE"]["dateFormated"],
                        $this->getFieldNameByFormatFieldName("DEL_FINISH_DATE") => $arrayAppDelegationDate["DEL_FINISH_DATE"]["dateFormated"],
                        $this->getFieldNameByFormatFieldName("DEL_DURATION") => $appDelegationDuration,
                        $this->getFieldNameByFormatFieldName("USR_UID") => $row2["USR_UID"],
                        $this->getFieldNameByFormatFieldName("USR_USERNAME") => $row2["USR_USERNAME"],
                        $this->getFieldNameByFormatFieldName("USR_FIRSTNAME") => $row2["USR_FIRSTNAME"],
                        $this->getFieldNameByFormatFieldName("USR_LASTNAME") => $row2["USR_LASTNAME"]
                    );
                }

                //Status
                $status = "";

                $rsCriteria2 = $case->getTotalAndMinDateForACase($applicationUid, $row["TAS_UID"]);

                $rsCriteria2->next();

                $row2 = $rsCriteria2->getRow();

                $rsCriteria3 = $case->getDelegationFinishDate($applicationUid, $row["TAS_UID"]);

                $rsCriteria3->next();

                $row3 = $rsCriteria3->getRow();

                if ($row3) {
                    $row2["FINISH"] = "";
                }

                //Status
                if (empty($row2["FINISH"]) && !is_null($taskUid) && $row["TAS_UID"] == $taskUid) {
                    $status = "TASK_IN_PROGRESS"; //Red
                } else {
                    if (!empty($row2["FINISH"])) {
                        $status = "TASK_COMPLETED"; //Green
                    } else {
                        if ($routeType != "SEC-JOIN") {
                            if ($row2["CANT"] != 0) {
                                $status = "TASK_IN_PROGRESS"; //Red
                            } else {
                                $status = "TASK_PENDING_NOT_EXECUTED"; //Gray
                            }
                        } else {
                            //$status = "TASK_PARALLEL"; //Yellow

                            if ($row3) {
                                $status = "TASK_IN_PROGRESS"; //Red
                            } else {
                                $status = "TASK_PENDING_NOT_EXECUTED"; //Gray
                            }
                        }
                    }
                }

                //Set data
                $arrayAux = $this->getTaskDataFromRecord($row);
                $arrayAux[$this->getFieldNameByFormatFieldName("ROUTE")][$this->getFieldNameByFormatFieldName("TYPE")] = $routeType;
                $arrayAux[$this->getFieldNameByFormatFieldName("ROUTE")][$this->getFieldNameByFormatFieldName("TO")] = $arrayRoute;
                $arrayAux[$this->getFieldNameByFormatFieldName("DELEGATIONS")] = $arrayAppDelegation;
                $arrayAux[$this->getFieldNameByFormatFieldName("STATUS")] = $status;

                $arrayTask[] = $arrayAux;
            }

            //Return
            return $arrayTask;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Put execute triggers
     *
     * @access public
     * @param string $app_uid , Uid for case
     * @param int $del_index , Index for case
     * @param string $obj_type , Index for case
     * @param string $obj_uid , Index for case
     *
     * @return void
     */
    public function putExecuteTriggers($app_uid, $del_index, $obj_type, $obj_uid)
    {
        Validator::isString($app_uid, '$app_uid');
        Validator::appUid($app_uid, '$app_uid');
        Validator::isInteger($del_index, '$del_index');

        $oCase = new ClassesCases();
        $aField = $oCase->loadCase($app_uid, $del_index);
        $tas_uid = $aField["TAS_UID"];

        $aField["APP_DATA"] = $oCase->executeTriggers($tas_uid, $obj_type, $obj_uid, "AFTER", $aField["APP_DATA"]);
        $aField = $oCase->updateCase($app_uid, $aField);
    }

    /**
     * Get Steps evaluate
     *
     * @access public
     * @param string $app_uid , Uid for case
     * @param int $del_index , Index for case
     *
     * @return array
     */
    public function getSteps($app_uid, $del_index)
    {
        Validator::isString($app_uid, '$app_uid');
        Validator::appUid($app_uid, '$app_uid');
        Validator::isInteger($del_index, '$del_index');

        $oCase = new ClassesCases();
        $aCaseField = $oCase->loadCase($app_uid, $del_index);
        $tas_uid = $aCaseField["TAS_UID"];
        $pro_uid = $aCaseField["PRO_UID"];

        $oApplication = new Applications();
        $aField = $oApplication->getSteps($app_uid, $del_index, $tas_uid, $pro_uid);

        return $aField;
    }

    /**
     * This function get the status information
     *
     * @param array $result
     * @param string $status
     *
     * @return array
     * @throws Exception
    */
    private function getStatusInfoFormatted(array $result, string $status = '')
    {
        try {
            $record = head($result);
            $arrayData = [
                'APP_STATUS' => empty($status) ? $record['APP_STATUS'] : $status,
                'DEL_INDEX' => [],
                'PRO_UID' => $record['PRO_UID']
            ];
            foreach ($result as $record) {
                $arrayData['DEL_INDEX'][] = $record['DEL_INDEX'];
            }
            //Return
            return $arrayData;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get status info Case
     *
     * @param string $appUid Unique id of Case
     * @param int $index Delegation index
     * @param string $userUid Unique id of User
     *
     * @return array Return an array with status info Case, array empty otherwise
     * @throws Exception
     *
     * @see workflow/engine/methods/cases/main_init.php
     * @see workflow/engine/methods/cases/opencase.php
     * @see \ProcessMaker\BusinessModel\Cases::setCaseVariables()
     * @see \ProcessMaker\BusinessModel\Cases\InputDocument::getCasesInputDocuments()
     * @see \ProcessMaker\BusinessModel\Cases\InputDocument::throwExceptionIfHaventPermissionToDelete()
     * @see \ProcessMaker\BusinessModel\Cases\OutputDocument::throwExceptionIfCaseNotIsInInbox()
     * @see \ProcessMaker\BusinessModel\Cases\OutputDocument::throwExceptionIfHaventPermissionToDelete()
     */
    public function getStatusInfo(string $appUid, int $index = 0, string $userUid = "")
    {
        try {
            $arrayData = [];
            // Verify data
            $this->throwExceptionIfNotExistsCase($appUid, $index, $this->getFieldNameByFormatFieldName("APP_UID"));
            // Get the case number
            $caseNumber = ModelApplication::getCaseNumber($appUid);
            // Status is PAUSED
            $result = Delay::getPaused($caseNumber, $index, $userUid);
            if (!empty($result)) {
                $arrayData = $this->getStatusInfoFormatted($result, 'PAUSED');
                return $arrayData;
            }

            // Status is UNASSIGNED
            $query = Delegation::query()->select([
                'APP_DELEGATION.APP_NUMBER',
                'APP_DELEGATION.DEL_INDEX',
                'APP_DELEGATION.PRO_UID'
            ]);
            $query->taskAssignType('SELF_SERVICE');
            $query->threadOpen()->withoutUserId();
            // Filter specific user
            if (!empty($userUid)) {
                $delegation = new Delegation();
                $delegation->casesUnassigned($query, $userUid);
            }
            // Filter specific case
            $query->case($caseNumber);
            // Filter specific index
            if ($index > 0) {
                $query->index($index);
            }
            $results = $query->get();
            $arrayData = $results->values()->toArray();
            if (!empty($arrayData)) {
                $arrayData = $this->getStatusInfoFormatted($arrayData, 'UNASSIGNED');
                return $arrayData;
            }

            // Status is TO_DO, DRAFT
            $query = Delegation::query()->select([
                'APPLICATION.APP_STATUS',
                'APP_DELEGATION.APP_NUMBER',
                'APP_DELEGATION.DEL_INDEX',
                'APP_DELEGATION.PRO_UID'
            ]);
            $query->joinApplication();
            // Filter the status TO_DO and DRAFT
            $query->casesInProgress([1, 2]);
            // Filter the OPEN thread
            $query->threadOpen();
            // Filter specific case
            $query->case($caseNumber);
            // Filter specific index
            if ($index > 0) {
                $query->index($index);
            }
            //  Filter specific user
            if (!empty($userUid)) {
                $userId = !empty($userUid) ? User::getId($userUid) : 0;
                $query->userId($userId);
            }
            $results = $query->get();
            $arrayData = $results->values()->toArray();

            if (!empty($arrayData)) {
                $arrayData = $this->getStatusInfoFormatted($arrayData);
                return $arrayData;
            }

            // Status is CANCELLED, COMPLETED
            $query = Delegation::query()->select([
                'APPLICATION.APP_STATUS',
                'APP_DELEGATION.APP_NUMBER',
                'APP_DELEGATION.DEL_INDEX',
                'APP_DELEGATION.PRO_UID'
            ]);
            $query->joinApplication();
            // Filter the status COMPLETED and CANCELLED
            $query->casesDone([3, 4]);
            // Filter specific case
            $query->case($caseNumber);
            // Filter specific index
            if ($index > 0)  {
                $query->index($index);
            }
            //  Filter specific user
            if (!empty($userUid)) {
                $userId = !empty($userUid) ? User::getId($userUid) : 0;
                $query->userId($userId);
            }
            $query->lastThread();
            $results = $query->get();
            $arrayData = $results->values()->toArray();
            if (!empty($arrayData)) {
                $arrayData = $this->getStatusInfoFormatted($arrayData);
                return $arrayData;
            }

            // Status is PARTICIPATED
            $arrayData = Delegation::getParticipatedInfo($appUid);
            if (!empty($arrayData)) {
                return $arrayData;
            }

            return $arrayData;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get process list for start case
     *
     * @param string $usrUid id of user
     * @param string $typeView type of view
     *
     * @return array Return an array with process list that the user can start.
     * @throws RestException
     */
    public function getCasesListStarCase($usrUid, $typeView)
    {
        try {
            Validator::usrUid($usrUid, '$usr_uid');

            $case = new ClassesCases();
            $response = $case->getProcessListStartCase($usrUid, $typeView);

            return $response;
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Get process list bookmark for start case
     *
     * @param string $usrUid id of user
     * @param string $typeView type of view
     *
     * @return array Return an array with process list that the user can start.
     * @throws Exception
     */
    public function getCasesListBookmarkStarCase($usrUid, $typeView)
    {
        try {
            Validator::usrUid($usrUid, '$usr_uid');

            $user = new ModelUsers();
            $fields = $user->load($usrUid);
            $bookmark = empty($fields['USR_BOOKMARK_START_CASES']) ? array() : unserialize($fields['USR_BOOKMARK_START_CASES']);

            //Getting group id and adding the user id
            $group = new Groups();
            $groups = $group->getActiveGroupsForAnUser($usrUid);
            $groups[] = $usrUid;

            $c = new Criteria();
            $c->clearSelectColumns();
            $c->addSelectColumn(TaskPeer::TAS_UID);
            $c->addSelectColumn(TaskPeer::TAS_TITLE);
            $c->addSelectColumn(TaskPeer::PRO_UID);
            $c->addSelectColumn(ProcessPeer::PRO_TITLE);
            $c->addJoin(TaskPeer::PRO_UID, ProcessPeer::PRO_UID, Criteria::LEFT_JOIN);
            $c->addJoin(TaskPeer::TAS_UID, TaskUserPeer::TAS_UID, Criteria::LEFT_JOIN);
            $c->add(ProcessPeer::PRO_STATUS, 'ACTIVE');
            $c->add(TaskPeer::TAS_START, 'TRUE');
            $c->add(TaskUserPeer::USR_UID, $groups, Criteria::IN);
            $c->add(TaskPeer::TAS_UID, $bookmark, Criteria::IN);

            if ($typeView == 'category') {
                $c->addAsColumn('PRO_CATEGORY', 'PCS.PRO_CATEGORY');
                $c->addAsColumn('CATEGORY_NAME', 'PCSCAT.CATEGORY_NAME');
                $c->addAlias('PCS', 'PROCESS');
                $c->addAlias('PCSCAT', 'PROCESS_CATEGORY');
                $aConditions = array();
                $aConditions[] = array(TaskPeer::PRO_UID, 'PCS.PRO_UID');
                $c->addJoinMC($aConditions, Criteria::LEFT_JOIN);
                $aConditions = array();
                $aConditions[] = array('PCS.PRO_CATEGORY', 'PCSCAT.CATEGORY_UID');
                $c->addJoinMC($aConditions, Criteria::LEFT_JOIN);
            }
            $c->setDistinct();
            $rs = TaskPeer::doSelectRS($c);

            $rs->setFetchmode(ResultSet::FETCHMODE_ASSOC);
            $processList = array();
            while ($rs->next()) {
                $row = $rs->getRow();
                if ($typeView == 'category') {
                    $processList[] = array(
                        'tas_uid' => $row['TAS_UID'],
                        'pro_title' => $row['PRO_TITLE'] . '(' . $row['TAS_TITLE'] . ')',
                        'pro_uid' => $row['PRO_UID'],
                        'pro_category' => $row['PRO_CATEGORY'],
                        'category_name' => $row['CATEGORY_NAME']
                    );
                } else {
                    $processList[] = array(
                        'tas_uid' => $row['TAS_UID'],
                        'pro_title' => $row['PRO_TITLE'] . '(' . $row['TAS_TITLE'] . ')',
                        'pro_uid' => $row['PRO_UID']
                    );
                }

            }
            if (count($processList) == 0) {
                $processList['success'] = 'failure';
                $processList['message'] = G::LoadTranslation('ID_NOT_HAVE_BOOKMARKED_PROCESSES');
            }

            return $processList;
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Get Users to reassign
     *
     * @param string $userUid Unique id of User (User logged)
     * @param string $taskUid Unique id of Task
     * @param string $appUid Unique id of Application
     *
     * @return array Return Users to reassign
     * @throws Exception
     */
    public function usersToReassign(
        $userUid,
        $taskUid,
        $appUid
    ) {
        $task = Task::where('TAS_UID', '=', $taskUid)->first();
        $type = $task->TAS_ASSIGN_TYPE;
        $variable = $task->TAS_GROUP_VARIABLE;
        $result = [];

        if ($type === 'SELF_SERVICE' && $variable !== '') {
            $variable = substr($variable, 2);
            $fields = ModelApplication::where('APP_UID', '=', $appUid)->first();
            $data = ClassesCases::unserializeData($fields->APP_DATA);

            $row = [];
            
            if (!empty($data[$variable])) {
                foreach ($data[$variable] as $uid) {
                    $group = Groupwf::where('GRP_UID', '=', $uid)->first();
                    if (!empty($group)) {
                        $usersOfGroup = GroupUser::where('GRP_UID', '=', $uid)->get()->toArray();
                        foreach ($usersOfGroup as $data) {
                            $row[] = $data['USR_UID'];
                        }
                    } else {
                        $row[] = $uid;
                    }
                }
            }
            
            $users = [];
            foreach ($row as $data) {
                $obj = User::where('USR_UID', '=', $data)->Active()->first();
                if (!is_null($obj) && $obj->USR_USERNAME !== "") {
                    $users[] = $obj;
                }
            }

            foreach ($users as $user) {
                $result[] = [
                    "USR_UID" => $user->USR_UID,
                    "USR_USERNAME" => $user->USR_USERNAME,
                    "USR_FIRSTNAME" => $user->USR_FIRSTNAME,
                    "USR_LASTNAME"=> $user->USR_LASTNAME
                ];
            }

        } else {
            $result = $this->getUsersToReassign($userUid, $taskUid)['data'];
        }
        return ['data' => $result];
    }

    /**
     * Get Users to reassign
     *
     * @param string $userUid Unique id of User (User logged)
     * @param string $taskUid Unique id of Task
     * @param array $arrayFilterData Data of the filters
     * @param string $sortField Field name to sort
     * @param string $sortDir Direction of sorting (ASC, DESC)
     * @param int $start Start
     * @param int $limit Limit
     *
     * @return array Return Users to reassign
     * @throws Exception
     */
    public function getUsersToReassign(
        $userUid,
        $taskUid,
        $arrayFilterData = null,
        $sortField = null,
        $sortDir = null,
        $start = null,
        $limit = null
    ) {
        try {
            $arrayUser = [];

            $numRecTotal = 0;

            //Set variables
            $task = TaskPeer::retrieveByPK($taskUid);

            $processUid = $task->getProUid();

            $user = new BmUser();
            $task = new ClassesTasks();
            $group = new Groups();

            //Set variables
            $filterName = 'filter';

            if (!is_null($arrayFilterData) && is_array($arrayFilterData) && isset($arrayFilterData['filter'])) {
                $arrayAux = [
                    '' => 'filter',
                    'LEFT' => 'lfilter',
                    'RIGHT' => 'rfilter'
                ];

                $filterName = $arrayAux[(isset($arrayFilterData['filterOption'])) ? $arrayFilterData['filterOption'] : ''];
            }

            //Get data
            if (!is_null($limit) && $limit . '' == '0') {
                //Return
                return [
                    'total' => $numRecTotal,
                    'start' => (int)((!is_null($start)) ? $start : 0),
                    'limit' => (int)((!is_null($limit)) ? $limit : 0),
                    $filterName => (!is_null($arrayFilterData) && is_array($arrayFilterData) && isset($arrayFilterData['filter'])) ? $arrayFilterData['filter'] : '',
                    'data' => $arrayUser
                ];
            }

            //Set variables
            $processSupervisor = new BmProcessSupervisor();

            $arrayResult = $processSupervisor->getProcessSupervisors($processUid, 'ASSIGNED', null, null, null,
                'group');

            $arrayGroupUid = array_merge(
                array_map(function ($value) {
                    return $value['GRP_UID'];
                }, $task->getGroupsOfTask($taskUid, 1)), //Groups
                array_map(function ($value) {
                    return $value['GRP_UID'];
                }, $task->getGroupsOfTask($taskUid, 2)), //AdHoc Groups
                array_map(function ($value) {
                    return $value['grp_uid'];
                }, $arrayResult['data'])                 //ProcessSupervisor Groups
            );

            $sqlTaskUser = '
            SELECT ' . TaskUserPeer::USR_UID . '
            FROM   ' . TaskUserPeer::TABLE_NAME . '
            WHERE  ' . TaskUserPeer::TAS_UID . ' = \'%s\' AND
                   ' . TaskUserPeer::TU_TYPE . ' IN (1, 2) AND
                   ' . TaskUserPeer::TU_RELATION . ' = 1
            ';

            $sqlGroupUser = '
            SELECT ' . GroupUserPeer::USR_UID . '
            FROM   ' . GroupUserPeer::TABLE_NAME . '
            WHERE  ' . GroupUserPeer::GRP_UID . ' IN (%s)
            ';

            $sqlProcessSupervisor = '
            SELECT ' . ProcessUserPeer::USR_UID . '
            FROM   ' . ProcessUserPeer::TABLE_NAME . '
            WHERE  ' . ProcessUserPeer::PRO_UID . ' = \'%s\' AND
                   ' . ProcessUserPeer::PU_TYPE . ' = \'%s\'
            ';

            $sqlUserToReassign = '(' . sprintf($sqlTaskUser, $taskUid) . ')';

            if (!empty($arrayGroupUid)) {
                $sqlUserToReassign .= ' UNION (' . sprintf($sqlGroupUser,
                        '\'' . implode('\', \'', $arrayGroupUid) . '\'') . ')';
            }

            $sqlUserToReassign .= ' UNION (' . sprintf($sqlProcessSupervisor, $processUid, 'SUPERVISOR') . ')';

            //Query
            $criteria = new Criteria('workflow');

            $criteria->addSelectColumn(UsersPeer::USR_UID);
            $criteria->addSelectColumn(UsersPeer::USR_USERNAME);
            $criteria->addSelectColumn(UsersPeer::USR_FIRSTNAME);
            $criteria->addSelectColumn(UsersPeer::USR_LASTNAME);

            $criteria->addAlias('USER_TO_REASSIGN', '(' . $sqlUserToReassign . ')');

            $criteria->addJoin(UsersPeer::USR_UID, 'USER_TO_REASSIGN.USR_UID', Criteria::INNER_JOIN);

            if (!is_null($arrayFilterData) && is_array($arrayFilterData) && isset($arrayFilterData['filter']) && trim($arrayFilterData['filter']) != '') {
                $arraySearch = [
                    '' => '%' . $arrayFilterData['filter'] . '%',
                    'LEFT' => $arrayFilterData['filter'] . '%',
                    'RIGHT' => '%' . $arrayFilterData['filter']
                ];

                $search = $arraySearch[(isset($arrayFilterData['filterOption'])) ? $arrayFilterData['filterOption'] : ''];

                $criteria->add(
                    $criteria->getNewCriterion(UsersPeer::USR_USERNAME, $search, Criteria::LIKE)->addOr(
                        $criteria->getNewCriterion(UsersPeer::USR_FIRSTNAME, $search, Criteria::LIKE))->addOr(
                        $criteria->getNewCriterion(UsersPeer::USR_LASTNAME, $search, Criteria::LIKE))
                );
            }

            $criteria->add(UsersPeer::USR_STATUS, 'ACTIVE', Criteria::EQUAL);

            if (!$user->checkPermission($userUid, 'PM_SUPERVISOR')) {
                $criteria->add(UsersPeer::USR_UID, $userUid, Criteria::NOT_EQUAL);
            }

            //Number records total
            $numRecTotal = UsersPeer::doCount($criteria);

            //Query
            $conf = new Configurations();
            $sortFieldDefault = UsersPeer::TABLE_NAME . '.' . $conf->userNameFormatGetFirstFieldByUsersTable();

            if (!is_null($sortField) && trim($sortField) != '') {
                $sortField = strtoupper($sortField);

                if (in_array(UsersPeer::TABLE_NAME . '.' . $sortField, $criteria->getSelectColumns())) {
                    $sortField = UsersPeer::TABLE_NAME . '.' . $sortField;
                } else {
                    $sortField = $sortFieldDefault;
                }
            } else {
                $sortField = $sortFieldDefault;
            }

            if (!is_null($sortDir) && trim($sortDir) != '' && strtoupper($sortDir) == 'DESC') {
                $criteria->addDescendingOrderByColumn($sortField);
            } else {
                $criteria->addAscendingOrderByColumn($sortField);
            }

            if (!is_null($start)) {
                $criteria->setOffset((int)($start));
            }

            if (!is_null($limit)) {
                $criteria->setLimit((int)($limit));
            }

            $rsCriteria = UsersPeer::doSelectRS($criteria);
            $rsCriteria->setFetchmode(ResultSet::FETCHMODE_ASSOC);

            while ($rsCriteria->next()) {
                $row = $rsCriteria->getRow();

                $arrayUser[] = $row;
            }

            //Return
            return [
                'total' => $numRecTotal,
                'start' => (int)((!is_null($start)) ? $start : 0),
                'limit' => (int)((!is_null($limit)) ? $limit : 0),
                $filterName => (!is_null($arrayFilterData) && is_array($arrayFilterData) && isset($arrayFilterData['filter'])) ? $arrayFilterData['filter'] : '',
                'data' => $arrayUser
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Batch reassign
     *
     * @param array $data
     *
     * @return json Return an json with the result of the reassigned cases.
     */

    public function doPostReassign($data)
    {
        if (!is_array($data)) {
            $isJson = is_string($data) && is_array(G::json_decode($data, true)) ? true : false;
            if ($isJson) {
                $data = G::json_decode($data, true);
            } else {
                return;
            }
        }
        $dataResponse = $data;
        $casesToReassign = $data['cases'];
        $oCases = new ClassesCases();
        foreach ($casesToReassign as $key => $val) {
            $appDelegation = AppDelegationPeer::retrieveByPK($val['APP_UID'], $val['DEL_INDEX']);
            $existDelegation = $this->validateReassignData($appDelegation, $val, $data, 'DELEGATION_NOT_EXISTS');
            if ($existDelegation) {
                $existDelegation = $this->validateReassignData($appDelegation, $val, $data,
                    'USER_NOT_ASSIGNED_TO_TASK');
                if ($existDelegation) {
                    $usrUid = '';
                    if (array_key_exists('USR_UID', $val)) {
                        if ($val['USR_UID'] != '') {
                            $usrUid = $val['USR_UID'];
                        }
                    }
                    if ($usrUid == '') {
                        $fields = $appDelegation->toArray(BasePeer::TYPE_FIELDNAME);
                        $usrUid = $fields['USR_UID'];
                    }
                    //Will be not able reassign a case when is paused
                    $flagPaused = $this->validateReassignData($appDelegation, $val, $data,
                        'ID_REASSIGNMENT_PAUSED_ERROR');
                    //Current users of OPEN DEL_INDEX thread
                    $flagSameUser = $this->validateReassignData($appDelegation, $val, $data,
                        'REASSIGNMENT_TO_THE_SAME_USER');
                    //reassign case
                    if ($flagPaused && $flagSameUser) {
                        $reassigned = $oCases->reassignCase($val['APP_UID'], $val['DEL_INDEX'], $usrUid,
                            $data['usr_uid_target']);
                        $result = $reassigned ? 1 : 0;
                        $this->messageResponse = [
                            'APP_UID' => $val['APP_UID'],
                            'DEL_INDEX' => $val['DEL_INDEX'],
                            'RESULT' => $result,
                            'STATUS' => 'SUCCESS'
                        ];
                    }
                }
            }
            $dataResponse['cases'][$key] = $this->messageResponse;
        }
        unset($dataResponse['usr_uid_target']);

        return G::json_encode($dataResponse);
    }

    /**
     * @param $appDelegation
     * @param $value
     * @param $data
     * @param string $type
     *
     * @return bool
     */
    private function validateReassignData($appDelegation, $value, $data, $type = 'DELEGATION_NOT_EXISTS')
    {
        $return = true;
        switch ($type) {
            case 'DELEGATION_NOT_EXISTS':
                if (is_null($appDelegation)) {
                    $this->messageResponse = [
                        'APP_UID' => $value['APP_UID'],
                        'DEL_INDEX' => $value['DEL_INDEX'],
                        'RESULT' => 0,
                        'STATUS' => $type
                    ];
                    $return = false;
                }
                break;
            case 'USER_NOT_ASSIGNED_TO_TASK':
                $task = new BmTask();
                $supervisor = new BmProcessSupervisor();
                $taskUid = $appDelegation->getTasUid();
                $flagBoolean = $task->checkUserOrGroupAssignedTask($taskUid, $data['usr_uid_target']);
                $flagps = $supervisor->isUserProcessSupervisor($appDelegation->getProUid(), $data['usr_uid_target']);

                if (!$flagBoolean && !$flagps) {
                    $this->messageResponse = [
                        'APP_UID' => $value['APP_UID'],
                        'DEL_INDEX' => $value['DEL_INDEX'],
                        'RESULT' => 0,
                        'STATUS' => 'USER_NOT_ASSIGNED_TO_TASK'
                    ];
                    $return = false;
                }
                break;
            case 'ID_REASSIGNMENT_PAUSED_ERROR':
                if (AppDelay::isPaused($value['APP_UID'], $value['DEL_INDEX'])) {
                    $this->messageResponse = [
                        'APP_UID' => $value['APP_UID'],
                        'DEL_INDEX' => $value['DEL_INDEX'],
                        'RESULT' => 0,
                        'STATUS' => G::LoadTranslation('ID_REASSIGNMENT_PAUSED_ERROR')
                    ];
                    $return = false;
                }
                break;
            case 'REASSIGNMENT_TO_THE_SAME_USER':
                $aCurUser = $appDelegation->getCurrentUsers($value['APP_UID'], $value['DEL_INDEX']);
                if (!empty($aCurUser)) {
                    foreach ($aCurUser as $keyAux => $val) {
                        if ($val === $data['usr_uid_target']) {
                            $this->messageResponse = [
                                'APP_UID' => $value['APP_UID'],
                                'DEL_INDEX' => $value['DEL_INDEX'],
                                'RESULT' => 1,
                                'STATUS' => 'SUCCESS'
                            ];
                            $return = false;
                        }
                    }
                } else {
                    //DEL_INDEX is CLOSED
                    $this->messageResponse = [
                        'APP_UID' => $value['APP_UID'],
                        'DEL_INDEX' => $value['DEL_INDEX'],
                        'RESULT' => 0,
                        'STATUS' => G::LoadTranslation('ID_REASSIGNMENT_ERROR')
                    ];
                    $return = false;
                }
                break;
        }

        return $return;
    }

    /**
     * If case already routed
     *
     * @param string $app_uid
     * @param string $del_index
     * @param string $usr_uid
     *
     * @return boolean
     */
    public function caseAlreadyRouted($app_uid, $del_index, $usr_uid = '')
    {
        $c = new Criteria('workflow');
        $c->add(AppDelegationPeer::APP_UID, $app_uid);
        $c->add(AppDelegationPeer::DEL_INDEX, $del_index);
        if (!empty($usr_uid)) {
            $c->add(AppDelegationPeer::USR_UID, $usr_uid);
        }
        $c->add(AppDelegationPeer::DEL_FINISH_DATE, null, Criteria::ISNULL);

        return !(boolean)AppDelegationPeer::doCount($c);
    }

    /**
     * This function review if the user has processPermissions or the user is supervisor
     *
     * @param string $userUid
     * @param string $applicationUid
     * @param string $dynaformUid
     *
     * @return boolean
    */
    public function checkUserHasPermissionsOrSupervisor($userUid, $applicationUid, $dynaformUid)
    {
        $arrayApplicationData = $this->getApplicationRecordByPk($applicationUid, [], false);
        //Get all access for the user, we no consider the permissions
        $userCanAccess = $this->userAuthorization(
            $userUid,
            $arrayApplicationData['PRO_UID'],
            $applicationUid,
            [],
            [],
            true
        );

        //We need to get all the object permission consider the BLOCK
        $case = new ClassesCases();
        $allObjectPermissions = $case->getAllObjects($arrayApplicationData['PRO_UID'], $applicationUid, '', $userUid);

        //Check case tracker
        $flagCaseTracker = $case->getAllObjectsTrackerDynaform($arrayApplicationData['PRO_UID'], $dynaformUid);

        //Review if the user has participated in the case
        //Review if the user is supervisor in the case and if had assign the objectSupervisor
        //Review if the user has process permission SUMMARY FORM
        //Review if the user has process permission DYNAFORM for the specific form
        //Review if the form is configured for case tracker
        return (
            $userCanAccess['participated']
            || ($userCanAccess['supervisor'] && in_array($dynaformUid, $userCanAccess['objectSupervisor']))
            || $allObjectPermissions['SUMMARY_FORM']
            || in_array($dynaformUid, $allObjectPermissions['DYNAFORMS'])
            || $flagCaseTracker
        );
    }

    /**
     * Delete MultipleFile in Case data
     *
     * @param array $arrayApplicationData Case data
     * @param string $variable1 Variable1
     * @param string $variable2 Variable2
     * @param string $type Type (NORMAL, GRID)
     * @param array $arrayDocumentToDelete Document to delete
     *
     * @return array Returns array with Case data updated
     */
    private function applicationDataDeleteMultipleFile(
        array $arrayApplicationData,
        $variable1,
        $variable2,
        $type,
        array $arrayDocumentToDelete
    ) {
        if (array_key_exists($variable1, $arrayApplicationData) &&
            is_array($arrayApplicationData[$variable1]) && !empty($arrayApplicationData[$variable1])
        ) {
            switch ($type) {
                case 'NORMAL':
                    $arrayAux = $arrayApplicationData[$variable1];
                    $arrayApplicationData[$variable1] = [];
                    $keyd = null;

                    foreach ($arrayAux as $key => $value) {
                        if ($value['appDocUid'] == $arrayDocumentToDelete['appDocUid'] &&
                            (int)($value['version']) == (int)($arrayDocumentToDelete['version'])
                        ) {
                            $keyd = $key;
                        } else {
                            $arrayApplicationData[$variable1][] = $value;
                        }
                    }

                    if (!is_null($keyd)) {
                        $variable1 = $variable1 . '_label';

                        if (array_key_exists($variable1, $arrayApplicationData) &&
                            is_array($arrayApplicationData[$variable1]) && !empty($arrayApplicationData[$variable1])
                        ) {
                            $arrayAux = $arrayApplicationData[$variable1];
                            $arrayApplicationData[$variable1] = [];

                            foreach ($arrayAux as $key => $value) {
                                if ($key != $keyd) {
                                    $arrayApplicationData[$variable1][] = $value;
                                }
                            }
                        }
                    }
                    break;
                case 'GRID':
                    foreach ($arrayApplicationData[$variable1] as $key => $value) {
                        if (array_key_exists($variable2, $value)) {
                            $arrayApplicationData[$variable1][$key] = $this->applicationDataDeleteMultipleFile(
                                $value, $variable2, null, 'NORMAL', $arrayDocumentToDelete
                            );
                        }
                    }
                    break;
            }
        }

        //Return
        return $arrayApplicationData;
    }

    /**
     * Delete MultipleFile
     *
     * @param string $applicationUid Unique id of Case
     * @param array $arrayVariableDocumentToDelete Variable with Documents to delete
     *
     * @return void
     */
    public function deleteMultipleFile($applicationUid, array $arrayVariableDocumentToDelete)
    {
        $case = new ClassesCases();
        $appDocument = new AppDocument();

        $arrayApplicationData = $this->getApplicationRecordByPk($applicationUid, [], false);
        $arrayApplicationData['APP_DATA'] = $case->unserializeData($arrayApplicationData['APP_DATA']);
        $flagDelete = false;
        foreach ($arrayVariableDocumentToDelete as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $type = '';

                $arrayAux = $value;
                $arrayAux = array_shift($arrayAux);

                if (array_key_exists('appDocUid', $arrayAux)) {
                    $type = 'NORMAL';
                } else {
                    $arrayAux = array_shift($arrayAux);
                    $arrayAux = array_shift($arrayAux);

                    if (array_key_exists('appDocUid', $arrayAux)) {
                        $type = 'GRID';
                    }
                }

                switch ($type) {
                    case 'NORMAL':
                        $variable = $key;
                        $arrayDocumentDelete = $value;

                        foreach ($arrayDocumentDelete as $value2) {
                            if ($value2['appDocUid'] !== "") {
                                $appDocument->remove($value2['appDocUid'], (int)($value2['version']));

                                $arrayApplicationData['APP_DATA'] = $this->applicationDataDeleteMultipleFile(
                                    $arrayApplicationData['APP_DATA'], $variable, null, $type, $value2
                                );

                                $flagDelete = true;
                            }
                        }
                        break;
                    case 'GRID':
                        $grid = $key;

                        foreach ($value as $value2) {
                            foreach ($value2 as $key3 => $value3) {
                                $variable = $key3;
                                $arrayDocumentDelete = $value3;

                                foreach ($arrayDocumentDelete as $value4) {
                                    if ($value4['appDocUid'] !== "") {
                                        $appDocument->remove($value4['appDocUid'], (int)($value4['version']));

                                        $arrayApplicationData['APP_DATA'] = $this->applicationDataDeleteMultipleFile(
                                            $arrayApplicationData['APP_DATA'], $grid, $variable, $type, $value4
                                        );

                                        $flagDelete = true;
                                    }
                                }
                            }
                        }
                        break;
                }
            }
        }

        //Delete simple files.
        //The observations suggested by 'pull request' approver are applied (please see pull request).
        foreach ($arrayVariableDocumentToDelete as $key => $value) {
            if (isset($value['appDocUid'])) {
                $appDocument->remove($value['appDocUid'], (int)(isset($value['version']) ? $value['version'] : 1));
                if (is_string($arrayApplicationData['APP_DATA'][$key])) {
                    try {
                        $files = G::json_decode($arrayApplicationData['APP_DATA'][$key]);
                        foreach ($files as $keyFile => $valueFile) {
                            if ($valueFile === $value['appDocUid']) {
                                unset($files[$keyFile]);
                            }
                        }
                        $arrayApplicationData['APP_DATA'][$key] = G::json_encode($files);
                    } catch (Exception $e) {
                        $message = $e->getMessage();
                        $context = $value;
                        Log::channel(':DeleteFile')->error($message, Bootstrap::context($context));
                    }
                }
                $flagDelete = true;
            }
        }

        if ($flagDelete) {
            $result = $case->updateCase($applicationUid, $arrayApplicationData);
        }
    }

    /**
     * Get Permissions, Participate, Access, Objects supervisor
     *
     * @param string $usrUid
     * @param string $proUid
     * @param string $appUid
     * @param array $rolesPermissions, the roles that we need to review
     * @param array $objectPermissions, the permissions that we need to review
     * @param boolean $objectSupervisor, if we need to get all the objects supervisor
     * @param string $tasUid
     *
     * @return array
     */
    public function userAuthorization(
        $usrUid,
        $proUid,
        $appUid,
        $rolesPermissions = [],
        $objectPermissions = [],
        $objectSupervisor = false,
        $tasUid = ''
    ) {
        $arrayAccess = [];

        // User has participated
        $arrayAccess['participated'] = Delegation::participation($appUid, $usrUid);

        // User is supervisor
        $supervisor = new BmProcessSupervisor();
        $isSupervisor = $supervisor->isUserProcessSupervisor($proUid, $usrUid);
        $arrayAccess['supervisor'] = ($isSupervisor) ? true : false;

        // If the user is supervisor we will to return the object assigned
        if ($isSupervisor && $objectSupervisor) {
            $ps = new BmProcessSupervisor();
            $arrayAccess['objectSupervisor']  = $ps->getObjectSupervisor($proUid);
        }

        // Roles Permissions
        if (count($rolesPermissions) > 0) {
            global $RBAC;
            foreach ($rolesPermissions as $value) {
                $arrayAccess['rolesPermissions'][$value] = ($RBAC->userCanAccess($value) < 0) ? false : true;
            }
        }

        // Object Permissions
        if (count($objectPermissions) > 0) {
            $case = new ClassesCases();
            foreach ($objectPermissions as $key => $value) {
                $resPermission = $case->getAllObjectsFrom($proUid, $appUid, $tasUid, $usrUid, $value);
                if (isset($resPermission[$key])) {
                    $arrayAccess['objectPermissions'][$key] = $resPermission[$key];
                }
            }
        }

        return $arrayAccess;
    }


    /**
     * Get Global System Variables
     * @param array $appData
     * @param array $dataVariable
     *
     * @return array
     */
    public static function getGlobalVariables($appData = array(), $dataVariable = array())
    {
        $appData = array_change_key_case($appData, CASE_UPPER);
        $dataVariable = array_change_key_case($dataVariable, CASE_UPPER);

        $result = [];
        //we get the appData parameters
        if (!empty($appData['APPLICATION'])) {
            $result['APPLICATION'] = $appData['APPLICATION'];
        }
        if (!empty($appData['PROCESS'])) {
            $result['PROCESS'] = $appData['PROCESS'];
        }
        if (!empty($appData['TASK'])) {
            $result['TASK'] = $appData['TASK'];
        }
        if (!empty($appData['INDEX'])) {
            $result['INDEX'] = $appData['INDEX'];
        }

        //we try to get the missing elements
        if (!empty($dataVariable['APP_UID']) && empty($result['APPLICATION'])) {
            $result['APPLICATION'] = $dataVariable['APP_UID'];
        }
        if (!empty($dataVariable['PRO_UID']) && empty($result['PROCESS'])) {
            $result['PROCESS'] = $dataVariable['PRO_UID'];
        }

        $result['USER_LOGGED'] = '';
        $result['USR_USERNAME'] = '';
        global $RBAC;
        if (isset($RBAC) && isset($RBAC->aUserInfo)) {
            $result['USER_LOGGED'] = isset($RBAC->aUserInfo['USER_INFO']['USR_UID']) ? $RBAC->aUserInfo['USER_INFO']['USR_UID'] : '';
            $result['USR_USERNAME'] = isset($RBAC->aUserInfo['USER_INFO']['USR_USERNAME']) ? $RBAC->aUserInfo['USER_INFO']['USR_USERNAME'] : '';
        }
        if (empty($result['USER_LOGGED'])) {
            $result['USER_LOGGED'] = Server::getUserId();
            if (!empty($result['USER_LOGGED'])) {
                $oUserLogged = new ModelUsers();
                $oUserLogged->load($result['USER_LOGGED']);
                $result['USR_USERNAME'] = $oUserLogged->getUsrUsername();
            }
        }

        //the parameter dataVariable may contain additional elements
        $result = array_merge($dataVariable, $result);

        return $result;
    }

    /**
     * Get index last participation from a user
     *
     * This function return the last participation
     * by default is not considered the status OPEN or CLOSED
     * in parallel cases return the first to find
     * @param string $appUid
     * @param string $userUid
     * @param string $threadStatus
     *
     * @return integer delIndex
     */
    public function getLastParticipatedByUser($appUid, $userUid, $threadStatus = '')
    {
        $criteria = new Criteria('workflow');
        $criteria->addSelectColumn(AppDelegationPeer::DEL_INDEX);
        $criteria->addSelectColumn(AppDelegationPeer::DEL_THREAD_STATUS);
        $criteria->add(AppDelegationPeer::APP_UID, $appUid, Criteria::EQUAL);
        $criteria->add(AppDelegationPeer::USR_UID, $userUid, Criteria::EQUAL);
        if (!empty($threadStatus)) {
            $criteria->add(AppDelegationPeer::DEL_THREAD_STATUS, $threadStatus, Criteria::EQUAL);
        }
        $dataSet = AppDelegationPeer::doSelectRS($criteria);
        $dataSet->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $dataSet->next();
        $row = $dataSet->getRow();

        return isset($row['DEL_INDEX']) ? $row['DEL_INDEX'] : 0;
    }

    /**
     * Get last index, we can considering the pause thread
     *
     * This function return the last index thread and will be considered the paused cases
     * Is created by Jump to and redirect the correct thread
     * by default is not considered the paused thread
     * in parallel cases return the first thread to find
     * @param string $appUid
     * @param boolean $checkCaseIsPaused
     *
     * @return integer delIndex
     */
    public function getOneLastThread($appUid, $checkCaseIsPaused = false)
    {
        $criteria = new Criteria('workflow');
        $criteria->addSelectColumn(AppDelegationPeer::DEL_INDEX);
        $criteria->addSelectColumn(AppDelegationPeer::DEL_THREAD_STATUS);
        $criteria->add(AppDelegationPeer::APP_UID, $appUid, Criteria::EQUAL);
        $dataSet = AppDelegationPeer::doSelectRS($criteria);
        $dataSet->setFetchmode(ResultSet::FETCHMODE_ASSOC);
        $dataSet->next();
        $row = $dataSet->getRow();
        $delIndex = 0;
        while (is_array($row)) {
            $delIndex = $row['DEL_INDEX'];
            if ($checkCaseIsPaused && AppDelay::isPaused($appUid, $delIndex)) {
                return $delIndex;
            }
            $dataSet->next();
            $row = $dataSet->getRow();
        }

        return $delIndex;
    }

    /**
     * This function will be return the criteria for the search filter
     *
     * We considered in the search criteria the custom cases list,
     * the titles related to: caseTitle taskTitle processTitle and
     * the case number
     * @param Criteria $criteria , must be contain the initial criteria for search
     * @param string $listPeer , name of the list class
     * @param string $search , the parameter for search in the table
     * @param string $additionalClassName , name of the className of pmtable
     * @param array $additionalColumns , columns related to the custom cases list ex: TABLE_NAME.COLUMN_NAME
     *
     * @throws PropelException
     */
    public function getSearchCriteriaListCases(
        &$criteria,
        $listPeer,
        $search,
        $additionalClassName = '',
        $additionalColumns = []
    ) {
        $tmpCriteria = '';
        //If we have additional tables configured in the custom cases list, prepare the variables for search
        if (count($additionalColumns) > 0) {
            require_once(PATH_DATA_SITE . 'classes' . PATH_SEP . $additionalClassName . '.php');

            $columnPivot = current($additionalColumns);
            $tableAndColumn = explode(".", $columnPivot);
            $type = PmTable::getTypeOfColumn($listPeer, $tableAndColumn[0], $tableAndColumn[1]);
            $tmpCriteria = $this->defineCriteriaByColumnType($type, $columnPivot, $search);

            //We prepare the query related to the custom cases list
            foreach (array_slice($additionalColumns, 1) as $column) {
                $tableAndColumn = explode(".", $column);
                $type = PmTable::getTypeOfColumn($listPeer, $tableAndColumn[0], $tableAndColumn[1]);
                $tmpCriteria = $this->defineCriteriaByColumnType($type, $column, $search)->addOr($tmpCriteria);

            }
        }

        if (!empty($tmpCriteria)) {
            $criteria->add(
                $criteria->getNewCriterion($listPeer::APP_TITLE, '%' . $search . '%', Criteria::LIKE)->addOr(
                    $criteria->getNewCriterion($listPeer::APP_TAS_TITLE, '%' . $search . '%', Criteria::LIKE)->addOr(
                        $criteria->getNewCriterion($listPeer::APP_PRO_TITLE, '%' . $search . '%',
                            Criteria::LIKE)->addOr(
                            $criteria->getNewCriterion($listPeer::APP_NUMBER, $search, Criteria::EQUAL)->addOr(
                                $tmpCriteria
                            ))))
            );
        } else {
            $criteria->add(
                $criteria->getNewCriterion($listPeer::APP_TITLE, '%' . $search . '%', Criteria::LIKE)->addOr(
                    $criteria->getNewCriterion($listPeer::APP_TAS_TITLE, '%' . $search . '%', Criteria::LIKE)->addOr(
                        $criteria->getNewCriterion($listPeer::APP_PRO_TITLE, '%' . $search . '%',
                            Criteria::LIKE)->addOr(
                            $criteria->getNewCriterion($listPeer::APP_NUMBER, $search, Criteria::EQUAL))))
            );
        }
    }

    /**
     * Define the criteria according to the column type
     *
     * @param string $fieldType
     * @param string $column
     * @param string $search
     *
     * @return Criteria
     */
    private function defineCriteriaByColumnType($fieldType, $column, $search)
    {
        $newCriteria = new Criteria("workflow");

        switch ($fieldType) {
            case CreoleTypes::BOOLEAN:
                $criteria = $newCriteria->getNewCriterion($column, $search, Criteria::EQUAL);
                break;
            case CreoleTypes::BIGINT:
            case CreoleTypes::INTEGER:
            case CreoleTypes::SMALLINT:
            case CreoleTypes::TINYINT:
                $criteria = $newCriteria->getNewCriterion($column, $search, Criteria::EQUAL);
                break;
            case CreoleTypes::REAL:
            case CreoleTypes::DECIMAL:
            case CreoleTypes::DOUBLE:
            case CreoleTypes::FLOAT:
                $criteria = $newCriteria->getNewCriterion($column, $search, Criteria::LIKE);
                break;
            case CreoleTypes::CHAR:
            case CreoleTypes::LONGVARCHAR:
            case CreoleTypes::VARCHAR:
                $criteria = $newCriteria->getNewCriterion($column, "%" . $search . "%", Criteria::LIKE);
                break;
            case CreoleTypes::DATE:
            case CreoleTypes::TIME:
            case CreoleTypes::TIMESTAMP://DATETIME
                //@todo use the same constant in other places
                if (preg_match(UtilDateTime::REGEX_IS_DATE,
                    $search, $arrayMatch)) {
                    $criteria = $newCriteria->getNewCriterion($column, $search, Criteria::GREATER_EQUAL);
                } else {
                    $criteria = $newCriteria->getNewCriterion($column, $search, Criteria::EQUAL);
                }
                break;
            default:
                $criteria = $newCriteria->getNewCriterion($column, $search, Criteria::EQUAL);
        }

        return $criteria;
    }

    /**
     * This function get the table.column by order by the result
     * We can include the additional table related to the custom cases list
     *
     * @param string $listPeer, name of the list class
     * @param string $field, name of the fieldName
     * @param string $sort, name of column by sort
     * @param string $defaultSort, name of column by sort default
     * @param string $additionalClassName, name of the className of pmTable
     * @param array $additionalColumns, columns related to the custom cases list with the format TABLE_NAME.COLUMN_NAME
     * @param string $userDisplayFormat, user information display format
     *
     * @return string|array could be an string $tableName, could be an array $columnSort
     */
    public function getSortColumn(
        $listPeer,
        $field,
        $sort,
        $defaultSort,
        $additionalClassName = '',
        $additionalColumns = array(),
        $userDisplayFormat = ''
    ) {
        $columnSort = $defaultSort;
        $tableName = '';

        //We will check if the column by sort is a LIST table
        $columnsList = $listPeer::getFieldNames($field);
        if (in_array($sort, $columnsList)) {
            switch ($sort) {
                case 'DEL_PREVIOUS_USR_UID':
                    $columnSort = $this->buildOrderFieldFormatted($columnsList, $userDisplayFormat, 'DEL_PREVIOUS_');
                    break;
                case 'USR_UID':
                    $columnSort = $this->buildOrderFieldFormatted($columnsList, $userDisplayFormat, 'DEL_CURRENT_');
                    if (empty($columnSort)) {
                        $columnSort = $this->buildOrderFieldFormatted($columnsList, $userDisplayFormat, '', false);
                    }
                    break;
                default:
                    $columnSort  = $listPeer::TABLE_NAME . '.' . $sort;
            }
        } else {
            //We will sort by CUSTOM CASE LIST table
            if (count($additionalColumns) > 0) {
                require_once(PATH_DATA_SITE . 'classes' . PATH_SEP . $additionalClassName . '.php');
                $aTable = explode('.', current($additionalColumns));
                if (count($aTable) > 0) {
                    $tableName = $aTable[0];
                }
            }
            if (in_array($tableName . '.' . $sort, $additionalColumns)) {
                $columnSort = $tableName . '.' . $sort;
            }
        }

        return $columnSort;
    }

    /**
     * When we order columns related to the user information we need to use the userDisplayFormat
     *
     * @param array $columnsList, the list of columns in the table
     * @param string $format, the user display format
     * @param string $prefix, the initial name of the columns related to the USR_FIRSTNAME USR_LASTNAME USR_USERNAME
     * 
     * @return array $columnSort, columns  by apply the sql command ORDER BY
     */
    public function buildOrderFieldFormatted($columnsList, $format, $prefix = 'DEL_PREVIOUS_', $validate = true)
    {
        $columnSort = [];

        if (!$validate || (in_array($prefix . 'USR_FIRSTNAME', $columnsList) &&
            in_array($prefix . 'USR_LASTNAME', $columnsList) &&
            in_array($prefix . 'USR_USERNAME', $columnsList))
        ) {
            switch ($format) {
                case '@firstName @lastName':
                    array_push($columnSort, $prefix . 'USR_FIRSTNAME');
                    array_push($columnSort, $prefix . 'USR_LASTNAME');
                    break;
                case '@firstName @lastName (@userName)':
                    array_push($columnSort, $prefix . 'USR_FIRSTNAME');
                    array_push($columnSort, $prefix . 'USR_LASTNAME');
                    array_push($columnSort, $prefix . 'USR_USERNAME');
                    break;
                case '@userName':
                    array_push($columnSort, $prefix . 'USR_USERNAME');
                    break;
                case '@userName (@firstName @lastName)':
                    array_push($columnSort, $prefix . 'USR_USERNAME');
                    array_push($columnSort, $prefix . 'USR_FIRSTNAME');
                    array_push($columnSort, $prefix . 'USR_LASTNAME');
                    break;
                case '@lastName, @firstName':
                    array_push($columnSort, $prefix . 'USR_LASTNAME');
                    array_push($columnSort, $prefix . 'USR_FIRSTNAME');
                    break;
                case '@lastName @firstName':
                    array_push($columnSort, $prefix . 'USR_LASTNAME');
                    array_push($columnSort, $prefix . 'USR_FIRSTNAME');
                    break;
                case '@lastName, @firstName (@userName)':
                    array_push($columnSort, $prefix . 'USR_LASTNAME');
                    array_push($columnSort, $prefix . 'USR_FIRSTNAME');
                    array_push($columnSort, $prefix . 'USR_USERNAME');
                    break;
                default:
                    array_push($columnSort, $prefix . 'USR_USERNAME');
                    break;
            }
        }

        return $columnSort;
    }

     /** This function verify if the user is a supervisor
     * If we send the formUid we will to review if has the object form assigned
     *
     * @param string $usrUid, Uid related to the user
     * @param string $appUid, Uid related to the case
     * @param string $formUid, Uid related to the dynaform
     * @param string $proUid, Uid related to the process
     *
     * @return boolean
     */
    public function isSupervisorFromForm($usrUid, $appUid, $formUid, $proUid = '')
    {

        //We will to search the proUid related to the appUid
        if (empty($proUid)) {
            $arrayApplicationData = $this->getApplicationRecordByPk($appUid, [], false);
            $proUid = $arrayApplicationData['PRO_UID'];
        }

        $supervisor = new BmProcessSupervisor();
        $isSupervisor = $supervisor->isUserProcessSupervisor($proUid, $usrUid);

        //We will check if the supervisor has the object form assigned
        if ($isSupervisor) {
            $cases = new ClassesCases();
            $resultDynaForm = $cases->getAllDynaformsStepsToRevise($appUid);
            $isSupervisor = false;
            while ($resultDynaForm->next()) {
                $row = $resultDynaForm->getRow();
                //Review if the supervisor has the form assigned
                if ($row["STEP_UID_OBJ"] === $formUid) {
                    $isSupervisor = true;
                    break;
                }
            }
        }

        return $isSupervisor;
    }

    /**
     * Upload file in the corresponding folder
     *
     * @param string $userUid
     * @param string $appUid
     * @param string $varName
     * @param mixed $inpDocUid
     * @param string $appDocUid
     * @param int $delegationIndex
     *
     * @return array
     * @throws Exception
     */
    public function uploadFiles($userUid, $appUid, $varName, $inpDocUid = -1, $appDocUid = null, $delegationIndex = null)
    {
        $response = [];
        // Review the appUid
        Validator::appUid($appUid, '$appUid');

        if (isset($_FILES["form"]["name"]) && count($_FILES["form"]["name"]) > 0) {
            // Get the delIndex related to the case
            $cases = new ClassesCases();
            if (!empty($delegationIndex)) {
                $delIndex = $delegationIndex;
            } else {
                $delIndex = $cases->getCurrentDelegation($appUid, $userUid);
            }
            // Get information about the user
            $user = new ModelUsers();
            $userCreator = $user->loadDetailed($userUid)['USR_FULLNAME'];
            $i = 0;
            foreach ($_FILES["form"]["name"] as $fieldIndex => $fieldValue) {
                if (!is_array($fieldValue)) {
                    $arrayFileName = [
                        'name' => $_FILES["form"]["name"][$fieldIndex],
                        'tmp_name' => $_FILES["form"]["tmp_name"][$fieldIndex],
                        'error' => $_FILES["form"]["error"][$fieldIndex]
                    ];

                    // We will to review the validation related to the Input document
                    $file = [
                        'filename' => $arrayFileName["name"],
                        'path' => $arrayFileName["tmp_name"]
                    ];
                    $this->canUploadFileRelatedToInput($inpDocUid, $file);

                    // There is no error, the file uploaded with success
                    if ($arrayFileName["error"] === UPLOAD_ERR_OK) {
                        $appDocument = new AppDocument();
                        $objCreated = $appDocument->uploadAppDocument(
                            $appUid,
                            $userUid,
                            $delIndex,
                            $inpDocUid ,
                            $arrayFileName,
                            $varName,
                            $appDocUid
                        );
                        $response[$i] = [
                            'appDocUid' => $objCreated->getAppDocUid(),
                            'docVersion' => $objCreated->getDocVersion(),
                            'appDocFilename' => $objCreated->getAppDocFilename(),
                            'appDocCreateDate' => $objCreated->getAppDocCreateDate(),
                            'appDocType' => $objCreated->getAppDocType(),
                            'appDocIndex' => $objCreated->getAppDocIndex(),
                            'appDocCreateUser' => $userCreator
                        ];

                        $i++;

                        //Plugin Hook PM_UPLOAD_DOCUMENT for upload document
                        $pluginRegistry = PluginRegistry::loadSingleton();

                        // If the hook exists try to execute
                        if ($pluginRegistry->existsTrigger(PM_UPLOAD_DOCUMENT) && class_exists('uploadDocumentData')) {
                            // Get hook details
                            $triggerDetail = $pluginRegistry->getTriggerInfo(PM_UPLOAD_DOCUMENT);

                            // Build path file
                            $info = pathinfo($arrayFileName['name']);
                            $extension = (isset($info['extension'])) ? $info['extension'] : '';
                            $pathCase = G::getPathFromUID($appUid);
                            $pathFile = PATH_DOCUMENT . $pathCase . PATH_SEP . $objCreated->getAppDocUid() . '_' . $objCreated->getDocVersion() . '.' . $extension;

                            // Instance object used by the hook
                            $documentData = new uploadDocumentData($appUid, $userUid, $pathFile, $objCreated->getAppDocFilename(), $objCreated->getAppDocUid(), $objCreated->getDocVersion());

                            // Execute hook
                            try {
                                $uploadReturn = $pluginRegistry->executeTriggers(PM_UPLOAD_DOCUMENT, $documentData);
                            } catch (Exception $error) {
                                // Is expected an exception when the user tries to upload a versioned input document, the file is removed and the error bubbled
                                 Documents::where('APP_DOC_UID', $objCreated->getAppDocUid())->where('DOC_VERSION', $objCreated->getDocVersion())->delete();
                                throw $error;
                            }

                            // If the executions is correct, update the record related to the document
                            if ($uploadReturn) {
                                Documents::where('APP_DOC_UID', $objCreated->getAppDocUid())->update(['APP_DOC_PLUGIN' => $triggerDetail->getNamespace()]);

                                // Remove the file from the server
                                unlink($pathFile);
                            }
                        }
                    } else {
                        throw new UploadException($arrayFileName['error']);
                    }
                }
            }
        } else {
            throw new Exception(G::LoadTranslation('ID_ERROR_UPLOAD_FILE_CONTACT_ADMINISTRATOR'));
        }

        return $response;
    }

    /**
     * Add a case note
     *
     * @param string $appUid
     * @param string $userUid
     * @param string $note
     * @param bool $sendMail
     * @param array $files
     * @param int $appNUmber
     *
     * @see Ajax::cancelCase()
     * @see Ajax::pauseCase()
     * @see Ajax::reassignCase()
     * @see AppProxy::postNote()
     * @see WsBase::addCaseNote()
     * @see Cases::saveCaseNote()
     *
     * @return array
     */
    public function addNote($appUid, $userUid, $note, $sendMail = false, $files = [], $appNumber = 0)
    {
        // Get the appNumber if was not send
        if ($appNumber === 0) {
            $appNumber = ModelApplication::getCaseNumber($appUid);
        }

        // Register the note
        $attributes = [
            "APP_UID" => $appUid,
            "APP_NUMBER" => $appNumber,
            "USR_UID" => $userUid,
            "NOTE_DATE" => date("Y-m-d H:i:s"),
            "NOTE_CONTENT" => $note,
            "NOTE_TYPE" => "USER",
            "NOTE_AVAILABILITY" => "PUBLIC",
            "NOTE_RECIPIENTS" => ""
        ];
        $newNote = Notes::create($attributes);
        // Get the FK
        $noteId = $newNote->NOTE_ID;

        $attachments = [];
        // Register the files related to the note
        if (!empty($files) || !empty($_FILES["filesToUpload"])) {
            $filesResponse = $this->uploadFilesInCaseNotes($userUid, $appUid, $files, $noteId);
            foreach ($filesResponse['attachments'] as $key => $value) {
                $attachments[$key] = [];
                $attachments[$key]['APP_DOC_FILENAME'] = $value['APP_DOC_FILENAME'];
                $attachments[$key]['LINK'] = "../cases/casesShowCaseNotes?a=" . $value["APP_DOC_UID"] . "&v=" . $value["DOC_VERSION"];
            }

        }

        // Send the email
        if ($sendMail) {
            // Get the recipients
            $case = new ClassesCases();
            $p = $case->getUsersParticipatedInCase($appUid, 'ACTIVE');
            $noteRecipientsList = [];

            foreach ($p["array"] as $key => $userParticipated) {
                if ($key != '') {
                    $noteRecipientsList[] = $key;
                }
            }

            $noteRecipients = implode(",", $noteRecipientsList);
            $note = stripslashes($note);

            // Send the notification
            $appNote = new AppNotes();
            $appNote->sendNoteNotification($appUid, $userUid, $note, $noteRecipients, '', 0, $noteId);
        }

        // Prepare the response
        $result = [];
        $result['success'] = 'success';
        $result['message'] = '';
        $result['attachments'] = $attachments;
        $result['attachment_errors'] = [];

        return $result;
    }

    /**
     * Send mail to notify and Add a case note
     *
     * @param string $appUid
     * @param string $userUid
     * @param string $note
     * @param bool $sendMail
     * @param string $toUser
     *
     */
    public function sendMail($appUid, $userUid, $note, $sendMail = false, $toUser = '')
    {
        
        $appNumber = ModelApplication::getCaseNumber($appUid);

        // Register the note
        $attributes = [
            "APP_UID" => $appUid,
            "APP_NUMBER" => $appNumber,
            "USR_UID" => $userUid,
            "NOTE_DATE" => date("Y-m-d H:i:s"),
            "NOTE_CONTENT" => $note,
            "NOTE_TYPE" => "USER",
            "NOTE_AVAILABILITY" => "PUBLIC",
            "NOTE_RECIPIENTS" => ""
        ];
        $newNote = Notes::create($attributes);
        
        // Send the email
        if ($sendMail) {
            // Get the FK
            $noteId = $newNote->NOTE_ID;
            
            $note = G::LoadTranslation('ID_ASSIGN_NOTIFICATION', [$appNumber]) . '<br />' . G::LoadTranslation('ID_REASON') . ': ' . stripslashes($note);

            // Send the notification
            $appNote = new AppNotes();
            $appNote->sendNoteNotification($appUid, $userUid, $note, $toUser, '', 0, $noteId);
        }
    }

    /**
     * Upload file related to the case notes
     *
     * @param string $userUid
     * @param string $appUid
     * @param array $filesReferences
     * @param int $noteId
     *
     * @return array
     * @throws Exception
     */
    public function uploadFilesInCaseNotes($userUid, $appUid, $filesReferences = [], $noteId = 0)
    {
        $files = [];
        if (!empty($_FILES["filesToUpload"])) {
            $upload = true;
            // This format is from ext-js multipart
            $filesName = !empty($_FILES["filesToUpload"]["name"]) ? $_FILES["filesToUpload"]["name"] : [];
            $filesTmpName = !empty($_FILES["filesToUpload"]["tmp_name"]) ? $_FILES["filesToUpload"]["tmp_name"] : [];
            $filesError = !empty($_FILES["filesToUpload"]["error"]) ? $_FILES["filesToUpload"]["error"] : [];

            foreach ($filesName as $index => $value) {
                if (!empty($value)) {
                    $files[] = [
                        'name' => $filesName[$index],
                        'tmp_name' => $filesTmpName[$index],
                        'error' => $filesError[$index]
                    ];
                }
            }
        } elseif (!empty($filesReferences)) {
            $upload = false;
            // Array with path references
            foreach ($filesReferences as $fileIndex => $fileName) {
                $nameFile = !is_numeric($fileIndex) ? basename($fileIndex) : basename($fileName);
                $files[] = [
                    'name' => $nameFile,
                    'tmp_name' => $fileName,
                    'error' => UPLOAD_ERR_OK
                ];
            }
        }

        // Rules validation
        foreach ($files as $key => $value) {
            $entry = [
                "filename" => $value['name'],
                "path" => $value['tmp_name']
            ];
            $validator = ValidationUploadedFiles::getValidationUploadedFiles()
                    ->runRulesForPostFilesOfNote($entry);
            if ($validator->fails()) {
                Notes::where('NOTE_ID', '=', $noteId)->delete();
                $messageError = G::LoadTranslation('ID_THE_FILE_COULDNT_BE_UPLOADED');
                throw new CaseNoteUploadFile($messageError . ' ' . $validator->getMessage());
            }
        }

        // Get the delIndex related to the case
        $cases = new ClassesCases();
        $delIndex = $cases->getCurrentDelegation($appUid);

        // We will to register the files in the database
        $response = [];
        $response['attachments'] = [];
        $response['attachment_errors'] = [];
        if (!empty($files)) {
            $i = 0;
            $j = 0;
            foreach ($files as $fileIndex => $fileName) {
                // There is no error, the file uploaded with success
                if ($fileName["error"] === UPLOAD_ERR_OK) {
                    $appDocUid = G::generateUniqueID();

                    // Upload or move the file
                    $pathFile = saveAppDocument($fileName, $appUid, $appDocUid, 1, $upload);

                    // If the file was uploaded correctly we will to register in the DB
                    if (!empty($pathFile)) {
                        $attributes = [
                            "DOC_ID" => $noteId,
                            "APP_DOC_UID" => $appDocUid,
                            "DOC_VERSION" => 1,
                            "APP_UID" => $appUid,
                            "DEL_INDEX" => $delIndex,
                            "USR_UID" => $userUid,
                            "DOC_UID" => -1,
                            "APP_DOC_TYPE" => 'CASE_NOTE',
                            "APP_DOC_CREATE_DATE" => date("Y-m-d H:i:s"),
                            "APP_DOC_FILENAME" => $fileName["name"]
                        ];
                        Documents::create($attributes);

                        // List of files uploaded or copy
                        $response['attachments'][$i++] = $attributes;

                        //Plugin Hook PM_UPLOAD_DOCUMENT for upload document
                        $pluginRegistry = PluginRegistry::loadSingleton();

                        // If the hook exists try to execute
                        if ($pluginRegistry->existsTrigger(PM_UPLOAD_DOCUMENT) && class_exists('uploadDocumentData')) {
                            // Get hook details
                            $triggerDetail = $pluginRegistry->getTriggerInfo(PM_UPLOAD_DOCUMENT);

                            // Instance object used by the hook
                            $documentData = new uploadDocumentData($appUid, $userUid, $pathFile, $attributes['APP_DOC_FILENAME'], $appDocUid, 1);

                            // Execute hook
                            $uploadReturn = $pluginRegistry->executeTriggers(PM_UPLOAD_DOCUMENT, $documentData);

                            // If the executions is correct, update the record related to the document
                            if ($uploadReturn) {
                                Documents::where('APP_DOC_UID', $appDocUid)->update(['APP_DOC_PLUGIN' => $triggerDetail->getNamespace()]);

                                // Remove the file from the server
                                unlink($pathFile);
                            }
                        }
                    } else {
                        $response['attachment_errors'][$j++] = [
                            'error' => 'error',
                            'file' => $fileName["name"]
                        ];
                    }
                } else {
                    throw new UploadException($fileName['error']);
                }
            }
        }

        return $response;
    }

    /**
     * Run the validations related to an Input Document
     *
     * @param array $file
     * @param mixed $inpDocUid
     *
     * @return boolean
     * @throws ExceptionRestApi
    */
    private function canUploadFileRelatedToInput($file, $inpDocUid = -1)
    {
        if ($inpDocUid !== -1) {
            $inputDocument = new InputDocument();
            $inputExist = $inputDocument->InputExists($inpDocUid);
            if ($inputExist) {
                $inputProperties = $inputDocument->load($inpDocUid);
                $inpDocTypeFile = $inputProperties['INP_DOC_TYPE_FILE'];
                $inpDocMaxFileSize = (int)$inputProperties["INP_DOC_MAX_FILESIZE"];
                $inpDocMaxFileSizeUnit = $inputProperties["INP_DOC_MAX_FILESIZE_UNIT"];

                $validator = new FileValidator();
                // Rule: extension
                $validator->addRule()
                    ->validate($file, function ($file) use ($inpDocTypeFile) {
                        $result = G::verifyInputDocExtension($inpDocTypeFile, $file->filename, $file->path);

                        return $result->status === false;
                    })
                    ->status(415)
                    ->message(G::LoadTranslation('ID_UPLOAD_INVALID_DOC_TYPE_FILE', [$inpDocTypeFile]))
                    ->log(function ($rule) {
                        $message = $rule->getMessage();
                        $context = [
                            'filename' => $rule->getData()->filename,
                            'url' => $_SERVER["REQUEST_URI"] ?? ''
                        ];
                        Log::channel(':phpUpload')->notice($message, Bootstrap::context($context));
                    });
                // Rule: maximum file size
                $validator->addRule()
                    ->validate($file, function ($file) use ($inpDocMaxFileSize, $inpDocMaxFileSizeUnit) {
                        if ($inpDocMaxFileSize > 0) {
                            $totalMaxFileSize = $inpDocMaxFileSize * ($inpDocMaxFileSizeUnit == self::UNIT_MB ? self::MB_TO_KB * self::MB_TO_KB : self::MB_TO_KB);
                            $fileSize = filesize($file->path);
                            if ($fileSize > $totalMaxFileSize) {
                                return true;
                            }
                        }

                        return false;
                    })
                    ->status(413)
                    ->message(G::LoadTranslation("ID_UPLOAD_INVALID_DOC_MAX_FILESIZE",
                        [$inpDocMaxFileSize . $inpDocMaxFileSizeUnit]))
                    ->log(function ($rule) {
                        $message = $rule->getMessage();
                        $context = [
                            'filename' => $rule->getData()->filename,
                            'url' => $_SERVER["REQUEST_URI"] ?? ''
                        ];
                        Log::channel(':phpUpload')->notice($message, Bootstrap::context($context));
                    });
                $validator->validate();
                // We will to review if the validator has some error
                if ($validator->fails()) {
                    throw new ExceptionRestApi($validator->getMessage(), $validator->getStatus());
                }
            }
        }

        return true;
    }

    /**
     * Get the cases related to the self services timeout that needs to execute the trigger related
     *
     * @return array
     * @throws Exception
    */
    public static function executeSelfServiceTimeout()
    {
        try {
            $casesSelfService = ListUnassigned::selfServiceTimeout();
            $casesExecuted = [];
            foreach ($casesSelfService as $row) {
                $appUid = $row["APP_UID"];
                $appNumber = $row["APP_NUMBER"];
                $delIndex = $row["DEL_INDEX"];
                $delegateDate = $row["DEL_DELEGATE_DATE"];
                $proUid = $row["PRO_UID"];
                $taskUid = $row["TAS_UID"];
                $taskSelfServiceTime = intval($row["TAS_SELFSERVICE_TIME"]);
                $taskSelfServiceTimeUnit = $row["TAS_SELFSERVICE_TIME_UNIT"];
                $triggerUid = $row["TAS_SELFSERVICE_TRIGGER_UID"];


                // Add the time in the corresponding unit to the delegation date
                $delegateDate = calculateDate($delegateDate, $taskSelfServiceTimeUnit, $taskSelfServiceTime);
                $datetime = new DateTime($delegateDate);
                //please the seconds is variant not must be considered
                $delegateDate = $datetime->format('Y-m-d H:i:00');

                // Define the current time
                $datetime = new DateTime('now');
                //please the seconds is variant not must be considered
                $currentDate = $datetime->format('Y-m-d H:i:00');
                $currentDate = UtilDateTime::convertDataToUtc($currentDate);

                // Check if the triggers to be executed
                if ($currentDate >= $delegateDate && $flagExecuteOnce) {
                    // Review if the session process is defined
                    $sessProcess = null;
                    $sessProcessSw = false;
                    if (isset($_SESSION["PROCESS"])) {
                        $sessProcess = $_SESSION["PROCESS"];
                        $sessProcessSw = true;
                    }
                    // Load case data
                    $case = new ClassesCases();
                    $appFields = $case->loadCase($appUid);
                    $appFields["APP_DATA"]["APPLICATION"] = $appUid;
                    // Set the process defined in the case related
                    $_SESSION["PROCESS"] = $appFields["PRO_UID"];

                    // Get the trigger related and execute
                    $triggersList = [];
                    if (!empty($triggerUid)) {
                        $trigger = new Triggers();
                        $trigger->setTrigger($triggerUid);
                        $triggersList = $trigger->triggers();
                    }

                    // If the trigger exist, let's to execute
                    if (!empty($triggersList)) {
                        // Execute the trigger defined in the self service timeout
                        $fieldsCase['APP_DATA'] = $case->executeTriggerFromList(
                            $triggersList,
                            $appFields['APP_DATA'],
                            'SELF_SERVICE_TIMEOUT',
                            '',
                            '',
                            '',
                            false
                        );

                        // Update the case
                        $case->updateCase($appUid, $fieldsCase);


                        array_push($casesExecuted, $appNumber); // Register the cases executed

                        // Logging this action
                        $context = [
                            'appUid' => $appUid,
                            'appNumber' => $appNumber,
                            'triUid' => $triggerUid,
                            'proUid' => $proUid,
                            'tasUid' => $taskUid,
                            'selfServiceTime' => $taskSelfServiceTime,
                            'selfServiceTimeUnit' => $taskSelfServiceTimeUnit,
                            'currentDate' => $currentDate,
                            'delegateDate' => $delegateDate
                        ];
                        Log::channel('taskScheduler:executeSelfServiceTimeout')->info('TriggerExecution', Bootstrap::context($context));
                    }

                    unset($_SESSION["PROCESS"]);

                    if ($sessProcessSw) {
                        $_SESSION["PROCESS"] = $sessProcess;
                    }
                }
            }

            return $casesExecuted;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Get DynaForms Uids assigned as steps in the related process by application Uid
     *
     * @param string $appUid
     * @param int $sourceTask
     * @param string $dynUid
     * @param string $caseStatus
     * @return array
     */
    public static function dynaFormsByApplication($appUid, $sourceTask = 0, $dynUid = '', $caseStatus = '')
    {
        // Select distinct DYN_UID
        $query = ModelApplication::query()->select('STEP.STEP_UID_OBJ AS DYN_UID')->distinct();

        // Join with STEP table
        $query->join('STEP', function ($join)  {
            $join->on('APPLICATION.PRO_UID', '=', 'STEP.PRO_UID');
            $join->on('STEP.STEP_TYPE_OBJ', '=', DB::raw("'DYNAFORM'"));
        });

        // Filter by application Uid
        $query->where('APPLICATION.APP_UID', '=', $appUid);

        // Filter by source task
        if (!empty($sourceTask) && (int)$sourceTask != 0) {
            $query->where('STEP.TAS_UID', '=', $sourceTask);
        }

        // Filter by DynaForm Uid
        if ($dynUid != '' && $dynUid != '0') {
            $query->where('STEP.STEP_UID_OBJ', '=', $dynUid);
        }

        // Get results
        $dynaForms = [];
        $items = $query->get();
        $items->each(function ($item) use (&$dynaForms) {
            $dynaForms[] = $item->DYN_UID;
        });

        // Return results
        return $dynaForms;
    }
    
    /**
     * Get objects that they have send it.
     * @param string $appUid
     * @param string $typeObject
     * @return array
     */
    public function getStepsToRevise(string $appUid, string $typeObject): array
    {
        $application = ModelApplication::where('APP_UID', '=', $appUid)
                ->first();
        $result = StepSupervisor::where('PRO_UID', '=', $application['PRO_UID'])->
                where('STEP_TYPE_OBJ', '=', $typeObject)->
                orderBy('STEP_POSITION', 'ASC')->
                get()->
                toArray();
        return $result;
    }

    /**
     * Get all url steps to revise.
     * @param string $appUid
     * @param int $delIndex
     * @return array
     */
    public function getAllUrlStepsToRevise(string $appUid, int $delIndex): array
    {
        $result = [];
        $dynaformStep = $this->getStepsToRevise($appUid, 'DYNAFORM');
        $inputDocumentStep = $this->getStepsToRevise($appUid, 'INPUT_DOCUMENT');
        $objects = array_merge($dynaformStep, $inputDocumentStep);
        usort($objects, function ($a, $b) {
            return $a['STEP_POSITION'] > $b['STEP_POSITION'];
        });
        $i = 0;
        $endPoint = '';
        $uidName = '';
        foreach ($objects as $step) {
            if ($step['STEP_TYPE_OBJ'] === 'DYNAFORM') {
                $endPoint = 'cases_StepToRevise';
                $uidName = 'DYN_UID';
            }
            if ($step['STEP_TYPE_OBJ'] === 'INPUT_DOCUMENT') {
                $endPoint = 'cases_StepToReviseInputs';
                $uidName = 'INP_DOC_UID';
            }
            $url = "{$endPoint}?"
                    . "type={$step['STEP_TYPE_OBJ']}&"
                    . "ex={$i}&"
                    . "PRO_UID={$step["PRO_UID"]}&"
                    . "{$uidName}={$step['STEP_UID_OBJ']}&"
                    . "APP_UID={$appUid}&"
                    . "position={$step['STEP_POSITION']}&"
                    . "DEL_INDEX={$delIndex}";
            $result[] = [
                'uid' => $step['STEP_UID_OBJ'],
                'type' => $step['STEP_TYPE_OBJ'],
                'url' => $url
            ];
            $i++;
        }
        return $result;
    }

}
