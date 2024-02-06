<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppThread extends Model
{
    use HasFactory;

    protected $table = 'APP_THREAD';
    // We do not have create/update timestamps for this table
    public $timestamps = false;

    /**
     * Scope a query to filter a specific case
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $appUid
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAppUid($query, string $appUid)
    {
        return $query->where('APP_UID', $appUid);
    }

    /**
     * Scope a query to filter a specific index
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  int $index
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIndex($query, int $index)
    {
        return $query->where('DEL_INDEX', $index);
    }

    /**
     * Get thread related to the specific case and index
     *
     * @param  string $appUid
     * @param  int $index
     *
     * @return array
     */
    public static function getThread(string $appUid, int $index)
    {
        $query = AppThread::query()->select(['APP_THREAD_INDEX']);
        $query->appUid($appUid);
        $query->index($index);
        $results = $query->get()->toArray();

        return $results;
    }
}