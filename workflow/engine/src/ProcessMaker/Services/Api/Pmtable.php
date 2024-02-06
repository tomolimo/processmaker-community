<?php

namespace ProcessMaker\Services\Api;

use Exception;
use Luracast\Restler\RestException;
use ProcessMaker\BusinessModel\Table as BusinessModelTable;
use ProcessMaker\Model\AdditionalTables;
use ProcessMaker\Services\Api;

/**
 * Pmtable Api Controller
 *
 * @protected
 */
class Pmtable extends Api
{
    /**
     * Get a list of the PM tables in the workspace. It does not include any Report Table
     *
     * @url GET
     * @status 200
     *
     * @param boolean $offline {@from path}
     *
     * @return array
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_LOGIN}
     * @link https://wiki.processmaker.com/3.1/REST_API_Administration/PM_Tables#PM_Tables_List:_GET_.2Fpmtable
     */
    public function doGetPmTables($offline = false)
    {
        try {
            if ($offline) {
                $response = AdditionalTables::getTablesOfflineStructure();
            } else {
                $pmTable = new BusinessModelTable();
                $response = $pmTable->getTables();
            }

            return $response;
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Get the data of the offline PM tables
     *
     * @url GET /offline/data
     * @status 200
     *
     * @param boolean $compress {@from path}
     *
     * @return array
     *
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_LOGIN}
     * @link https://wiki.processmaker.com/3.1/REST_API_Administration/PM_Tables#PM_Tables_List:_GET_.2Fpmtable
     */
    public function doGetPmTablesDataOffline($compress = true)
    {
        try {
            $data = AdditionalTables::getTablesOfflineData();
            if ($compress) {
                $json = json_encode($data);
                $compressed = gzcompress($json, 5);
                echo $compressed;
            } else {
                return $data;
            }
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Get the structure from a specific PM Table, including a list of its fields and their properties.
     *
     * @url GET /:pmt_uid
     * @status 200
     *
     * @param string $pmt_uid {@min 1} {@max 32}
     *
     * @return array
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_SETUP_PM_TABLES}
     * @link https://wiki.processmaker.com/3.1/REST_API_Administration/PM_Tables#Get_PM_Table_Structure:_GET_.2Fpmtable.2F.7Bpmt_uid.7D
     */
    public function doGetPmTable($pmt_uid)
    {
        try {
            $oPmTable = new BusinessModelTable();
            $response = $oPmTable->getTable($pmt_uid);
            return $response;
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Get the data from a PM table
     *
     * @url GET /:pmt_uid/data
     * @status 200
     *
     * @param string $pmt_uid {@min 1} {@max 32}
     * @param string $filter
     * @param string $q
     *
     * @return array
     * @throws RestException
     *
     * @access protected
     * @class  AccessControl {@permission PM_SETUP_PM_TABLES}
     *
     */
    public function doGetPmTableData($pmt_uid, $filter = null, $q = "")
    {
        try {
            $oPmTable = new BusinessModelTable();
            $response = $oPmTable->getTableData($pmt_uid, null, $filter, false, $q);
            return $response;
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Create a new PM Table
     *
     * @url POST
     * @status 201
     *
     * @param array $request_data
     * @param string $pmt_tab_name {@from body}
     * @param string $pmt_tab_dsc {@from body}
     *
     * @return array
     * @throws RestException
     *
     * @access protected
     * @class  AccessControl {@permission PM_SETUP_PM_TABLES}
     */
    public function doPostPmTable(
        $request_data,
        $pmt_tab_name,
        $pmt_tab_dsc = ''
    ) {
        try {
            $oReportTable = new BusinessModelTable();
            $response = $oReportTable->saveTable($request_data);
            if (isset($response['pro_uid'])) {
                unset($response['pro_uid']);
            }
            return $response;
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Create a new PM Table
     *
     * @url POST /pmtables
     * @status 201
     *
     * @param array $request_data
     *
     * @return array
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_SETUP_PM_TABLES}
     */
    public function doPostPmTables($request_data) 
    {
        try {
            $reportTable = new BusinessModelTable();
            $response = $reportTable->createPmTable($request_data);
            if (isset($response['pro_uid'])) {
                unset($response['pro_uid']);
            }
            return $response;
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Add a new record to a PM Table
     *
     * @url POST /:pmt_uid/data
     * @status 201
     *
     * @param string $pmt_uid {@min 1} {@max 32}
     * @param array $request_data
     *
     * @return array
     * @throws RestException
     * @access protected
     * @class  AccessControl {@permission PM_SETUP_PM_TABLES}
     */
    public function doPostPmTableData(
        $pmt_uid,
        $request_data
    ) {
        try {
            $oReportTable = new BusinessModelTable();
            $response = $oReportTable->saveTableData($pmt_uid, $request_data);
            return $response;
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Update the structure of a PM table.
     *
     * @url PUT /:pmt_uid
     * @status 200
     *
     * @param string $pmt_uid {@min 1} {@max 32}
     * @param array $request_data
     *
     * @return void
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_SETUP_PM_TABLES}
     */
    public function doPutPmTable(
        $pmt_uid,
        $request_data
    ) {
        try {
            $request_data['pmt_uid'] = $pmt_uid;
            $pmTable = new BusinessModelTable();
            $response = $pmTable->updateTable($request_data);
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Update the data of an existing record in a PM table.
     *
     * @url PUT /:pmt_uid/data
     * @status 200
     *
     * @param string $pmt_uid {@min 1} {@max 32}
     * @param array $request_data
     *
     * @return array
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_SETUP_PM_TABLES}
     */
    public function doPutPmTableData(
        $pmt_uid,
        $request_data
    ) {
        try {
            $oReportTable = new BusinessModelTable();
            $response = $oReportTable->updateTableData($pmt_uid, $request_data);
            return $response;
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Delete a specified PM table and all its data.
     *
     * @url DELETE /:pmt_uid
     * @status 200
     *
     * @param string $pmt_uid {@min 1} {@max 32}
     *
     * @return void
     * @throws RestException
     *
     * @access protected
     * @class  AccessControl {@permission PM_SETUP_PM_TABLES}
     */
    public function doDeletePmTable($pmt_uid)
    {
        try {
            $oReportTable = new BusinessModelTable();
            $response = $oReportTable->deleteTable($pmt_uid);
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }

    /**
     * Delete a record from a PM table, by specifying its primary key(s). The PM Table can have up to 3 primary key
     * fields.
     *
     * @url DELETE /:pmt_uid/data/:key1/:value1
     * @url DELETE /:pmt_uid/data/:key1/:value1/:key2/:value2
     * @url DELETE /:pmt_uid/data/:key1/:value1/:key2/:value2/:key3/:value3
     * @status 200
     *
     * @param string $pmt_uid {@min 1} {@max 32}
     * @param string $key1 {@min 1}
     * @param string $value1 {@min 1}
     * @param string $key2
     * @param string $value2
     * @param string $key3
     * @param string $value3
     *
     * @return array
     * @throws RestException
     *
     * @access protected
     * @class  AccessControl {@permission PM_SETUP_PM_TABLES}
     */
    public function doDeletePmTableData($pmt_uid, $key1, $value1, $key2 = '', $value2 = '', $key3 = '', $value3 = '')
    {
        try {
            $rows = array($key1 => $value1);
            if ($key2 != '') {
                $rows[$key2] = $value2;
            }
            if ($key3 != '') {
                $rows[$key3] = $value3;
            }
            $oReportTable = new BusinessModelTable();
            $response = $oReportTable->deleteTableData($pmt_uid, $rows);
            return $response;
        } catch (Exception $e) {
            throw (new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage()));
        }
    }
}

