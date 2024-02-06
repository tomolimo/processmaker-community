<?php
namespace ProcessMaker\Services\Api\Project;

use Exception;
use G;
use Luracast\Restler\RestException;
use ProcessMaker\BusinessModel\Variable as BmVariable;
use ProcessMaker\Services\Api;

/**
 * Project\Variable Api Controller
 *
 * @protected
 */
class Variable extends Api
{
    /**
     * @url GET /:prj_uid/process-variables
     *
     * @param string $prj_uid {@min 32}{@max 32}
     * 
     * @return array
     * @throws RestException
     * 
     * @access protected
     * @class AccessControl {@permission PM_FACTORY}
     */
    public function doGetVariables($prj_uid)
    {
        try {
            $variable = new BmVariable();
            $response = $variable->getVariables($prj_uid);

            return $response;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Get variables by type
     * 
     * @url GET /:prj_uid/process-variables/:typeVariable/paged
     *
     * @param string $prj_uid {@min 32}{@max 32}
     * @param string $typeVariable {@from path}
     * @param int $start {@from path}
     * @param int $limit {@from path}
     * @param string $search {@from path}
     * 
     * @return array
     * @throws RestException
     * 
     * @access protected
     * @class AccessControl {@permission PM_FACTORY}
     */
    public function doGetVariablesByType($prj_uid, $typeVariable, $start = null, $limit = null, $search = null)
    {
        try {
            $variable = new BmVariable();
            $typesAccepted = $variable::$varTypesValues;
            if (!empty($typesAccepted[$typeVariable])) {
                $typeVatId = $typesAccepted[$typeVariable];
            } else {
                throw new Exception(G::LoadTranslation("ID_INVALID_VALUE_ONLY_ACCEPTS_VALUES", ['$typeVariable', implode(',', $variable->getVariableTypes())]));
            }
            // Review if the word has the prefix
            $count = preg_match_all('/\@(?:([\@\%\#\?\$\=\&Qq\!])|([a-zA-Z\_][\w\-\>\:]*)\(((?:[^\\\\\)]*(?:[\\\\][\w\W])?)*)\))((?:\s*\[[\'"]?\w+[\'"]?\])+|\-\>([a-zA-Z\_]\w*))?/', $search, $match, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);
            // Check if the search has some prefix
            $prefix = '';
            if ($count) {
                $prefix = substr($search,0,2);
                $search = substr($search,2);
            }
            $response = $variable->getVariablesByType($prj_uid, $typeVatId, $start, $limit, $search, $prefix);

            return $response;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * @url GET /:prj_uid/process-variable/:var_uid
     *
     * @param string $var_uid {@min 32}{@max 32}
     * @param string $prj_uid {@min 32}{@max 32}
     * 
     * @return array
     * @throws RestException
     * 
     * @access protected
     * @class AccessControl {@permission PM_FACTORY}
     */
    public function doGetVariable($var_uid, $prj_uid)
    {
        try {
            $variable = new BmVariable();
            $response = $variable->getVariable($prj_uid, $var_uid);

            return $response;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Create a process variable.
     * 
     * @url POST /:prj_uid/process-variable
     * @status 201
     * 
     * @param string $prj_uid      {@min 32}{@max 32}
     * @param array  $request_data
     * 
     * @return array
     * @throws RestException
     * 
     * @access protected
     * @class AccessControl {@permission PM_FACTORY}
     */
    public function doPostVariable($prj_uid, $request_data)
    {
        try {
            $request_data = (array)($request_data);
            $variable = new BmVariable();
            $arrayData = $variable->create($prj_uid, $request_data);
            $response = $arrayData;

            return $response;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Update variable.
     *
     * @url PUT /:prj_uid/process-variable/:var_uid
     *
     * @param string $prj_uid      {@min 32}{@max 32}
     * @param string $var_uid      {@min 32}{@max 32}
     * @param array  $request_data
     *
     * @throws RestException
     *
     * @access protected
     * @class AccessControl {@permission PM_FACTORY}
     */
    public function doPutVariable($prj_uid, $var_uid, array $request_data)
    {
        try {
            $request_data = (array)($request_data);
            $variable = new BmVariable();
            $variable->update($prj_uid, $var_uid, $request_data);

        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * @url DELETE /:prj_uid/process-variable/:var_uid
     *
     * @param string $prj_uid {@min 32}{@max 32}
     * @param string $var_uid {@min 32}{@max 32}
     * 
     * @throws RestException
     * 
     * @access protected
     * @class AccessControl {@permission PM_FACTORY}
     */
    public function doDeleteVariable($prj_uid, $var_uid)
    {
        try {
            $variable = new BmVariable();
            $variable->delete($prj_uid, $var_uid);
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Executes an SQL query of a dependent field, such as a dropdown box, checkgroup 
     * or radiogroup, that uses an SQL query with one or more dynamic variables 
     * to populate its list of options.
     * 
     * @url POST /:prj_uid/process-variable/:var_name/execute-query
     * 
     * @param string $prj_uid      {@min 32}{@max 32}
     * @param string $var_name
     * @param array  $request_data
     * 
     * @return array
     * @throws RestException
     * 
     * @access protected
     * @class AccessControl {@permission PM_FACTORY, PM_CASES}
     */
    public function doPostVariableExecuteSql($prj_uid, $var_name = '', $request_data = array())
    {
        try {
            $variable = new BmVariable();
            $arrayData = ($request_data != null)? $variable->executeSql($prj_uid, $var_name, $request_data) : $variable->executeSql($prj_uid, $var_name);
            $response = $arrayData;

            return $response;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }

    /**
     * Gets the options in a suggest box, dropdown box, checkgroup or radiogroup, 
     * which uses an SQL query to populate its list of options (or uses a datasource 
     * which is "array variable" in version 3.0.1.8 or later).
     * 
     * @url POST /:prj_uid/process-variable/:var_name/execute-query-suggest
     * 
     * @param string $prj_uid      {@min 32}{@max 32}
     * @param string $var_name
     * @param array  $request_data
     * 
     * @return array
     * @throws RestException
     * 
     * @access protected
     * @class AccessControl {@permission PM_FACTORY, PM_CASES}
     */
    public function doPostVariableExecuteSqlSuggest($prj_uid, $var_name, $request_data)
    {
        try {
            $variable = new BmVariable();
            $arrayData = ($request_data != null)? $variable->executeSqlSuggest($prj_uid, $var_name, $request_data) : $variable->executeSqlSuggest($prj_uid, $var_name);
            $response = $arrayData;

            return $response;
        } catch (Exception $e) {
            throw new RestException(Api::STAT_APP_EXCEPTION, $e->getMessage());
        }
    }
}

