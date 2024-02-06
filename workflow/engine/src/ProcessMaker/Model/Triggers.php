<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Triggers extends Model
{
    use HasFactory;

    // Set our table name
    protected $table = 'TRIGGERS';
    // No timestamps
    public $timestamps = false;
    // Primary key
    protected $primaryKey = 'TRI_UID';
    // No incrementing
    public $incrementing = false;

    // Filter by a specific uid
    private $triUid = '';

    /**
     * Set trigger uid
     *
     * @param string $triUid
     */
    public function setTrigger($triUid)
    {
        $this->triUid = $triUid;
    }

    /**
     * Get trigger uid
     *
     * @return int
     */
    public function getTrigger()
    {
        return $this->triUid;
    }

    /**
     * Scope a query to filter an specific process
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcess($query, string $proUid)
    {
        return $query->where('PRO_UID', $proUid);
    }

    /**
     * Scope a query to filter an specific trigger
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $triUid
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTrigger($query, string $triUid)
    {
        return $query->where('TRI_UID', $triUid);
    }

    /**
     * Get the records
     *
     * @return array
     */
    public function triggers()
    {
        $query = Triggers::query()->select();
        // Specific trigger
        if (!empty($this->getTrigger())) {
            $query->trigger($this->getTrigger());
        }
        $results = $query->get()->toArray();

        return $results;
    }
}