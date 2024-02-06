<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\AbeConfiguration::class, function (Faker $faker) {
    $process = factory(\ProcessMaker\Model\Process::class)->create();
    $dynaform = factory(\ProcessMaker\Model\Dynaform::class)->create([
        'PRO_UID' => $process->PRO_UID
    ]);
    $task = factory(\ProcessMaker\Model\Task::class)->create([
        'PRO_UID' => $process->PRO_UID
    ]);
    $emailServer = factory(\ProcessMaker\Model\EmailServerModel::class)->create();
    return [
        'ABE_UID' => G::generateUniqueID(),
        'PRO_UID' => $process->PRO_UID,
        'TAS_UID' => $task->TAS_UID,
        'ABE_TYPE' => $faker->randomElement(['', 'LINK']),
        'ABE_TEMPLATE' => 'actionByEmail.html',
        'ABE_DYN_TYPE' => 'NORMAL',
        'DYN_UID' => $dynaform->DYN_UID,
        'ABE_EMAIL_FIELD' => 'admin@processmaker.com',
        'ABE_ACTION_FIELD' => '',
        'ABE_CASE_NOTE_IN_RESPONSE' => $faker->randomElement(['0', '1']),
        'ABE_FORCE_LOGIN' => $faker->randomElement(['0', '1']),
        'ABE_CREATE_DATE' => $faker->dateTime(),
        'ABE_UPDATE_DATE' => $faker->dateTime(),
        'ABE_SUBJECT_FIELD' => '',
        'ABE_MAILSERVER_OR_MAILCURRENT' => 0,
        'ABE_CUSTOM_GRID' => serialize([]),
        'ABE_EMAIL_SERVER_UID' => $emailServer->MESS_UID,
        'ABE_ACTION_BODY_FIELD' => '',
        'ABE_EMAIL_SERVER_RECEIVER_UID' => ''
    ];
});
