<?php
/**
 * Model factory for a list unassigned
 */
use Faker\Generator as Faker;
use ProcessMaker\Model\Application;
use ProcessMaker\Model\Process;
use ProcessMaker\Model\Task;
use ProcessMaker\Model\User;

$factory->define(\ProcessMaker\Model\ListUnassigned::class, function(Faker $faker) {
    return [
        'APP_UID' => G::generateUniqueID(),
        'DEL_INDEX' => 2,
        'TAS_UID' => G::generateUniqueID(),
        'PRO_UID' => G::generateUniqueID(),
        'APP_NUMBER' => $faker->unique()->numberBetween(1000),
        'APP_TITLE' => $faker->sentence(3),
        'APP_PRO_TITLE' => $faker->sentence(3),
        'APP_TAS_TITLE' => $faker->sentence(3),
        'DEL_PREVIOUS_USR_USERNAME' => $faker->name,
        'DEL_PREVIOUS_USR_FIRSTNAME' => $faker->firstName,
        'DEL_PREVIOUS_USR_LASTNAME' => $faker->lastName,
        'APP_UPDATE_DATE' => $faker->dateTime(),
        'DEL_PREVIOUS_USR_UID' => G::generateUniqueID(),
        'DEL_DELEGATE_DATE' => $faker->dateTime(),
        'DEL_DUE_DATE' => $faker->dateTime(),
        'DEL_PRIORITY' => 3,
        'PRO_ID' => $faker->unique()->numberBetween(1000),
        'TAS_ID' => $faker->unique()->numberBetween(1000),
    ];
});

$factory->state(\ProcessMaker\Model\ListUnassigned::class, 'foreign_keys', function (Faker $faker) {
    $process = factory(Process::class)->create();
    $app = factory(Application::class)->create(['PRO_UID' => $process->PRO_UID]);
    $user = factory(User::class)->create();
    $task = factory(Task::class)->create([
        'TAS_ASSIGN_TYPE' => 'SELF_SERVICE', // Define a self-service type
        'TAS_GROUP_VARIABLE' => '',
        'PRO_UID' => $process->PRO_UID
    ]);

    return [
        'APP_UID' => $app->APP_UID,
        'DEL_INDEX' => 2,
        'TAS_UID' => $task->TAS_UID,
        'PRO_UID' => $process->PRO_UID,
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

