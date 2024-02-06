<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;

class EmailServerModel extends Model
{
    protected $table = 'EMAIL_SERVER';
    public $timestamps = false;

    /**
     * Obtain the selected columns of the EMAIL_SERVER table
     *
     * @param string $messUid
     * @return array
     * @see ProcessMaker\BusinessModel\ActionsByEmail::forwardMail()
     */
    public function getEmailServer($messUid)
    {
        $selectedColumns = [
            'EMAIL_SERVER.MESS_UID',
            'EMAIL_SERVER.MESS_ENGINE',
            'EMAIL_SERVER.MESS_SERVER',
            'EMAIL_SERVER.MESS_PORT',
            'EMAIL_SERVER.MESS_RAUTH',
            'EMAIL_SERVER.MESS_ACCOUNT',
            'EMAIL_SERVER.MESS_PASSWORD',
            'EMAIL_SERVER.MESS_FROM_MAIL',
            'EMAIL_SERVER.MESS_FROM_NAME',
            'EMAIL_SERVER.SMTPSECURE',
            'EMAIL_SERVER.MESS_TRY_SEND_INMEDIATLY',
            'EMAIL_SERVER.MAIL_TO',
            'EMAIL_SERVER.MESS_DEFAULT'
        ];
        $query = EmailServerModel::query()->select($selectedColumns);
        $query->where('EMAIL_SERVER.MESS_UID', '=', $messUid);
        $res = $query->get()->values()->toArray();
        $firstElement = head($res);

        return $firstElement;
    }

    /**
     * Obtain the default EMAI_SERVER configuration
     *
     * @return array
     * @see ProcessMaker\BusinessModel\ActionsByEmail::forwardMail()
     */
    public function getEmailServerDefault()
    {
        $selectedColumns = [
            'EMAIL_SERVER.MESS_UID',
            'EMAIL_SERVER.MESS_ENGINE',
            'EMAIL_SERVER.MESS_SERVER',
            'EMAIL_SERVER.MESS_PORT',
            'EMAIL_SERVER.MESS_INCOMING_SERVER',
            'EMAIL_SERVER.MESS_INCOMING_PORT',
            'EMAIL_SERVER.MESS_RAUTH',
            'EMAIL_SERVER.MESS_ACCOUNT',
            'EMAIL_SERVER.MESS_PASSWORD',
            'EMAIL_SERVER.MESS_FROM_MAIL',
            'EMAIL_SERVER.MESS_FROM_NAME',
            'EMAIL_SERVER.SMTPSECURE',
            'EMAIL_SERVER.MESS_TRY_SEND_INMEDIATLY',
            'EMAIL_SERVER.MAIL_TO',
            'EMAIL_SERVER.MESS_DEFAULT'
        ];
        $query = EmailServerModel::query()->select($selectedColumns)
            ->where('MESS_DEFAULT', '=', 1);
        $firstElement = $query->get()->values()->toArray();

        if (!empty($firstElement)) {
            $firstElement = head($firstElement);
            // @todo these index are been keep due to compatibility reasons
            $firstElement['MESS_BACKGROUND'] = '';
            $firstElement['MESS_PASSWORD_HIDDEN'] = '';
            $firstElement['MESS_EXECUTE_EVERY'] = '';
            $firstElement['MESS_SEND_MAX'] = '';
        }

        return $firstElement;
    }
}
