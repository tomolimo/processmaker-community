<?php

namespace ProcessMaker\Model;

use G;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'TASK';
    protected $primaryKey = 'TAS_ID';
    // We do not have create/update timestamps for this table
    public $timestamps = false;

    public function process()
    {
        return $this->belongsTo(Process::class, 'PRO_UID', 'PRO_UID');
    }

    public function delegations()
    {
        return $this->hasMany(Delegation::class, 'TAS_ID', 'TAS_ID');
    }

    /**
     * Scope a query to only include self-service
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsSelfService($query)
    {
        return $query->where('TAS_ASSIGN_TYPE', '=', 'SELF_SERVICE')
            ->where('TAS_GROUP_VARIABLE', '=', '');
    }

    /**
     * Get the title of the task
     *
     * @param  integer $tasId
     *
     * @return string
     */
    public function title($tasId)
    {
        $query = Task::query()->select('TAS_TITLE');
        $query->where('TAS_ID', $tasId);
        $results = $query->get();
        $title = '';
        $results->each(function ($item, $key) use (&$title) {
            $title = $item->TAS_TITLE;
            switch ($title) {
                case "INTERMEDIATE-THROW-EMAIL-EVENT":
                    $title = G::LoadTranslation('ID_INTERMEDIATE_THROW_EMAIL_EVENT');
                    break;
                case "INTERMEDIATE-THROW-MESSAGE-EVENT":
                    $title = G::LoadTranslation('ID_INTERMEDIATE_THROW_MESSAGE_EVENT');
                    break;
                case "INTERMEDIATE-CATCH-MESSAGE-EVENT":
                    $title = G::LoadTranslation('ID_INTERMEDIATE_CATCH_MESSAGE_EVENT');
                    break;
                case "INTERMEDIATE-CATCH-TIMER-EVENT":
                    $title = G::LoadTranslation('ID_INTERMEDIATE_CATCH_TIMER_EVENT');
                    break;
            }
        });

        return $title;
    }
}
