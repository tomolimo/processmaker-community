<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\AppMessage::class, function(Faker $faker) {
    return [
        'APP_MSG_UID' => G::generateUniqueID(),
        'MSG_UID' => '',
        'APP_UID' => function() {
            return factory(\ProcessMaker\Model\Application::class)->create()->APP_UID;
        },
        'DEL_INDEX' => 1,
        'APP_MSG_TYPE' => 'ROUTING',
        'APP_MSG_TYPE_ID' => 0,
        'APP_MSG_SUBJECT' => $faker->title,
        'APP_MSG_FROM' => $faker->email,
        'APP_MSG_TO' => $faker->email,
        'APP_MSG_BODY' => $faker->text,
        'APP_MSG_DATE' => $faker->dateTime(),
        'APP_MSG_CC' => '',
        'APP_MSG_BCC' => '',
        'APP_MSG_TEMPLATE' => '',
        'APP_MSG_STATUS' => 'pending',
        'APP_MSG_STATUS_ID' => 1,
        'APP_MSG_ATTACH' => '',
        'APP_MSG_SEND_DATE' => $faker->dateTime(),
        'APP_MSG_SHOW_MESSAGE' => 1,
        'APP_MSG_ERROR' => '',
        'PRO_ID' => function() {
            return factory(\ProcessMaker\Model\Process::class)->create()->PRO_ID;
        },
        'TAS_ID' => function() {
            return factory(\ProcessMaker\Model\Task::class)->create()->TAS_ID;
        },
        'APP_NUMBER' => 1
    ];
});
