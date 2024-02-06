<?php

namespace ProcessMaker\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

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
            'EMAIL_SERVER.MESS_DEFAULT',
            'EMAIL_SERVER.OAUTH_CLIENT_ID',
            'EMAIL_SERVER.OAUTH_CLIENT_SECRET',
            'EMAIL_SERVER.OAUTH_REFRESH_TOKEN'
        ];
        $query = EmailServerModel::query()->select($selectedColumns);
        $query->where('EMAIL_SERVER.MESS_UID', '=', $messUid);
        $res = $query->get()->values()->toArray();
        $firstElement = head($res);

        if (!empty($firstElement)) {
            $firstElement['OAUTH_CLIENT_ID'] = !empty($firstElement['OAUTH_CLIENT_ID']) ?
                Crypt::decryptString($firstElement['OAUTH_CLIENT_ID']) : '';
            $firstElement['OAUTH_CLIENT_SECRET'] = !empty($firstElement['OAUTH_CLIENT_SECRET']) ?
                Crypt::decryptString($firstElement['OAUTH_CLIENT_SECRET']) : '';
            $firstElement['OAUTH_REFRESH_TOKEN'] = !empty($firstElement['OAUTH_REFRESH_TOKEN']) ?
                Crypt::decryptString($firstElement['OAUTH_REFRESH_TOKEN']) : '';
        }

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
            'EMAIL_SERVER.MESS_DEFAULT',
            'EMAIL_SERVER.OAUTH_CLIENT_ID',
            'EMAIL_SERVER.OAUTH_CLIENT_SECRET',
            'EMAIL_SERVER.OAUTH_REFRESH_TOKEN'
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
            $firstElement['OAUTH_CLIENT_ID'] = !empty($firstElement['OAUTH_CLIENT_ID']) ?
                Crypt::decryptString($firstElement['OAUTH_CLIENT_ID']) : '';
            $firstElement['OAUTH_CLIENT_SECRET'] = !empty($firstElement['OAUTH_CLIENT_SECRET']) ?
                Crypt::decryptString($firstElement['OAUTH_CLIENT_SECRET']) : '';
            $firstElement['OAUTH_REFRESH_TOKEN'] = !empty($firstElement['OAUTH_REFRESH_TOKEN']) ?
                Crypt::decryptString($firstElement['OAUTH_REFRESH_TOKEN']) : '';
        }

        return $firstElement;
    }

    /**
     * Check if the email server is IMAP
     * 
     * @param string $emailServerUid
     * @return boolean
     */
    public function isImap($emailServerUid)
    {
        $query = EmailServerModel::query()->select(['EMAIL_SERVER.MESS_UID']);
        $query->where('EMAIL_SERVER.MESS_UID', '=', $emailServerUid);
        $query->where('MESS_ENGINE', '=', 'IMAP');
        $res = $query->get()->values()->toArray();
        if (!empty($res)) {
            return true;
        }
        return false;
    }
}
