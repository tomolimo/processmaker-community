<?php

namespace ProcessMaker\Services\Api;

use Exception;
use Luracast\Restler\RestException;
use PMLicensedFeatures;
use ProcessMaker\BusinessModel\Cases\CasesList;
use ProcessMaker\BusinessModel\DataBaseConnection;
use ProcessMaker\BusinessModel\Language;
use ProcessMaker\BusinessModel\Skins;
use ProcessMaker\Services\Api;

/**
 * System class
 */
class System extends Api
{
    /**
     * @url GET /db-engines
     * @status 200
     *
     * @return array
     *
     * @protected
     */
    public function doGetDataBaseEngines()
    {
        try {
            $dbConnection = new DataBaseConnection();
            $response = $dbConnection->getDbEngines();

            return $response;
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Get counter for all lists
     *
     * @url GET /counters-lists
     * @status 200
     *
     * @return array
     *
     * @protected
     */
    public function doGetCountersLists()
    {
        try {
            $usrUid = $this->getUserId();
            $count = new CasesList();
            $result = $count->getAllCounters($usrUid, true);

            return $result;
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Get a list of the installed languages.
     *
     * @url GET /languages
     * @status 200
     *
     * @return array
     *
     * @public
     * @category HOR-3209,PROD-181
     */
    public function doGetLanguages()
    {
        try {
            $language = new Language;
            $list = $language->getLanguageList();

            return ["data" => $list];
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     *
     * @url GET /enabled-features
     * @status 200
     *
     * @return array
     *
     * @protected
     */
    public function doGetEnabledFeatures()
    {
        try {
            $enabledFeatures = [];

            return $enabledFeatures;
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Get the list of installed skins.
     *
     * @url GET /skins
     * @status 200
     *
     * @return array
     * @access protected
     * @class  AccessControl {@permission PM_FACTORY}
     * @protected
     */
    public function doGetSkins()
    {
        try {
            $model = new Skins();
            $response = $model->getSkins();

            return ["data" => $response];
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }
}
