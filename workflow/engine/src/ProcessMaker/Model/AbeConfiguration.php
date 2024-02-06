<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AbeConfiguration extends model
{
    protected $table = "ABE_CONFIGURATION";
    // We do not have create/update timestamps for this table
    public $timestamps = false;

    /**
     * Relation between process
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function process()
    {
        return $this->belongsTo(Process::class, 'PRO_UID', 'PRO_UID');
    }

    /**
     * Relation between task
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task()
    {
        return $this->belongsTo(Task::class, 'TAS_UID', 'TAS_UID');
    }

    /**
     * Relation between emailServer
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function emailServer()
    {
        return $this->belongsTo(EmailServer::class, 'MESS_UID', 'MESS_UID');
    }

    /**
     * Get information about the notification sent
     *
     * @param string $abeRequestUid
     *
     * @return array
     */
    public static function getAbeRequest($abeRequestUid)
    {
        $selectedColumns = [
            'ABE_CONFIGURATION.ABE_UID',
            'ABE_CONFIGURATION.PRO_UID',
            'ABE_CONFIGURATION.TAS_UID',
            'ABE_CONFIGURATION.ABE_EMAIL_SERVER_UID',
            'ABE_CONFIGURATION.ABE_TYPE',
            'ABE_CONFIGURATION.ABE_MAILSERVER_OR_MAILCURRENT',
            'TASK.TAS_ID',
            'PROCESS.PRO_ID',
            'ABE_REQUESTS.ABE_REQ_UID',
            'ABE_REQUESTS.APP_UID',
            'ABE_REQUESTS.DEL_INDEX',
            'ABE_REQUESTS.ABE_REQ_SENT_TO',
            'ABE_REQUESTS.ABE_REQ_SUBJECT',
            'ABE_REQUESTS.ABE_REQ_BODY',
            'ABE_REQUESTS.ABE_REQ_ANSWERED',
            'ABE_REQUESTS.ABE_REQ_STATUS',
            'APP_DELEGATION.DEL_FINISH_DATE',
            'APP_DELEGATION.APP_NUMBER'
        ];
        $query = AbeConfiguration::query()->select($selectedColumns);

        $query->leftJoin('TASK', function ($join) {
            $join->on('ABE_CONFIGURATION.TAS_UID', '=', 'TASK.TAS_UID');
        });
        $query->leftJoin('PROCESS', function ($join) {
            $join->on('ABE_CONFIGURATION.PRO_UID', '=', 'PROCESS.PRO_UID');
        });
        $query->leftJoin('ABE_REQUESTS', function ($join) {
            $join->on('ABE_CONFIGURATION.ABE_UID', '=', 'ABE_REQUESTS.ABE_UID');
        });
        $query->leftJoin('APP_DELEGATION', function ($join) {
            $join->on('ABE_REQUESTS.APP_UID', '=', 'APP_DELEGATION.APP_UID')
                ->on('ABE_REQUESTS.DEL_INDEX', '=', 'APP_DELEGATION.DEL_INDEX');
        });
        $query->where('ABE_REQUESTS.ABE_REQ_UID', '=', $abeRequestUid);

        $query->limit(1);

        $res = $query->get()->values()->toArray();

        if (!empty($res)) {
            return $res[0];
        }

        return $res;
    }
}
