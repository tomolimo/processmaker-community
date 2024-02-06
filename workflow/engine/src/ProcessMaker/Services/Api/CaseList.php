<?php

namespace ProcessMaker\Services\Api;

use Exception;
use G;
use Luracast\Restler\RestException;
use ProcessMaker\Model\CaseList as CaseListBusinessModel;
USE ProcessMaker\Model\UserConfig;
use ProcessMaker\Services\Api;
use RBAC;

class CaseList extends Api
{

    /**
     * Constructor of the class.
     * @global object $RBAC
     */
    public function __construct()
    {
        parent::__construct();
        global $RBAC;
        if (!isset($RBAC)) {
            $RBAC = RBAC::getSingleton(PATH_DATA, session_id());
            $RBAC->sSystem = 'PROCESSMAKER';
            $RBAC->initRBAC();
            $RBAC->loadUserRolePermission($RBAC->sSystem, $this->getUserId());
        }
    }

    /**
     * Create the Case List setting.
     * @url POST
     * @param array $request_data
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     * @return array
     */
    public function doPost(array $request_data)
    {
        $ownerId = $this->getUserId();
        $caseList = CaseListBusinessModel::createSetting($request_data, $ownerId);
        $caseList = CaseListBusinessModel::getAliasFromColumnName($caseList->toArray());
        return $caseList;
    }

    /**
     * Update the Case List setting.
     * @url PUT /:id
     * @param string $id
     * @param array $request_data
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     * @throws RestException
     * @return array
     */
    public function doPut(int $id, array $request_data)
    {
        $ownerId = $this->getUserId();
        $caseList = CaseListBusinessModel::updateSetting($id, $request_data, $ownerId);
        if (is_null($caseList)) {
            throw new RestException(Api::STAT_APP_EXCEPTION, G::LoadTranslation('ID_DOES_NOT_EXIST'));
        }
        $caseList = CaseListBusinessModel::getAliasFromColumnName($caseList->toArray());
        UserConfig::updateUserConfig($id, $caseList);
        return $caseList;
    }

    /**
     * Delete the Case List setting.
     * @url DELETE /:id
     * @param string $id
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     * @throws RestException
     * @return array
     */
    public function doDelete(int $id)
    {
        try {
            $caseList = CaseListBusinessModel::deleteSetting($id);
            if (is_null($caseList)) {
                throw new RestException(Api::STAT_APP_EXCEPTION, G::LoadTranslation('ID_DOES_NOT_EXIST'));
            }
            $caseList = CaseListBusinessModel::getAliasFromColumnName($caseList->toArray());
            return $caseList;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get inbox Case List settings.
     * @url GET /inbox
     * @param string $search
     * @param int $offset
     * @param int $limit
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     * @return array
     */
    public function doGetInbox(string $search = '', int $offset = 0, int $limit = 10)
    {
        return CaseListBusinessModel::getSetting('inbox', $search, $offset, $limit);
    }

    /**
     * Get draft Case List settings.
     * @url GET /draft
     * @param string $search
     * @param int $offset
     * @param int $limit
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     * @return array
     */
    public function doGetDraft(string $search = '', int $offset = 0, int $limit = 10)
    {
        return CaseListBusinessModel::getSetting('draft', $search, $offset, $limit);
    }

    /**
     * Get paused Case List settings.
     * @url GET /paused
     * @param string $search
     * @param int $offset
     * @param int $limit
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     * @return array
     */
    public function doGetPaused(string $search = '', int $offset = 0, int $limit = 10)
    {
        return CaseListBusinessModel::getSetting('paused', $search, $offset, $limit);
    }

    /**
     * Get unassigned Case List settings.
     * @url GET /unassigned
     * @param string $search
     * @param int $offset
     * @param int $limit
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     * @return array
     */
    public function doGetUnassigned(string $search = '', int $offset = 0, int $limit = 10)
    {
        return CaseListBusinessModel::getSetting('unassigned', $search, $offset, $limit);
    }

    /**
     * Get unassigned Case List settings.
     * @url GET /:id/export
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     * @throws RestException
     */
    public function doExport(int $id)
    {
        try {
            $result = CaseListBusinessModel::export($id);
            G::streamFile($result['filename'], true, $result['downloadFilename']);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get unassigned Case List settings.
     * @url POST /import
     * @param array $request_data
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     * @throws RestException
     * @return array
     */
    public function doImport(array $request_data)
    {
        try {
            $ownerId = $this->getUserId();
            return CaseListBusinessModel::import($request_data, $ownerId);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get default columns associate to custom cases list.
     * @url GET /:type/default-columns
     * @param string $type
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     * @throws RestException
     * @return array
     */
    public function doGetDefaultColumns(string $type)
    {
        try {
            return CaseListBusinessModel::formattingColumns($type, '', []);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get report tables.
     * @url GET /report-tables
     * @param string $search
     * @access protected
     * @class AccessControl {@permission PM_CASES}
     * @throws RestException
     * @return array
     */
    public function doGetReportTables(string $search = '')
    {
        try {
            return CaseListBusinessModel::getReportTables($search);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }
}
