<?php
/**
 * Model factory for a list unassigned
 */
use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\ListUnassigned::class, function (Faker $faker) {
    $app = factory(\ProcessMaker\Model\Application::class)->states('foreign_keys')->create();
    $user = factory(\ProcessMaker\Model\User::class)->create();
    $process = \ProcessMaker\Model\Process::where('PRO_UID', $app->PRO_UID)->first();
    $task = $process->tasks->first();

    return [
        'APP_UID' => $app->APP_UID,
        'DEL_INDEX' => 1,
        'TAS_UID' => $task->TAS_UID,
        'PRO_UID' => $app->PRO_UID,
        'APP_NUMBER' => $app->APP_NUMBER,
        'APP_TITLE' => $app->APP_TITLE,
        'APP_PRO_TITLE' => $process->PRO_TITLE,
        'APP_TAS_TITLE' => $task->TAS_TITLE,
        'DEL_PREVIOUS_USR_USERNAME' => $user->USR_USERNAME,
        'DEL_PREVIOUS_USR_FIRSTNAME' => $user->USR_FIRSTNAME,
        'DEL_PREVIOUS_USR_LASTNAME' => $user->USR_LASTNAME,
        'APP_UPDATE_DATE' => $faker->dateTime(),
        'DEL_PREVIOUS_USR_UID' => G::generateUniqueID(),
        'DEL_DELEGATE_DATE' => $faker->dateTime(),
        'DEL_DUE_DATE' => $faker->dateTime(),
        'DEL_PRIORITY' => 3,
        'PRO_ID' => $process->PRO_ID,
        'TAS_ID' => $task->TAS_ID,
    ];
});

