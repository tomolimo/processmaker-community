<?php

namespace ProcessMaker\Services\Api;

use AppDelegation;
use AppDelegationPeer;
use AppDocument;
use Criteria;
use Exception;
use ListUnassigned;
use Luracast\Restler\RestException;
use ProcessMaker\BusinessModel\Cases as BmCases;
use ProcessMaker\BusinessModel\Cases\Filter;
use ProcessMaker\BusinessModel\User as BmUser;
use ProcessMaker\Services\Api;
use ProcessMaker\Util\DateTime;
use RBAC;


/**
 * Cases Api Controller
 *
 * @protected
 */
class Cases extends Api
{
    private $arrayFieldIso8601 = [
        "del_init_date",
        "del_finish_date",
        "del_task_due_date",
        "del_risk_date",
        "del_delegate_date",
        "app_create_date",
        "app_update_date",
        "app_finish_date",
        "del_delegate_date",
        "note_date"
    ];

    /**
     * Constructor of the class
     * We will to define the $RBAC definition
     */
    public function __construct()
    {
        global $RBAC;
        if (!isset($RBAC)) {
            $RBAC = RBAC::getSingleton(PATH_DATA, session_id());
            $RBAC->sSystem = 'PROCESSMAKER';
            $RBAC->initRBAC();
            $RBAC->loadUserRolePermission($RBAC->sSystem, $this->getUserId());
        }
    }

    /**
     * This function adds customized validations for allow the access to functions
     * If does not have access will be return 401
     *
     * @return boolean
     * @throws Exception
    */
    public function __isAllowed()
    {
        try {
            $methodName = $this->restler->apiMethodInfo->methodName;
            $arrayArgs  = $this->restler->apiMethodInfo->arguments;
            switch ($methodName) {
                case 'doGetCaseVariables':
                    $applicationUid = $this->parameters[$arrayArgs['appUid']];
                    $dynaformUid = $this->parameters[$arrayArgs['dyn_uid']];
                    $delIndex = $this->parameters[$arrayArgs['app_index']];
                    $userUid = $this->getUserId();
                    //check the guest user
                    if ($userUid === RBAC::GUEST_USER_UID) {
                        return true;
                    }
                    //Check if the user has the case
                    $appDelegation = new AppDelegation();
                    $curUser = $appDelegation->getCurrentUsers($applicationUid, $delIndex);
                    if (!empty($curUser)) {
                        foreach ($curUser as $key => $value) {
                            if ($value === $userUid) {
                                return true;
                            }
                        }
                    }
                    //Check if the user has Permissions
                    $cases = new BmCases();
                    return $cases->checkUserHasPermissionsOrSupervisor($userUid, $applicationUid, $dynaformUid);
                    break;
                case 'doPutCaseVariables':
                    $applicationUid = $this->parameters[$arrayArgs['appUid']];
                    $dynaformUid = $this->parameters[$arrayArgs['dyn_uid']];
                    $delIndex = $this->parameters[$arrayArgs['del_index']];
                    $userUid = $this->getUserId();

                    //Check if the user has the case currently
                    $appDelegation = new AppDelegation();
                    $currentUser = $appDelegation->getCurrentUsers($applicationUid, $delIndex);
                    foreach ($currentUser as $key => $value) {
                        if ($value === $userUid) {
                            return true;
                        }
                    }

                    //Check if the user is a supervisor
                    //Unlike GET, it is not enough to have the processPermission for update the variables
                    $cases = new BmCases();
                    $isSupervisor = $cases->isSupervisorFromForm($userUid, $applicationUid, $dynaformUid);
                    return $isSupervisor;
                    break;
                case 'doPostReassign':
                    $arrayParameters = $this->parameters[0]['cases'];
                    $usrUid = $this->getUserId();

                    //Check if the user is supervisor process
                    $case = new BmCases();
                    $user = new BmUser();
                    $count = 0;
                    foreach ($arrayParameters as $value) {
                        $arrayApplicationData = $case->getApplicationRecordByPk($value['APP_UID'], [], false);

                        if (!empty($arrayApplicationData)) {
                            $canReassign = $user->userCanReassign($usrUid, $arrayApplicationData['PRO_UID']);
                            if (!$canReassign) {
                                //We count when the user is not supervisor to the process
                                $count = $count + 1;
                            }
                        }
                    }

                    if ($count == 0) {
                        return true;
                    }
                    break;
                case 'doPutReassignCase':
                    $appUid = $this->parameters[$arrayArgs['appUid']];
                    $usrUid = $this->getUserId();
                    $case = new BmCases();
                    $user = new BmUser();
                    $arrayApplicationData = $case->getApplicationRecordByPk($appUid, [], false);

                    return $user->userCanReassign($usrUid, $arrayApplicationData['PRO_UID']);
                    break;
                case 'doGetCaseInfo':
                    $appUid = $this->parameters[$arrayArgs['appUid']];
                    $usrUid = $this->getUserId();

                    $case = new BmCases();
                    $arrayApplicationData = $case->getApplicationRecordByPk($appUid, [], false);
                    if (!empty($arrayApplicationData)) {
                        $criteria = new Criteria('workflow');
                        $criteria->addSelectColumn(AppDelegationPeer::APP_UID);
                        $criteria->add(AppDelegationPeer::APP_UID, $appUid);
                        $criteria->add(AppDelegationPeer::USR_UID, $usrUid);
                        $criteria->setLimit(1);
                        $rsCriteria = AppDelegationPeer::doSelectRS($criteria);
                        if ($rsCriteria->next()) {
                            return true;
                        }

                        //verify unassigned
                        $list = new ListUnassigned();
                        $data = $list->loadList($usrUid, ['search' => $appUid, 'caseLink' => true, 'limit' => 1]);

                        if ($data) {
                            return true;
                        }

                        //Check if the user is a process supervisor or has summary form view permission
                        $userCanAccess = $case->userAuthorization(
                            $usrUid,
                            $arrayApplicationData['PRO_UID'],
                            $appUid,
                            [],
                            ['SUMMARY_FORM' => 'VIEW']
                        );

                        return $userCanAccess['supervisor'] || $userCanAccess['objectPermissions']['SUMMARY_FORM'];
                    }
                    break;
                case 'doDownloadInputDocument':
                    //Verify if the user can be download the file
                    $appDocUid = $this->parameters[$arrayArgs['app_doc_uid']];
                    $version = $this->parameters[$arrayArgs['v']];
                    $usrUid = $this->getUserId();
                    $appDocument = new AppDocument();
                    if ($version == 0) {
                        $docVersion = $appDocument->getLastAppDocVersion($appDocUid);
                    } else {
                        $docVersion = $version;
                    }
                    if (defined('DISABLE_DOWNLOAD_DOCUMENTS_SESSION_VALIDATION') && DISABLE_DOWNLOAD_DOCUMENTS_SESSION_VALIDATION == 0) {
                        if ($appDocument->canDownloadInput($usrUid, $appDocUid, $docVersion)) {
                            return true;
                        }
                    } else {
                        return true;
                    }
                    break;
            }

            return false;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get list Cases To Do
     *
     * @url GET
     *
     * @param int $start {@from path}
     * @param int $limit {@from path}
     * @param string $sort {@from path}
     * @param string $dir {@from path}
     * @param string $cat_uid {@from path}
     * @param string $pro_uid {@from path}
     * @param string $search {@from path}
     *
     * @return array
     * @throws Exception
     * @deprecated Method deprecated in Release 3.6.x
     */
    public function doGetCasesListToDo(
        $start = 0,
        $limit = 0,
        $sort = 'APP_CACHE_VIEW.APP_NUMBER',
        $dir = 'DESC',
        $cat_uid = '',
        $pro_uid = '',
        $search = ''
    ) {
        try {
            $dataList['userId'] = $this->getUserId();
            $dataList['action'] = 'todo';
            $dataList['paged']  = false;
            $dataList['start'] = $start;
            $dataList['limit'] = $limit;
            $dataList['sort'] = $sort;
            $dataList['dir'] = $dir;
            $dataList['category'] = $cat_uid;
            $dataList['process'] = $pro_uid;
            $dataList['search'] = $search;
            $cases = new BmCases();
            $response = $cases->getList($dataList);

            return DateTime::convertUtcToIso8601($response, $this->arrayFieldIso8601);
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Get list Cases To Do with paged
     * @url GET /paged
     *
     * @param int $start {@from path}
     * @param int $limit {@from path}
     * @param string $sort {@from path}
     * @param string $dir {@from path}
     * @param string $cat_uid {@from path}
     * @param string $pro_uid {@from path}
     * @param string $search {@from path}
     *
     * @return array
     * @throws Exception
     * @deprecated Method deprecated in Release 3.6.x
     */
    public function doGetCasesListToDoPaged(
        $start = 0,
        $limit = 0,
        $sort = 'APP_CACHE_VIEW.APP_NUMBER',
        $dir = 'DESC',
        $cat_uid = '',
        $pro_uid = '',
        $search = ''
    ) {
        try {
            $dataList['userId'] = $this->getUserId();
            $dataList['action'] = 'todo';
            $dataList['paged']  = true;
            $dataList['start'] = $start;
            $dataList['limit'] = $limit;
            $dataList['sort'] = $sort;
            $dataList['dir'] = $dir;
            $dataList['category'] = $cat_uid;
            $dataList['process'] = $pro_uid;
            $dataList['search'] = $search;
            $cases = new BmCases();
            $response = $cases->getList($dataList);

            return DateTime::convertUtcToIso8601($response, $this->arrayFieldIso8601);
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Get list Cases Draft
     * @url GET /draft
     *
     * @param int $start {@from path}
     * @param int $limit {@from path}
     * @param string $sort {@from path}
     * @param string $dir {@from path}
     * @param string $cat_uid {@from path}
     * @param string $pro_uid {@from path}
     * @param string $search {@from path}
     *
     * @return array
     * @throws Exception
     * @deprecated Method deprecated in Release 3.6.x
     */
    public function doGetCasesListDraft(
        $start = 0,
        $limit = 0,
        $sort = 'APP_CACHE_VIEW.APP_NUMBER',
        $dir = 'DESC',
        $cat_uid = '',
        $pro_uid = '',
        $search = ''
    ) {
        try {
            $dataList['userId'] = $this->getUserId();
            $dataList['action'] = 'draft';
            $dataList['paged']  = false;
            $dataList['start'] = $start;
            $dataList['limit'] = $limit;
            $dataList['sort'] = $sort;
            $dataList['dir'] = $dir;
            $dataList['category'] = $cat_uid;
            $dataList['process'] = $pro_uid;
            $dataList['search'] = $search;
            $cases = new BmCases();
            $response = $cases->getList($dataList);

            return DateTime::convertUtcToIso8601($response, $this->arrayFieldIso8601);
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Get list Cases Draft with paged
     * @url GET /draft/paged
     *
     * @param int $start {@from path}
     * @param int $limit {@from path}
     * @param string $sort {@from path}
     * @param string $dir {@from path}
     * @param string $cat_uid {@from path}
     * @param string $pro_uid {@from path}
     * @param string $search {@from path}
     *
     * @return array
     * @throws Exception
     * @deprecated Method deprecated in Release 3.6.x
     */
    public function doGetCasesListDraftPaged(
        $start = 0,
        $limit = 0,
        $sort = 'APP_CACHE_VIEW.APP_NUMBER',
        $dir = 'DESC',
        $cat_uid = '',
        $pro_uid = '',
        $search = ''
    ) {
        try {
            $dataList['userId'] = $this->getUserId();
            $dataList['action'] = 'draft';
            $dataList['paged']  = true;
            $dataList['start'] = $start;
            $dataList['limit'] = $limit;
            $dataList['sort'] = $sort;
            $dataList['dir'] = $dir;
            $dataList['category'] = $cat_uid;
            $dataList['process'] = $pro_uid;
            $dataList['search'] = $search;
            $cases = new BmCases();
            $response = $cases->getList($dataList);

            return DateTime::convertUtcToIso8601($response, $this->arrayFieldIso8601);
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Get list Cases Participated
     * @url GET /participated
     *
     * @param int $start {@from path}
     * @param int $limit {@from path}
     * @param string $sort {@from path}
     * @param string $dir {@from path}
     * @param string $cat_uid {@from path}
     * @param string $pro_uid {@from path}
     * @param string $search {@from path}
     *
     * @return array
     * @throws Exception
     * @deprecated Method deprecated in Release 3.6.x
     */
    public function doGetCasesListParticipated(
        $start = 0,
        $limit = 0,
        $sort = 'APP_CACHE_VIEW.APP_NUMBER',
        $dir = 'DESC',
        $cat_uid = '',
        $pro_uid = '',
        $search = ''
    ) {
        try {
            $dataList['userId'] = $this->getUserId();
            $dataList['action'] = 'sent';
            $dataList['paged']  = false;
            $dataList['start'] = $start;
            $dataList['limit'] = $limit;
            $dataList['sort'] = $sort;
            $dataList['dir'] = $dir;
            $dataList['category'] = $cat_uid;
            $dataList['process'] = $pro_uid;
            $dataList['search'] = $search;
            $cases = new BmCases();
            $response = $cases->getList($dataList);

            return DateTime::convertUtcToIso8601($response, $this->arrayFieldIso8601);
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Get list Cases Participated with paged
     * @url GET /participated/paged
     *
     * @param int $start {@from path}
     * @param int $limit {@from path}
     * @param string $sort {@from path}
     * @param string $dir {@from path}
     * @param string $cat_uid {@from path}
     * @param string $pro_uid {@from path}
     * @param string $search {@from path}
     *
     * @return array
     * @throws Exception
     * @deprecated Method deprecated in Release 3.6.x
     */
    public function doGetCasesListParticipatedPaged(
        $start = 0,
        $limit = 0,
        $sort = 'APP_CACHE_VIEW.APP_NUMBER',
        $dir = 'DESC',
        $cat_uid = '',
        $pro_uid = '',
        $search = ''
    ) {
        try {
            $dataList['userId'] = $this->getUserId();
            $dataList['action'] = 'sent';
            $dataList['paged']  = true;
            $dataList['start'] = $start;
            $dataList['limit'] = $limit;
            $dataList['sort'] = $sort;
            $dataList['dir'] = $dir;
            $dataList['category'] = $cat_uid;
            $dataList['process'] = $pro_uid;
            $dataList['search'] = $search;
            $cases = new BmCases();
            $response = $cases->getList($dataList);

            return DateTime::convertUtcToIso8601($response, $this->arrayFieldIso8601);
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Get list Cases Unassigned
     * @url GET /unassigned
     *
     * @param int $start {@from path}
     * @param int $limit {@from path}
     * @param string $sort {@from path}
     * @param string $dir {@from path}
     * @param string $cat_uid {@from path}
     * @param string $pro_uid {@from path}
     * @param string $search {@from path}
     *
     * @return array
     * @throws Exception
     * @deprecated Method deprecated in Release 3.6.x
     */
    public function doGetCasesListUnassigned(
        $start = 0,
        $limit = 0,
        $sort = 'APP_CACHE_VIEW.APP_NUMBER',
        $dir = 'DESC',
        $cat_uid = '',
        $pro_uid = '',
        $search = ''
    ) {
        try {
            $dataList['userId'] = $this->getUserId();
            $dataList['action'] = 'unassigned';
            $dataList['paged']  = false;
            $dataList['start'] = $start;
            $dataList['limit'] = $limit;
            $dataList['sort'] = $sort;
            $dataList['dir'] = $dir;
            $dataList['category'] = $cat_uid;
            $dataList['process'] = $pro_uid;
            $dataList['search'] = $search;
            $cases = new BmCases();
            $response = $cases->getList($dataList);

            return DateTime::convertUtcToIso8601($response, $this->arrayFieldIso8601);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get list Cases Unassigned with paged
     * @url GET /unassigned/paged
     *
     * @param int $start {@from path}
     * @param int $limit {@from path}
     * @param string $sort {@from path}
     * @param string $dir {@from path}
     * @param string $cat_uid {@from path}
     * @param string $pro_uid {@from path}
     * @param string $search {@from path}
     *
     * @return array
     * @throws Exception
     * @deprecated Method deprecated in Release 3.6.x
     */
    public function doGetCasesListUnassignedPaged(
        $start = 0,
        $limit = 0,
        $sort = 'APP_CACHE_VIEW.APP_NUMBER',
        $dir = 'DESC',
        $cat_uid = '',
        $pro_uid = '',
        $search = ''
    ) {
        try {
            $dataList['userId'] = $this->getUserId();
            $dataList['action'] = 'unassigned';
            $dataList['paged']  = true;
            $dataList['start'] = $start;
            $dataList['limit'] = $limit;
            $dataList['sort'] = $sort;
            $dataList['dir'] = $dir;
            $dataList['category'] = $cat_uid;
            $dataList['process'] = $pro_uid;
            $dataList['search'] = $search;
            $cases = new BmCases();
            $response = $cases->getList($dataList);

            return DateTime::convertUtcToIso8601($response, $this->arrayFieldIso8601);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get list Cases Paused
     * @url GET /paused
     *
     * @param int $start {@from path}
     * @param int $limit {@from path}
     * @param string $sort {@from path}
     * @param string $dir {@from path}
     * @param string $cat_uid {@from path}
     * @param string $pro_uid {@from path}
     * @param string $search {@from path}
     *
     * @return array
     * @throws Exception
     * @deprecated Method deprecated in Release 3.6.x
     */
    public function doGetCasesListPaused(
        $start = 0,
        $limit = 0,
        $sort = 'APP_CACHE_VIEW.APP_NUMBER',
        $dir = 'DESC',
        $cat_uid = '',
        $pro_uid = '',
        $search = ''
    ) {
        try {
            $dataList['userId'] = $this->getUserId();
            $dataList['action'] = 'paused';
            $dataList['paged']  = false;
            $dataList['start'] = $start;
            $dataList['limit'] = $limit;
            $dataList['sort'] = $sort;
            $dataList['dir'] = $dir;
            $dataList['category'] = $cat_uid;
            $dataList['process'] = $pro_uid;
            $dataList['search'] = $search;
            $cases = new BmCases();
            $response = $cases->getList($dataList);

            return DateTime::convertUtcToIso8601($response, $this->arrayFieldIso8601);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get list Cases Paused with paged
     * @url GET /paused/paged
     *
     * @param int $start {@from path}
     * @param int $limit {@from path}
     * @param string $sort {@from path}
     * @param string $dir {@from path}
     * @param string $cat_uid {@from path}
     * @param string $pro_uid {@from path}
     * @param string $search {@from path}
     *
     * @return array
     * @throws Exception
     * @deprecated Method deprecated in Release 3.6.x
     */
    public function doGetCasesListPausedPaged(
        $start = 0,
        $limit = 0,
        $sort = 'APP_CACHE_VIEW.APP_NUMBER',
        $dir = 'DESC',
        $cat_uid = '',
        $pro_uid = '',
        $search = ''
    ) {
        try {
            $dataList['userId'] = $this->getUserId();
            $dataList['action'] = 'paused';
            $dataList["paged"]  = true;
            $dataList['start'] = $start;
            $dataList['limit'] = $limit;
            $dataList['sort'] = $sort;
            $dataList['dir'] = $dir;
            $dataList['category'] = $cat_uid;
            $dataList['process'] = $pro_uid;
            $dataList['search'] = $search;
            $cases = new BmCases();
            $response = $cases->getList($dataList);

            return DateTime::convertUtcToIso8601($response, $this->arrayFieldIso8601);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get list Cases Advanced Search
     * @url GET /advanced-search
     *
     * @param int $start {@from path}
     * @param int $limit {@from path}
     * @param string $sort {@from path}
     * @param string $dir {@from path}
     * @param string $cat_uid {@from path}
     * @param string $pro_uid {@from path}
     * @param string $app_status {@from path}
     * @param string $usr_uid {@from path}
     * @param string $date_from {@from path}
     * @param string $date_to {@from path}
     * @param string $search {@from path}
     *
     * @return array
     * @throws Exception
     * @deprecated Method deprecated in Release 3.6.x
     */
    public function doGetCasesListAdvancedSearch(
        $start = 0,
        $limit = 0,
        $sort = 'APP_CACHE_VIEW.APP_NUMBER',
        $dir = 'DESC',
        $cat_uid = '',
        $pro_uid = '',
        $app_status = '',
        $usr_uid = '',
        $date_from = '',
        $date_to = '',
        $search = ''
    ) {
        try {
            global $RBAC;
            //If the user does not have PM_ALLCASES we will be able to search for cases in which the user has participated
            $dataList['userId'] = ($RBAC->userCanAccess('PM_ALLCASES') == 1)? '' : $this->getUserId();
            $dataList['action'] = 'search';
            $dataList['paged']  = false;
            $dataList['start'] = $start;
            $dataList['limit'] = $limit;
            $dataList['sort'] = $sort;
            $dataList['dir'] = $dir;
            $dataList['category'] = $cat_uid;
            $dataList['process'] = $pro_uid;
            $dataList['status'] = $app_status;
            $dataList['user'] = $usr_uid;
            $dataList['dateFrom'] = $date_from;
            $dataList['dateTo'] = $date_to;
            $dataList['search'] = $search;
            $cases = new BmCases();
            $response = $cases->getList($dataList);

            return DateTime::convertUtcToIso8601($response, $this->arrayFieldIso8601);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get list Cases Advanced Search with Paged
     * @url GET /advanced-search/paged
     *
     * @param int $start {@from path}
     * @param int $limit {@from path}
     * @param string $sort {@from path}
     * @param string $dir {@from path}
     * @param string $cat_uid {@from path}
     * @param string $pro_uid {@from path}
     * @param string $app_status {@from path}
     * @param string $usr_uid {@from path}
     * @param string $date_from {@from path}
     * @param string $date_to {@from path}
     * @param string $search {@from path}
     *
     * @return array
     * @throws Exception
     * @deprecated Method deprecated in Release 3.6.x
     */
    public function doGetCasesListAdvancedSearchPaged(
        $start = 0,
        $limit = 0,
        $sort = 'APP_CACHE_VIEW.APP_NUMBER',
        $dir = 'DESC',
        $cat_uid = '',
        $pro_uid = '',
        $app_status = '',
        $usr_uid = '',
        $date_from = '',
        $date_to = '',
        $search = ''
    ) {
        try {
            global $RBAC;
            //If the user does not have PM_ALLCASES we will be able to search for cases in which the user has participated
            $dataList['userId'] = ($RBAC->userCanAccess('PM_ALLCASES') == 1)? '' : $this->getUserId();
            $dataList['action'] = 'search';
            $dataList['paged']  = true;
            $dataList['start'] = $start;
            $dataList['limit'] = $limit;
            $dataList['sort'] = $sort;
            $dataList['dir'] = $dir;
            $dataList['category'] = $cat_uid;
            $dataList['process'] = $pro_uid;
            $dataList['status'] = $app_status;
            $dataList['user'] = $usr_uid;
            $dataList['dateFrom'] = $date_from;
            $dataList['dateTo'] = $date_to;
            $dataList['search'] = $search;
            $cases = new BmCases();
            $response = $cases->getList($dataList);

            return DateTime::convertUtcToIso8601($response, $this->arrayFieldIso8601);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * @access protected
     * @class AccessControl {@className \ProcessMaker\Services\Api\Cases}
     * @url GET /:appUid
     *
     * @param string $appUid {@min 32}{@max 32}
     *
     * @return array
     * @throws Exception
     */
    public function doGetCaseInfo($appUid)
    {
        try {
            $case = new BmCases();
            $case->setFormatFieldNameInUppercase(false);
            $caseInfo = $case->getCaseInfo($appUid, $this->getUserId());
            $caseInfo = DateTime::convertUtcToIso8601($caseInfo, $this->arrayFieldIso8601);

            return $caseInfo;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * @url GET /:appUid/current-task
     *
     * @param string $appUid {@min 32}{@max 32}
     */
    public function doGetTaskCase($appUid)
    {
        try {
            $case = new BmCases();
            $case->setFormatFieldNameInUppercase(false);
            $arrayData = $case->getTaskCase($appUid, $this->getUserId());
            $response = $arrayData;

            return DateTime::convertUtcToIso8601($response, $this->arrayFieldIso8601);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Start a new case and assign the logged-in user to work on the initial task 
     * in the case. Note that the logged-in user must be in the pool of assigned 
     * users for the initial task.
     * 
     * @url POST
     * 
     * @param string $pro_uid {@from body} {@min 32}{@max 32}
     * @param string $tas_uid {@from body} {@min 32}{@max 32}
     * @param array $variables {@from body}
     * 
     * @return array
     * @throws RestException 
     * 
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doPostCase($pro_uid, $tas_uid, $variables = null)
    {
        try {
            $userUid = $this->getUserId();
            $cases = new BmCases();
            $data = $cases->addCase($pro_uid, $tas_uid, $userUid, $variables);

            return $data;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Creates a new case. It is similar to POST /cases, but it impersonates the 
     * session variables, so it is more robust than POST /cases. Note that the 
     * specified user to work on the case must be assigned to the pool of users 
     * for the initial task. Also note that the new case's status will be set to 
     * "DRAFT", not "TO_DO". If wishing to change the new case's status to "TO_DO", 
     * then create the following trigger in the process and use 
     * PUT /cases/{app_uid}/execute-trigger/{tri_uid} to execute it.
     * 
     * @url POST /impersonate
     * @status 201
     * 
     * @param string $pro_uid {@from body} {@min 32}{@max 32}
     * @param string $usr_uid {@from body} {@min 32}{@max 32}
     * @param string $tas_uid {@from body} {@min 32}{@max 32}
     * @param array $variables {@from body}
     * 
     * @return array
     * @throws RestException 
     * 
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doPostCaseImpersonate($pro_uid, $usr_uid, $tas_uid, $variables = null)
    {
        try {
            $cases = new BmCases();
            $data = $cases->addCaseImpersonate($pro_uid, $usr_uid, $tas_uid, $variables);
            return $data;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Update case reassignment.
     *
     * @url PUT /:appUid/reassign-case
     *
     * @param string $appUid {@min 32}{@max 32}
     * @param string $usr_uid_source {@from body} {@min 32}{@max 32}
     * @param string $usr_uid_target {@from body} {@min 32}{@max 32}
     * @param int $del_index {@from body}
     * @param string $reason {@from body}
     * @param boolean $sendMail {@from body}
     *
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@className \ProcessMaker\Services\Api\Cases}
     */
    public function doPutReassignCase($appUid, $usr_uid_source, $usr_uid_target, $del_index = null, $reason = '', $sendMail = false)
    {
        try {
            $userUid = $this->getUserId();
            $cases = new BmCases();
            $cases->updateReassignCase($appUid, $userUid, $del_index, $usr_uid_source, $usr_uid_target, $reason, $sendMail);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Route Case.
     *
     * @url PUT /:appUid/route-case
     *
     * @param string $appUid {@min 32}{@max 32}
     * @param string $del_index {@from body}
     * @param boolean $executeTriggersBeforeAssignment {@from body}
     *
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doPutRouteCase($appUid, $del_index = null, $executeTriggersBeforeAssignment = false)
    {
        try {
            $userUid = $this->getUserId();
            $cases = new BmCases();
            $cases->updateRouteCase($appUid, $userUid, $del_index, $executeTriggersBeforeAssignment);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Cancel Case
     *
     * @url PUT /:appUid/cancel
     *
     * @param string $appUid {@min 1}{@max 32}
     * @param integer $index {@from body}
     * @param string $reason {@from body}
     * @param boolean $sendMail {@from body}
     *
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_CANCELCASE}
     */
    public function doPutCancelCase($appUid, $index = null, $reason = '', $sendMail = false)
    {
        try {
            $userUid = $this->getUserId();
            $cases = new BmCases();
            $cases->putCancelCase($appUid, $userUid, $index, $reason, $sendMail);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Pause Case
     *
     * @url PUT /:appUid/pause
     *
     * @param string $appUid {@min 1}{@max 32}
     * @param string $unpaused_date {@from body}
     * @param string $unpaused_time {@from body}
     * @param int $index {@from body}
     * @param string $reason {@from body}
     * @param boolean $sendMail {@from body}
     *
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doPutPauseCase($appUid, $unpaused_date = null, $unpaused_time = '00:00', $index = 0, $reason = '', $sendMail = false)
    {
        try {
            $userUid = $this->getUserId();
            $cases = new BmCases();
            $cases->putPauseCase($appUid, $userUid, $index, $unpaused_date, $unpaused_time, $reason, $sendMail);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Unpause Case
     *
     * @url PUT /:appUid/unpause
     *
     * @param string $appUid {@min 1}{@max 32}
     * @param int $index {@from body}
     *
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doPutUnpauseCase($appUid, $index = 0)
    {
        try {
            $userUid = $this->getUserId();
            $cases = new BmCases();
            $cases->putUnpauseCase($appUid, $userUid, $index);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Claim Case
     *
     * @url PUT /:appUid/claim
     *
     * @param string $appUid {@min 1}{@max 32}
     * @param integer $index {@from body}
     *
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doPutClaimCase($appUid, $index)
    {
        try {
            $userUid = $this->getUserId();
            $cases = new BmCases();
            $cases->putClaimCase($appUid, $index, $userUid, 'Claim');
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }
    
    /**
     * Verify if current user is a supervisor
     *
     * @url GET /:appNumber/supervisor
     *
     * @param int $appNumber
     *
     * @return boolean
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function isSupervisor(int $appNumber)
    {
        try {
            $userUid = $this->getUserId();
            $cases = new BmCases();
            return $cases->isSupervisor($userUid, $appNumber);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Assign Case
     *
     * @url PUT /:appUid/:usrUid/assign
     *
     * @param string $appUid {@min 1}{@max 32}
     * @param string $usrUid {@min 1}{@max 32}
     * @param int $index {@from body}
     * @param string $reason {@from body}
     * @param bool $sendMail {@from body}
     *
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doPutAssignCase($appUid, $usrUid, $index, $reason = '', $sendMail = false)
    {
        try {
            $cases = new BmCases();
            $cases->putClaimCase($appUid, $index, $usrUid, 'Assign', $reason);

            /** Add the note */
            if (!empty($reason)) {
                $currentUserUid = $this->getUserId();
                $cases->sendMail($appUid, $currentUserUid, $reason, $sendMail, $usrUid);
            }
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get users to reassign or assign
     *
     * @url GET /:task_uid/:app_uid/userstoreassign
     *
     * @param string $task_uid
     * @param string $app_uid
     *
     * @return array
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function usersToReasign($task_uid, $app_uid)
    {
        try {
            $usr_uid = $this->getUserId();
            $cases = new BmCases();
            return $cases->usersToReassign($usr_uid, $task_uid, $app_uid);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Execute trigger in a case.
     *
     * @url PUT /:appUid/execute-trigger/:tri_uid
     *
     * @param string $appUid {@min 1}{@max 32}
     * @param string $tri_uid {@min 1}{@max 32}
     *
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doPutExecuteTriggerCase($appUid, $tri_uid)
    {
        try {
            $userUid = $this->getUserId();
            $cases = new BmCases();
            $cases->putExecuteTriggerCase($appUid, $tri_uid, $userUid);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Delete Case
     * @url DELETE /:appUid
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     * @param string $appUid {@min 1}{@max 32}
     * @throws Exception
     */
    public function doDeleteCase($appUid)
    {
        try {
            $usr_uid = $this->getUserId();
            $cases = new BmCases();
            $cases->deleteCase($appUid, $usr_uid);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get Case Variables
     *
     * @access protected
     * @class  AccessControl {@className \ProcessMaker\Services\Api\Cases}
     * @url GET /:appUid/variables
     *
     * @param string $appUid {@min 1}{@max 32}
     * @param string $dyn_uid
     * @param string $pro_uid
     * @param string $act_uid
     * @param int $app_index
     * @return mixed
     * @throws RestException
     */
    public function doGetCaseVariables($appUid, $dyn_uid = null, $pro_uid = null, $act_uid = null, $app_index = null)
    {
        try {
            $usr_uid = $this->getUserId();
            $cases = new BmCases();
            $response = $cases->getCaseVariables($appUid, $usr_uid, $dyn_uid, $pro_uid, $act_uid, $app_index);
            return DateTime::convertUtcToIso8601($response);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Put Case Variables
     *
     * @url PUT /:appUid/variable
     *
     * @param string $appUid {@min 1}{@max 32}
     * @param array $request_data
     * @param string $dyn_uid {@from path}
     * @param int $del_index {@from path}
     *
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doPutCaseVariables($appUid, $request_data, $dyn_uid = '', $del_index = 0)
    {
        try {
            $usr_uid = $this->getUserId();
            $cases = new BmCases();
            $request_data = \ProcessMaker\Util\DateTime::convertDataToUtc($request_data);
            $cases->setCaseVariables($appUid, $request_data, $dyn_uid, $usr_uid, $del_index);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get Case Notes
     * @url GET /:appUid/notes
     * @url GET /:appUid/notes/:paged
     *
     * @param string $appUid {@min 1}{@max 32}
     * @param string $paged
     * @param int $start {@from path}
     * @param int $limit {@from path}
     * @param string $sort {@from path}
     * @param string $dir {@from path}
     * @param string $usr_uid {@from path}
     * @param string $date_from {@from path}
     * @param string $date_to {@from path}
     * @param string $search {@from path}
     * @param boolean $files {@from path}
     *
     * @return array
     * @throws Exception
     */
    public function doGetCaseNotes(
        $appUid,
        $paged = '',
        $start = 0,
        $limit = 25,
        $sort = 'NOTE_DATE',
        $dir = 'DESC',
        $usr_uid = '',
        $date_from = '',
        $date_to = '',
        $search = '',
        $files = false
    ) {
        try {
            $dataList['paged'] = ($paged === 'paged') ? true : false;
            $dataList['start'] = $start;
            $dataList['limit'] = $limit;
            $dataList['sort'] = $sort;
            $dataList['dir'] = $dir;
            $dataList['user'] = $usr_uid;
            $dataList['dateFrom'] = $date_from;
            $dataList['dateTo'] = $date_to;
            $dataList['search'] = $search;
            $dataList['files'] = $files;
            $usr_uid = $this->getUserId();
            $cases = new BmCases();
            $response = $cases->getCaseNotes($appUid, $usr_uid, $dataList);

            return DateTime::convertUtcToIso8601($response, $this->arrayFieldIso8601);
        } catch (Exception $e) {
            throw new RestException(401, $e->getMessage());
        }
    }

    /**
     * Create a new case note for a given case. Note that only users who are
     * currently assigned to work on the case or have Process Permissions to
     * access case notes may create a case note.
     *
     * @url POST /:appUid/note
     * @status 201
     *
     * @param string $appUid {@min 1}{@max 32}
     * @param string $note_content {@min 1}{@max 500}
     * @param int $send_mail {@choice 1,0}
     *
     * @return void
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doPostCaseNote($appUid, $note_content, $send_mail = 0)
    {
        try {
            $usr_uid = $this->getUserId();
            $cases = new BmCases();
            $send_mail = ($send_mail == 0) ? false : true;
            $cases->saveCaseNote($appUid, $usr_uid, $note_content, $send_mail);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * @url GET /:appUid/tasks
     *
     * @param string $appUid {@min 32}{@max 32}
     */
    public function doGetTasks($appUid)
    {
        try {
            $case = new BmCases();
            $case->setFormatFieldNameInUppercase(false);
            $response = $case->getTasks($appUid);

            return DateTime::convertUtcToTimeZone($response);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Execute triggers
     *
     * @url PUT /:appUid/execute-triggers
     *
     * @param string $appUid {@min 1}{@max 32}
     * @param int $del_index {@from body}
     * @param string $obj_type {@from body}
     * @param string $obj_uid {@from body}
     *
     * @throws RestException
     *
     * @class AccessControl {@permission PM_CASES}
     * @access protected
     */
    public function doPutExecuteTriggers($appUid, $del_index, $obj_type, $obj_uid)
    {
        try {
            $cases = new BmCases();
            $cases->putExecuteTriggers($appUid, $del_index, $obj_type, $obj_uid);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * @url GET /:appUid/:del_index/steps
     *
     * @param string $appUid {@min 32}{@max 32}
     * @param int $del_index
     */
    public function doGetSteps($appUid, $del_index)
    {
        try {
            $case = new BmCases();
            $case->setFormatFieldNameInUppercase(false);
            $response = $case->getSteps($appUid, $del_index);

            return $response;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get process list for start case
     *
     * @url GET /start-cases
     *
     * @param string $type_view {@from path}
     * @return array
     * @throws Exception
     */
    public function doGetCasesListStarCase(
        $type_view = ''
    ) {
        try {
            $usrUid = $this->getUserId();
            $case = new BmCases();
            $response = $case->getCasesListStarCase($usrUid, $type_view);

            return $response;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get process list bookmark for start case
     *
     * @url GET /bookmark-start-cases
     *
     * @param string $type_view {@from path}
     * @return array
     * @throws Exception
     */
    public function doGetCasesListBookmarkStarCase(
        $type_view = ''
    ) {
        try {
            $usrUid = $this->getUserId();
            $case = new BmCases();
            $response = $case->getCasesListBookmarkStarCase($usrUid, $type_view);

            return $response;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }


    /**
     * Mark a task process as a bookmark
     *
     * @url POST /bookmark/:tas_uid
     *
     * @param string $tas_uid {@min 32}{@max 32}
     *
     * @return void
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doPostBookmarkStartCase($tas_uid)
    {
        try {
            $userLoggedUid = $this->getUserId();
            $user = new BmUser();
            $user->updateBookmark($userLoggedUid, $tas_uid, 'INSERT');
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Remove a task process from bookmarks
     * @url DELETE /bookmark/:tas_uid
     *
     * @param string $tas_uid {@min 32}{@max 32}
     * @throws Exception
     *
     */
    public function doDeleteBookmarkStartCase($tas_uid)
    {
        try {
            $userLoggedUid = $this->getUserId();
            $user = new BmUser();
            $user->updateBookmark($userLoggedUid, $tas_uid, 'DELETE');
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Batch reassign
     * @url POST /reassign
     *
     * @access protected
     * @class  AccessControl {@className \ProcessMaker\Services\Api\Cases}
     *
     * @param array $request_data
     * @throws Exception
     *
     */
    public function doPostReassign($request_data)
    {
        try {
            $case = new BmCases();
            $response = $case->doPostReassign($request_data);

            return $response;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Upload attachment related to the case, it does not need docUid
     * Upload document related to the case, it does need docUid
     *
     * @url POST /:appUid/upload/:var_name
     * @url POST /:appUid/upload/:var_name/:doc_uid
     * @url POST /:appUid/upload/:var_name/:doc_uid/:app_doc_uid
     *
     * @param string $appUid
     * @param string $var_name
     * @param string $doc_uid
     * @param string $app_doc_uid
     * @param int $delIndex {@from body}
     *
     * @return array
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function uploadDocumentToCase($appUid, $var_name, $doc_uid = '-1', $app_doc_uid = null, $delIndex = null)
    {
        try {
            $userUid = $this->getUserId();
            $case = new BmCases();
            if (isset($delIndex)) {
                $response = $case->uploadFiles($userUid, $appUid, $var_name, $doc_uid, $app_doc_uid, $delIndex);
            } else {
                $response = $case->uploadFiles($userUid, $appUid, $var_name, $doc_uid, $app_doc_uid);
            }
        } catch (ExceptionRestApi $e) {
            throw new RestException($e->getCode(), $e->getMessage());
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }

        return $response;
    }

    /**
     * Return information for sub process cases
     *
     * @url GET /:appUid/sub-process-cases
     *
     * @param string $appUid {@min 32}{@max 32}
     *
     * @return array
     * @throws Exception
     *
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     */
    public function doGetCaseSubProcess($appUid)
    {
        try {
            $case = new BmCases();
            $case->setFormatFieldNameInUppercase(false);
            $caseInfo = $case->getCaseInfoSubProcess($appUid, $this->getUserId());
            $caseInfo = DateTime::convertUtcToIso8601($caseInfo, $this->arrayFieldIso8601);

            return $caseInfo;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get filters of the advanced search for the current user
     *
     * @url GET /advanced-search/filters
     *
     * @return array
     *
     * @throws RestException
     */
    public function doGetAdvancedSearchFilters()
    {
        try {
            return Filter::getByUser($this->getUserId());
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get a specific filter of the advanced search for the current user
     *
     * @url GET /advanced-search/filter/:filterUid
     *
     * @param string $filterUid {@min 32}{@max 32}
     *
     * @return object
     *
     * @throws RestException
     */
    public function doGetAdvancedSearchFilter($filterUid)
    {
        try {
            $filter = Filter::getByUid($this->getUserId(), $filterUid);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }

        // If not exists the requested filter throw an 404 error
        if (is_null($filter)) {
            throw new RestException(404, "Filter with Uid '{$filterUid}'.");
        }
        return $filter;
    }

    /**
     * Add a new filter of the advanced search for the current user
     *
     * @url POST /advanced-search/filter
     *
     * @param string $name
     * @param string $filters
     *
     * @return object
     *
     * @throws RestException
     */
    public function doPostAdvancedSearchFilter($name, $filters)
    {
        try {
            // Create JSON object if is a serialized string
            $filters = is_string($filters) ? json_decode($filters) : $filters;
            // Create new filter
            $filter = Filter::create($this->getUserId(), $name, $filters);
            // Return the new filter
            return $filter;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Update a filter of the advanced search for the current user
     *
     * @url PUT /advanced-search/filter/:filterUid
     *
     * @param string $filterUid {@min 32}{@max 32}
     * @param string $name
     * @param string $filters
     *
     * @throws RestException
     */
    public function doPutAdvancedSearchFilter($filterUid, $name, $filters)
    {
        try {
            // Create JSON object if is a serialized string
            $filters = is_string($filters) ? json_decode($filters) : $filters;
            // Get requested filter
            $filter = Filter::getByUid($this->getUserId(), $filterUid);
            // Update the requested filter if exists
            if (!is_null($filter)) {
                Filter::update($this->getUserId(), $filterUid, $name, $filters);
            }
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
        // If not exists the requested filter throw an 404 error
        if (is_null($filter)) {
            throw new RestException(404, "Filter with Uid '{$filterUid}'.");
        }
    }

    /**
     * Delete a specific filter of the advanced search for the current user
     *
     * @url DELETE /advanced-search/filter/:filterUid
     *
     * @param string $filterUid {@min 32}{@max 32}
     *
     * @throws RestException
     */
    public function doDeleteAdvancedSearchFilter($filterUid)
    {
        try {
            Filter::delete($filterUid);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }
}
