<?php
/**
 * Model factory for a process
 */
use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\Process::class, function(Faker $faker) {
    // Return with default values
    return [
        'PRO_UID' => G::generateUniqueID(),
        'PRO_ID' => $faker->unique()->numberBetween(1, 200000),
        'PRO_TITLE' => $faker->sentence(3),
        'PRO_DESCRIPTION' => $faker->paragraph(3),
        'PRO_CREATE_USER' => '00000000000000000000000000000001',
        'PRO_DYNAFORMS' => '',
        'PRO_ITEE' => 1,
        'PRO_STATUS' => 'ACTIVE',
        'PRO_STATUS_ID' => 1,
        'PRO_TYPE_PROCESS' => 'PUBLIC',
        'PRO_UPDATE_DATE' => $faker->dateTime(),
        'PRO_CREATE_DATE' => $faker->dateTime(),
        'PRO_CATEGORY' => '',
    ];
});

// Create a process with the foreign keys
$factory->state(\ProcessMaker\Model\Process::class, 'foreign_keys', function (Faker $faker) {
    $user = factory(\ProcessMaker\Model\User::class)->create();
    return [
        'PRO_UID' => G::generateUniqueID(),
        'PRO_ID' => $faker->unique()->numberBetween(1, 200000),
        'PRO_TITLE' => $faker->sentence(3),
        'PRO_DESCRIPTION' => $faker->paragraph(3),
        'PRO_CREATE_USER' => $user->USR_UID,
        'PRO_DYNAFORMS' => '',
        'PRO_ITEE' => 1,
        'PRO_STATUS' => 'ACTIVE',
        'PRO_STATUS_ID' => 1,
        'PRO_TYPE_PROCESS' => 'PUBLIC',
        'PRO_UPDATE_DATE' => $faker->dateTime(),
        'PRO_CREATE_DATE' => $faker->dateTime(),
        'PRO_CATEGORY' => '',
    ];
});

// Create a process related to the flow designer
$factory->state(\ProcessMaker\Model\Process::class, 'flow', function (Faker $faker) {
    // Create values in the foreign key relations
    $user = factory(\ProcessMaker\Model\User::class)->create();
    $process = [
        'PRO_UID' => G::generateUniqueID(),
        'PRO_ID' => $faker->unique()->numberBetween(1, 200000),
        'PRO_TITLE' => $faker->sentence(3),
        'PRO_DESCRIPTION' => $faker->paragraph(3),
        'PRO_CREATE_USER' => $user->USR_UID,
        'PRO_DYNAFORMS' => '',
        'PRO_ITEE' => 1,
        'PRO_STATUS' => 'ACTIVE',
        'PRO_STATUS_ID' => 1,
        'PRO_TYPE_PROCESS' => 'PUBLIC',
        'PRO_UPDATE_DATE' => $faker->dateTime(),
        'PRO_CREATE_DATE' => $faker->dateTime(),
        'PRO_CATEGORY' => '',
    ];
    // Create a task related to this process
    $task = factory(\ProcessMaker\Model\Task::class)->create([
        'PRO_UID' => $process->PRO_UID,
        'PRO_ID' => $process->PRO_ID,
    ]);
});
