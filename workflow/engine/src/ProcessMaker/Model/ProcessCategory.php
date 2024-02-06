<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProcessCategory
 * @package ProcessMaker\Model
 *
 * Represents a process category object in the system.
 */
class ProcessCategory extends Model
{
    use HasFactory;

    // Set our table name
    protected $table = 'PROCESS_CATEGORY';

    public $timestamps = false;

    /**
     * Scope a query to specific category id
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param int $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('CATEGORY_ID', $category);
    }

    /**
     * Scope a query to specific category name
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $name
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCategoryName($query, $name)
    {
        return $query->where('CATEGORY_NAME', 'LIKE', "%{$name}%");
    }

    /**
     * Get the categories
     *
     * @param string $dir
     *
     * @return array
     *
     * @see ProcessProxy::categoriesList()
     * @link https://wiki.processmaker.com/3.0/Process_Categories
     */
    public static function getCategories($dir = 'ASC')
    {
        $query = ProcessCategory::query()
            ->select([
                'CATEGORY_UID',
                'CATEGORY_NAME'
            ])
            ->orderBy('CATEGORY_NAME', $dir);

        return $query->get()->values()->toArray();
    }

    /**
     * Get the process categories
     *
     * @param string $name
     * @param int $start
     * @param int $limit
     *
     * @return array
     *
     * @see ProcessMaker\Services\Api\Home::getCategories()
     */
    public static function getProcessCategories($name = null, $start = null, $limit = null)
    {
        $query = ProcessCategory::query()->select(['CATEGORY_ID', 'CATEGORY_NAME']);

        if (!is_null($name)) {
            $query->categoryName($name);
        }

        if (!is_null($start) && !is_null($limit)) {
            $query->offset($start)->limit($limit);
        }

        return $query->get()->toArray();
    }

    /**
     * Get category Id
     * 
     * @param string $categoryUid
     * 
     * @return int
     */
    public static function getCategoryId($categoryUid)
    {
        $query = ProcessCategory::query()->select(['CATEGORY_ID']);
        $query->where('CATEGORY_UID', $categoryUid);
        if ($query->first()) {
            return $query->first()->CATEGORY_ID;
        }
    }

    /**
     * Get category name
     * 
     * @param int $category
     * 
     * @return string
     */
    public static function getCategory(int $category)
    {
        $query = ProcessCategory::query()->select(['CATEGORY_NAME']);
        $query->category($category);
        if ($query->first()) {
            return $query->first()->CATEGORY_NAME;
        } else {
            return '';
        }
    }
}
