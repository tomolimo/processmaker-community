<?php

namespace ProcessMaker\Model;

use App\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppTimeoutAction extends Model
{
    use HasFactory;

    protected $table = 'APP_TIMEOUT_ACTION_EXECUTED';
    // We do not have create/update timestamps for this table
    public $timestamps = false;
    // Filter by a specific case using case number
    private $caseUid = '';
    // Filter by a specific index
    private $index = 0;

    /**
     * Set Case Uid
     *
     * @param string $caseUid
     */
    public function setCaseUid($caseUid)
    {
        $this->caseUid = $caseUid;
    }

    /**
     * Get Case Uid
     *
     * @return string
     */
    public function getCaseUid()
    {
        return $this->caseUid;
    }

    /**
     * Set index
     *
     * @param int $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * Get index
     *
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Scope a query to get specific case uid
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param string $appUid
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCase($query, $appUid)
    {
        return $query->where('APP_UID', $appUid);
    }

    /**
     * Scope a query to get index
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param int $index
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIndex($query, $index)
    {
        return $query->where('DEL_INDEX', $index);
    }

    /**
     * Get the records related to the case and index if it was defined
     *
     * @return array
     */
    public function cases()
    {
        $query = AppTimeoutAction::query()->select();
        // Specific case uid
        if (!empty($this->getCaseUid())) {
            $query->case($this->getCaseUid());
        }
        // Specific index
        if (!empty($this->getIndex())) {
            $query->index($this->getIndex());
        }
        $results = $query->get()->toArray();

        return $results;
    }
}
