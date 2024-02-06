<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Exception;
use G;
use ProcessMaker\BusinessModel\Table;
use ProcessMaker\Core\System;
use ProcessMaker\Model\AdditionalTables;
use ProcessMaker\Model\Fields;
use ProcessMaker\Model\User;
use Illuminate\Database\Eloquent\Model;

class CaseList extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'CASE_LIST';

    /**
     * The primary key for the model.
     * @var string
     */
    protected $primaryKey = 'CAL_ID';

    /**
     * Indicates if the IDs are auto-incrementing.
     * @var bool
     */
    public $incrementing = true;

    /**
     * Indicates if the model should be timestamped.
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     * @var array
     */
    protected $guarded = [];

    /**
     * Represents the column aliases.
     * @var array
     */
    private static $columnAliases = [
        'CAL_ID' => 'id',
        'CAL_TYPE' => 'type',
        'CAL_NAME' => 'name',
        'CAL_DESCRIPTION' => 'description',
        'ADD_TAB_UID' => 'tableUid',
        'CAL_COLUMNS' => 'columns',
        'USR_ID' => 'userId',
        'CAL_ICON_LIST' => 'iconList',
        'CAL_ICON_COLOR' => 'iconColor',
        'CAL_ICON_COLOR_SCREEN' => 'iconColorScreen',
        'CAL_CREATE_DATE' => 'createDate',
        'CAL_UPDATE_DATE' => 'updateDate',
        'USR_USERNAME' => 'userName',
        'USR_FIRSTNAME' => 'userFirstname',
        'USR_LASTNAME' => 'userLastname',
        'USR_EMAIL' => 'userEmail',
        'USR_POSITION' => 'userPosition',
        'ADD_TAB_NAME' => 'tableName',
        'PRO_TITLE' => 'process'
    ];

    /**
     * Represents the columns exclude from report table.
     * @var array
     */
    public static $excludeColumns = ['APP_UID', 'APP_NUMBER', 'APP_STATUS'];

    /**
     * Get case list
     *
     * @param string $id
     * @param string $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getCaseList($id, $type)
    {
        $caseList = CaseList::where('CAL_ID', '=', $id)
            ->where('CAL_TYPE', '=', $type)
            ->leftJoin('ADDITIONAL_TABLES', 'ADDITIONAL_TABLES.ADD_TAB_UID', '=', 'CASE_LIST.ADD_TAB_UID')
            ->select([
                'CASE_LIST.*',
                'ADDITIONAL_TABLES.ADD_TAB_NAME',
                'ADDITIONAL_TABLES.PRO_UID'
            ])
            ->first();

        return $caseList;
    }

    /**
     * Get column name from alias.
     * @param array $array
     * @return array
     */
    public static function getColumnNameFromAlias(array $array): array
    {
        foreach (self::$columnAliases as $key => $value) {
            if (array_key_exists($value, $array)) {
                $array[$key] = $array[$value];
                unset($array[$value]);
            }
        }
        return $array;
    }

    /**
     * Get alias from column name.
     * @param array $array
     * @return array
     */
    public static function getAliasFromColumnName(array $array)
    {
        foreach (self::$columnAliases as $key => $value) {
            if (array_key_exists($key, $array)) {
                $array[$value] = $array[$key];
                unset($array[$key]);
            }
        }
        return $array;
    }

    /**
     * Create and save this model from array values.
     * @param array $values
     * @param int $ownerId
     * @return object
     */
    public static function createSetting(array $values, int $ownerId)
    {
        $attributes = CaseList::getColumnNameFromAlias($values);

        $attributes['USR_ID'] = $ownerId;
        $attributes['CAL_CREATE_DATE'] = date("Y-m-d H:i:s");
        $attributes['CAL_UPDATE_DATE'] = date("Y-m-d H:i:s");
        if (empty($attributes['CAL_COLUMNS'])) {
            $attributes['CAL_COLUMNS'] = [];
        }
        $attributes['CAL_COLUMNS'] = json_encode($attributes['CAL_COLUMNS']);

        $model = CaseList::create($attributes);
        $model->CAL_COLUMNS = json_decode($model->CAL_COLUMNS);
        return $model;
    }

    /**
     * Update and save this model from array values.
     * @param int $id
     * @param array $values
     * @param int $ownerId
     * @return object
     */
    public static function updateSetting(int $id, array $values, int $ownerId)
    {
        $attributes = CaseList::getColumnNameFromAlias($values);

        $attributes['USR_ID'] = $ownerId;
        $attributes['CAL_UPDATE_DATE'] = date("Y-m-d H:i:s");
        if (empty($attributes['CAL_COLUMNS'])) {
            $attributes['CAL_COLUMNS'] = [];
        }
        $attributes['CAL_COLUMNS'] = json_encode($attributes['CAL_COLUMNS']);

        self::checkColumnsConfigurationChanges($id, $attributes['CAL_TYPE'], $attributes['CAL_COLUMNS']);

        $caseList = CaseList::where('CAL_ID', '=', $id);
        $caseList->update($attributes);
        $model = $caseList->first();
        if (!is_null($model)) {
            $model->CAL_COLUMNS = json_decode($model->CAL_COLUMNS);
        }
        return $model;
    }

    /**
     * Check if the columns configuration has changed.
     * @param int $calId
     * @param string $type
     * @param string $newColumns
     * @return void
     */
    private static function checkColumnsConfigurationChanges(int $calId, string $type, string $newColumns): void
    {
        $caseList = CaseList::where('CAL_ID', '=', $calId)->first();
        if ($caseList->CAL_COLUMNS === $newColumns) {
            return;
        }

        $listUserConfig = UserConfig::where('USC_NAME', '=', 'userConfig')
            ->select()
            ->get();
        foreach ($listUserConfig as $userConfig) {
            if (empty($userConfig->USC_SETTING)) {
                continue;
            }
            $uscSetting = json_decode($userConfig->USC_SETTING);
            if (!property_exists($uscSetting, $type)) {
                continue;
            }
            if (!property_exists($uscSetting->{$type}, 'customCaseList')) {
                continue;
            }
            if (!property_exists($uscSetting->{$type}->customCaseList, $calId)) {
                continue;
            }
            unset($uscSetting->{$type}->customCaseList->{$calId});
            UserConfig::editSetting($userConfig->USR_ID, 'userConfig', (array) $uscSetting);
        }
    }

    /**
     * Delete this model.
     * @param int $id
     * @return object
     */
    public static function deleteSetting(int $id)
    {
        $caseList = CaseList::where('CAL_ID', '=', $id);
        $model = $caseList->first();
        if (!is_null($model)) {
            $caseList->delete();
            $model->CAL_COLUMNS = json_decode($model->CAL_COLUMNS);
        }
        return $model;
    }

    /**
     * Get the array of the elements of this model, this method supports the filter by: 
     * name, description, user name, first user name, second user name, user email. 
     * The result is returned based on the delimiters to allow pagination and the total 
     * of the existing models.
     * @param string $type
     * @param string $search
     * @param int $offset
     * @param int $limit
     * @param bool $paged
     * @return array
     */
    public static function getSetting(string $type, string $search, int $offset, int $limit, bool $paged = true): array
    {
        $order = 'asc';
        $model = CaseList::where('CAL_TYPE', '=', $type)
            ->leftJoin('USERS', 'USERS.USR_ID', '=', 'CASE_LIST.USR_ID')
            ->leftJoin('ADDITIONAL_TABLES', 'ADDITIONAL_TABLES.ADD_TAB_UID', '=', 'CASE_LIST.ADD_TAB_UID')
            ->leftJoin('PROCESS', 'PROCESS.PRO_UID', '=', 'ADDITIONAL_TABLES.PRO_UID')
            ->select([
                'CASE_LIST.*',
                'PROCESS.PRO_TITLE',
                'ADDITIONAL_TABLES.ADD_TAB_NAME',
                'USERS.USR_UID', 'USERS.USR_USERNAME', 'USERS.USR_FIRSTNAME', 'USERS.USR_LASTNAME', 'USERS.USR_EMAIL', 'USERS.USR_POSITION'
            ])
            ->where(function ($query) use ($search) {
                $query
                ->orWhere('CASE_LIST.CAL_NAME', 'like', '%' . $search . '%')
                ->orWhere('PROCESS.PRO_TITLE', 'like', '%' . $search . '%')
                ->orWhere('ADDITIONAL_TABLES.ADD_TAB_NAME', 'like', '%' . $search . '%');
            })
            ->orderBy('CASE_LIST.CAL_NAME', $order);

        $count = $model->count();

        if ($paged === true) {
            $model->offset($offset)->limit($limit);
        }
        $data = $model->get();

        $data->transform(function ($item, $key) {
            if (is_null($item->CAL_COLUMNS)) {
                $item->CAL_COLUMNS = '[]';
            }

            $result = CaseList::getAliasFromColumnName($item->toArray());

            $columns = json_decode($result['columns']);
            $columns = CaseList::formattingColumns($result['type'], $result['tableUid'], $columns);

            $result['columns'] = $columns;
            $result['userAvatar'] = System::getServerMainPath() . '/users/users_ViewPhotoGrid?pUID=' . $result['USR_UID'] . '&h=' . microtime(true);
            unset($result['USR_UID']);

            return $result;
        });

        return [
            'total' => $count,
            'data' => $data
        ];
    }

    /**
     * The export creates a temporary file with record data in json format.
     * @param int $id
     * @throws Exception
     */
    public static function export(int $id)
    {
        $model = CaseList::where('CAL_ID', '=', $id)
            ->leftJoin('USERS', 'USERS.USR_ID', '=', 'CASE_LIST.USR_ID')
            ->leftJoin('ADDITIONAL_TABLES', 'ADDITIONAL_TABLES.ADD_TAB_UID', '=', 'CASE_LIST.ADD_TAB_UID')
            ->select([
                'CASE_LIST.*',
                'ADDITIONAL_TABLES.ADD_TAB_NAME'
            ])
            ->first();
        if (empty($model)) {
            throw new Exception(G::LoadTranslation('ID_DOES_NOT_EXIST'));
        }

        $result = CaseList::getAliasFromColumnName($model->toArray());
        $result['columns'] = json_decode($result['columns']);
        $result['tableName'] = $model->ADD_TAB_NAME;

        //clean invalid items
        unset($result['id']);
        unset($result['userId']);
        unset($result['createDate']);
        unset($result['updateDate']);

        //random name to distinguish the different sessions
        $filename = sys_get_temp_dir() . "/pm" . random_int(10000, 99999);
        file_put_contents($filename, json_encode($result));
        return [
            'filename' => $filename,
            'downloadFilename' => $result['name'] . ' ' . date('Y-m-d H:i:s') . '.json',
            'data' => $result
        ];
    }

    /**
     * The import requires a $ _FILES content in json format to create a record.
     * @param array $requestData
     * @param int $ownerId
     * @return array
     * @throws Exception
     */
    public static function import(array $requestData, int $ownerId)
    {
        if ($_FILES['file_content']['error'] !== UPLOAD_ERR_OK ||
            $_FILES['file_content']['tmp_name'] === '') {
            throw new Exception(G::LoadTranslation('ID_ERROR_UPLOAD_FILE_CONTACT_ADMINISTRATOR'));
        }
        $content = file_get_contents($_FILES['file_content']['tmp_name']);
        try {
            // Check if the content is a binary string and convert to a string
            if (preg_match('~[^\x20-\x7E\t\r\n]~', $content) > 0) {
                $content = mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
            }
            $array = json_decode($content, true);

            $tableName = $array['tableName'];
            unset($array['tableName']);

            //the pmtable not exist
            $table = AdditionalTables::where('ADD_TAB_NAME', '=', $tableName)
                ->first();
            if ($table === null) {
                return [
                    'status' => 'tableNotExist',
                    'message' => G::LoadTranslation('ID_CASELIST_CAN_NOT_BE_IMPORTED_THE_PMTABLE_NOT_EXIST', [$_FILES['file_content']['name']])
                ];
            }
            $array['tableUid'] = $table->ADD_TAB_UID;

            //the fields have differences between the import file and the current table
            $requestData['invalidFields'] = $requestData['invalidFields'] ?? '';
            if ($requestData['invalidFields'] !== 'continue') {
                $fields = [];
                $columns = CaseList::formattingColumns($array['type'], $array['tableUid'], []);
                foreach ($columns as $column) {
                    $fields[] = $column['field'];
                }
                foreach ($array['columns'] as $value) {
                    if (!in_array($value['field'], $fields)) {
                        return [
                            'status' => 'invalidFields',
                            'message' => G::LoadTranslation('ID_PMTABLE_NOT_HAVE_ALL_CASELIST_FIELDS_WOULD_YOU_LIKE_CONTINUE', [$tableName, $_FILES['file_content']['name']])
                        ];
                    }
                }
            }

            //the name of the case list already exist
            $list = CaseList::where('CAL_NAME', '=', $array['name'])
                ->first();
            $requestData['duplicateName'] = $requestData['duplicateName'] ?? '';
            if ($requestData['duplicateName'] !== 'continue') {
                if ($list !== null) {
                    return [
                        'status' => 'duplicateName',
                        'message' => G::LoadTranslation('ID_IMPORTING_CASELIST_WITH_THE_SAME_NAME_SELECT_OPTION', [$array['name']])
                    ];
                }
            }

            if ($requestData['duplicateName'] === 'continue' && $list !== null) {
                $caseList = CaseList::updateSetting($list->CAL_ID, $array, $ownerId);
            } else {
                $caseList = CaseList::createSetting($array, $ownerId);
            }

            $result = CaseList::getAliasFromColumnName($caseList->toArray());
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Formatting columns from minimal stored columns configuration in custom cases list.
     * @param string $type
     * @param string $tableUid
     * @param array $storedColumns
     * @return array
     */
    public static function formattingColumns(string $type = 'inbox', string $tableUid = '', array $storedColumns = [])
    {
        $default = [
            [
                'list' => ['inbox', 'draft', 'paused', 'unassigned'],
                'field' => 'case_number',
                'idFilter' => 'case_number',
                'name' => G::LoadTranslation('ID_MYCASE_NUMBER'),
                'type' => 'integer',
                'source' => 'APPLICATION',
                'typeSearch' => 'integer range',
                'enableFilter' => false,
                'set' => true
            ], [
                'list' => ['inbox', 'draft', 'paused', 'unassigned'],
                'field' => 'case_title',
                'idFilter' => 'caseTitle',
                'name' => G::LoadTranslation('ID_CASE_THREAD_TITLE'),
                'type' => 'string',
                'source' => 'APPLICATION',
                'typeSearch' => 'search text',
                'enableFilter' => false,
                'set' => true
            ], [
                'list' => ['inbox', 'draft', 'paused', 'unassigned'],
                'field' => 'process_category',
                'idFilter' => 'process_category',
                'name' => G::LoadTranslation('ID_PROCESS_CATEGORY'),
                'type' => 'string',
                'source' => 'APPLICATION',
                'typeSearch' => 'search text',
                'enableFilter' => false,
                'set' => true
            ], [
                'list' => ['inbox', 'draft', 'paused', 'unassigned'],
                'field' => 'process_name',
                'idFilter' => 'process_name',
                'name' => G::LoadTranslation('ID_PROCESS_NAME'),
                'type' => 'string',
                'source' => 'APPLICATION',
                'typeSearch' => 'search text',
                'enableFilter' => false,
                'set' => true
            ], [
                'list' => ['inbox', 'draft', 'paused', 'unassigned'],
                'field' => 'task',
                'idFilter' => 'task',
                'name' => G::LoadTranslation('ID_TASK'),
                'type' => 'string',
                'source' => 'APPLICATION',
                'typeSearch' => 'search text',
                'enableFilter' => false,
                'set' => true
            ], [
                'list' => ['inbox', 'draft', 'paused', 'unassigned'],
                'field' => 'send_by',
                'idFilter' => 'send_by',
                'name' => G::LoadTranslation('ID_SEND_BY'),
                'type' => 'string',
                'source' => 'APPLICATION',
                'typeSearch' => 'search text',
                'enableFilter' => false,
                'set' => true
            ], [
                'list' => ['inbox', 'paused', 'unassigned'],
                'field' => 'due_date',
                'idFilter' => 'due_date',
                'name' => G::LoadTranslation('ID_DUE_DATE'),
                'type' => 'date',
                'source' => 'APPLICATION',
                'typeSearch' => 'date range',
                'enableFilter' => false,
                'set' => true
            ], [
                'list' => ['inbox', 'paused', 'unassigned'],
                'field' => 'delegation_date',
                'idFilter' => 'delegation_date',
                'name' => G::LoadTranslation('ID_DELEGATION_DATE'),
                'type' => 'date',
                'source' => 'APPLICATION',
                'typeSearch' => 'date range',
                'enableFilter' => false,
                'set' => true
            ], [
                'list' => ['inbox', 'draft', 'paused', 'unassigned'],
                'field' => 'priority',
                'idFilter' => 'priority',
                'name' => G::LoadTranslation('ID_PRIORITY'),
                'type' => 'string',
                'source' => 'APPLICATION',
                'typeSearch' => 'option',
                'enableFilter' => false,
                'set' => true
            ],
        ];

        //filter by type
        $result = [];
        foreach ($default as &$column) {
            if (in_array($type, $column['list'])) {
                unset($column['list']);
                $result[] = $column;
            }
        }
        $default = $result;

        //get additional tables
        $additionalTables = AdditionalTables::where('ADD_TAB_UID', '=', $tableUid)
            ->where('PRO_UID', '<>', '')
            ->whereNotNull('PRO_UID')
            ->get();
        $additionalTables->transform(function ($object) {
            $table = new Table();
            return $table->getTable($object->ADD_TAB_UID, $object->PRO_UID, true, false);
        });
        $result = $additionalTables->toArray();
        if (!empty($result)) {
            $result = $result[0];
            if (isset($result['fields'])) {
                foreach ($result['fields'] as $column) {
                    if (in_array($column['fld_name'], self::$excludeColumns)) {
                        continue;
                    }
                    $default[] = [
                        'field' => $column['fld_name'],
                        'name' => $column['fld_description'],
                        'type' => $column['fld_type'],
                        'source' => $result['rep_tab_name'],
                        'typeSearch' => 'search text',
                        'enableFilter' => false,
                        'set' => false
                    ];
                }
            }
        }

        //merge with stored information
        $result = [];
        foreach ($default as &$column) {
            foreach ($storedColumns as $keyStoredColumn => $storedColumn) {
                if (!is_object($storedColumn)) {
                    continue;
                }
                $storedColumn = (array) $storedColumn;
                if (!isset($storedColumn['field'])) {
                    continue;
                }
                if ($column['field'] === $storedColumn['field']) {
                    if (isset($storedColumn['enableFilter'])) {
                        $column['enableFilter'] = $storedColumn['enableFilter'];
                    }
                    if (isset($storedColumn['set'])) {
                        $column['set'] = $storedColumn['set'];
                    }
                    //for column ordering, this will be removed later
                    $column['sortIndex'] = $keyStoredColumn;
                    break;
                }
            }
            $result[] = $column;
        }

        //sort columns by 'sortIndex', then 'sortIndex' will be removed.
        $n = count($result);
        usort($result, function ($a, $b) use ($n) {
            $a1 = isset($a['sortIndex']) ? $a['sortIndex'] : $n;
            $b1 = isset($b['sortIndex']) ? $b['sortIndex'] : $n;
            return $a1 - $b1;
        });
        foreach ($result as &$value) {
            unset($value['sortIndex']);
        }

        return $result;
    }

    /**
     * Get the report tables, this can filter the results by the search parameter.
     * @param string $search
     * @return array
     */
    public static function getReportTables(string $search = '')
    {
        $additionalTables = AdditionalTables::where('ADD_TAB_NAME', 'LIKE', "%{$search}%")
            ->where('PRO_UID', '<>', '')
            ->whereNotNull('PRO_UID')
            ->get();
        $additionalTables->transform(function ($object) {
            $table = new Table();
            $result = $table->getTable($object->ADD_TAB_UID, $object->PRO_UID, true, false);
            $fields = [];
            if (isset($result['fields'])) {
                foreach ($result['fields'] as $column) {
                    if (in_array($column['fld_name'], self::$excludeColumns)) {
                        continue;
                    }
                    $fields[] = [
                        'field' => $column['fld_name'],
                        'name' => $column['fld_description'],
                        'type' => $column['fld_type'],
                        'source' => $result['rep_tab_name'],
                        'typeSearch' => 'search text',
                        'enableFilter' => false,
                        'set' => false
                    ];
                }
            }
            $format = [
                'uid' => $result['rep_uid'],
                'name' => $result['rep_tab_name'],
                'description' => $result['rep_tab_description'],
                'fields' => $fields
            ];
            return $format;
        });
        $result = $additionalTables->toArray();

        return $result;
    }
}
