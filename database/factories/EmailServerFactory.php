<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\EmailServerModel::class, function(Faker $faker) {
    return [
        'MESS_UID' => G::generateUniqueID(),
        'MESS_ENGINE' => '',
        'MESS_SERVER' => '',
        'MESS_PORT' => 0,
        'MESS_INCOMING_SERVER' => '',
        'MESS_INCOMING_PORT' => 0,
        'MESS_RAUTH' => 0,
        'MESS_ACCOUNT' => '',
        'MESS_PASSWORD' => '',
        'MESS_FROM_MAIL' => '',
        'MESS_FROM_NAME' => '',
        'SMTPSECURE' => 'No',
        'MESS_TRY_SEND_INMEDIATLY' => 0,
        'MAIL_TO' => '',
        'MESS_DEFAULT' => 0,
    ];
});
