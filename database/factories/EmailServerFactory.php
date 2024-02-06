<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\EmailServerModel::class, function(Faker $faker) {
    return [
        'MESS_UID' => G::generateUniqueID(),
        'MESS_ENGINE' => 'MAIL',
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
        'OAUTH_CLIENT_ID' => '',
        'OAUTH_CLIENT_SECRET' => '',
        'OAUTH_REFRESH_TOKEN' => ''
    ];
});

$factory->state(\ProcessMaker\Model\EmailServerModel::class, 'PHPMAILER', function ($faker) {
    return [
        'MESS_UID' => G::generateUniqueID(),
        'MESS_ENGINE' => 'PHPMAILER',
        'MESS_PORT' => $faker->numberBetween(400, 500),
        'MESS_INCOMING_SERVER' => '',
        'MESS_INCOMING_PORT' => 0,
        'MESS_RAUTH' => 1,
        'MESS_ACCOUNT' => $faker->email,
        'MESS_PASSWORD' => $faker->password,
        'MESS_FROM_MAIL' => $faker->email,
        'MESS_FROM_NAME' => $faker->name,
        'SMTPSECURE' => 'ssl',
        'MESS_TRY_SEND_INMEDIATLY' => 0,
        'MAIL_TO' => $faker->email,
        'MESS_DEFAULT' => 0,
        'OAUTH_CLIENT_ID' => '',
        'OAUTH_CLIENT_SECRET' => '',
        'OAUTH_REFRESH_TOKEN' => ''
    ];
});

$factory->state(\ProcessMaker\Model\EmailServerModel::class, 'IMAP', function ($faker) {
    return [
        'MESS_UID' => G::generateUniqueID(),
        'MESS_ENGINE' => 'IMAP',
        'MESS_PORT' => $faker->numberBetween(400, 500),
        'MESS_INCOMING_SERVER' => 'imap.' . $faker->domainName,
        'MESS_INCOMING_PORT' => $faker->numberBetween(400, 500),
        'MESS_RAUTH' => 1,
        'MESS_ACCOUNT' => $faker->email,
        'MESS_PASSWORD' => $faker->password,
        'MESS_FROM_MAIL' => $faker->email,
        'MESS_FROM_NAME' => $faker->name,
        'SMTPSECURE' => 'ssl',
        'MESS_TRY_SEND_INMEDIATLY' => 0,
        'MAIL_TO' => $faker->email,
        'MESS_DEFAULT' => 0,
        'OAUTH_CLIENT_ID' => '',
        'OAUTH_CLIENT_SECRET' => '',
        'OAUTH_REFRESH_TOKEN' => ''
    ];
});

$factory->state(\ProcessMaker\Model\EmailServerModel::class, 'GMAILAPI', function ($faker) {
    return [
        'MESS_UID' => G::generateUniqueID(),
        'MESS_ENGINE' => 'GMAILAPI',
        'MESS_PORT' => 0,
        'MESS_INCOMING_SERVER' => '',
        'MESS_INCOMING_PORT' => 0,
        'MESS_RAUTH' => 1,
        'MESS_ACCOUNT' => $faker->email,
        'MESS_PASSWORD' => '',
        'MESS_FROM_MAIL' => $faker->email,
        'MESS_FROM_NAME' => $faker->name,
        'SMTPSECURE' => 'No',
        'MESS_TRY_SEND_INMEDIATLY' => 0,
        'MAIL_TO' => $faker->email,
        'MESS_DEFAULT' => 0,
        'OAUTH_CLIENT_ID' => $faker->regexify("/[0-9]{12}-[a-z]{32}\.apps\.googleusercontent\.com/"),
        'OAUTH_CLIENT_SECRET' => $faker->regexify("/[a-z]{24}/"),
        'OAUTH_REFRESH_TOKEN' => $faker->regexify("/[a-z]{7}[a-zA-Z0-9]{355}==/")
    ];
});
