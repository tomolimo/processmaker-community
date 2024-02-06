<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use stdClass;

class UserConfig extends Model
{
    use HasFactory;

    /**
     * Bind table.
     * @var string
     */
    protected $table = 'USER_CONFIG';

    /**
     * Column timestamps.
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Get user setting.
     * @param int $id
     * @param string $name
     * @return mix array|null
     */
    public static function getSetting(int $id, string $name)
    {
        $userConfig = UserConfig::where('USR_ID', '=', $id)
            ->where('USC_NAME', '=', $name)
            ->first();
        if (empty($userConfig)) {
            return null;
        }
        $setting = json_decode($userConfig->USC_SETTING);
        if (empty($setting)) {
            $setting = new stdClass();
        }
        return [
            "id" => $userConfig->USR_ID,
            "name" => $userConfig->USC_NAME,
            "setting" => $setting
        ];
    }

    /**
     * Add user setting.
     * @param int $id
     * @param string $name
     * @param array $setting
     * @return mix array|null
     */
    public static function addSetting(int $id, string $name, array $setting)
    {
        $model = new UserConfig();
        $model->USR_ID = $id;
        $model->USC_NAME = $name;
        $model->USC_SETTING = json_encode($setting);
        $model->save();
        $userConfig = UserConfig::getSetting($id, $name);
        return $userConfig;
    }

    /**
     * Edit user setting.
     * @param int $id
     * @param string $name
     * @param array $setting
     * @return mix array|null
     */
    public static function editSetting(int $id, string $name, array $setting)
    {
        UserConfig::where('USR_ID', '=', $id)
            ->where('USC_NAME', '=', $name)
            ->update(["USC_SETTING" => json_encode($setting)]);

        return UserConfig::getSetting($id, $name);
    }

    /**
     * Delete user setting.
     * @param int $id
     * @param string $name
     * @return mix array|null
     */
    public static function deleteSetting(int $id, string $name)
    {
        $userConfig = UserConfig::getSetting($id, $name);
        UserConfig::where('USR_ID', '=', $id)
            ->where('USC_NAME', '=', $name)
            ->delete();
        return $userConfig;
    }

    /**
     * This updates the filter settings on custom case list.
     * @param string $id
     * @param array $caseList
     */
    public static function updateUserConfig(string $id, array $caseList)
    {
        //get columns deactivates
        $columnsDisableFilter = [];
        if (isset($caseList['columns'])) {
            foreach ($caseList['columns'] as $column) {
                if ($column->enableFilter === false) {
                    $columnsDisableFilter[] = $column;
                }
            }
        }
        //process all custom configuration
        $name = 'userConfig';
        $usersConfig = UserConfig::select(['USR_ID', 'USC_SETTING'])
            ->where('USC_NAME', '=', $name)
            ->get();
        foreach ($usersConfig as $value) {
            if (empty($value->USC_SETTING)) {
                continue;
            }
            $lists = json_decode($value->USC_SETTING);
            foreach ($lists as &$list) {
                if (!property_exists($list, 'customCaseList')) {
                    continue;
                }
                foreach ($list->customCaseList as $key => &$item) {
                    if (intval($key) !== intval($id)) {
                        continue;
                    }
                    if (!property_exists($item, 'filters')) {
                        continue;
                    }
                    if (!is_array($item->filters)) {
                        continue;
                    }
                    $i = count($item->filters) - 1;
                    while ($i >= 0) {
                        if (isset($item->filters[$i])) {
                            foreach ($columnsDisableFilter as $column) {
                                if ($item->filters[$i]->fieldId === $column->field) {
                                    unset($item->filters[$i]);
                                    //reindex array keys
                                    $item->filters = array_values($item->filters);
                                }
                            }
                        }
                        $i--;
                    }
                }
            }
            //update database
            $lists = (array) $lists;
            UserConfig::editSetting($value->USR_ID, $name, $lists);
        }
    }
}
