<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;
use ProcessMaker\Model\EmailServerModel;

class EmailEvent extends Model
{
    protected $table = 'EMAIL_EVENT';
    public $timestamps = false;

    /**
     * Update the email event when the email server is deleted
     * 
     * @param $emailServerUid
     * @return void
     */
    public function updateServerAndFromToDefaultOrEmpty($emailServerUid)
    {
        $emailServerModel = new EmailServerModel();
        $emailServerDefault = $emailServerModel->getEmailServerDefault();
        $query = EmailEvent::query();

        $query->where('EMAIL_SERVER_UID', '=', $emailServerUid);

        if (!empty($emailServerDefault)) {
            $query->update(['EMAIL_SERVER_UID' => $emailServerDefault['MESS_UID'], 'EMAIL_EVENT_FROM' => $emailServerDefault['MESS_ACCOUNT']]);
        } else {
            $query->update(['EMAIL_SERVER_UID' => '', 'EMAIL_EVENT_FROM' => '']);
        }
    }
}
