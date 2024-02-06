<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\Application::class, function(Faker $faker) {
    $statuses = ['DRAFT', 'TO_DO'];
    $status = $faker->randomElement($statuses);
    $statusId = array_search($status, $statuses) + 1;
    $appNumber = $faker->unique()->numberBetween(1000);
    return [
        'APP_UID' => G::generateUniqueID(),
        'APP_TITLE' => $faker->sentence(3),
        'APP_NUMBER' => $appNumber,
        'APP_STATUS' => $status,
        'APP_STATUS_ID' => $statusId,
        'PRO_UID' => G::generateUniqueID(),
        'APP_PARALLEL' => 'N',
        'APP_INIT_USER' => G::generateUniqueID(),
        'APP_CUR_USER' => G::generateUniqueID(),
        'APP_PIN' => G::generateUniqueID(),
        'APP_CREATE_DATE' => $faker->dateTime(),
        'APP_UPDATE_DATE' => $faker->dateTime(),
        'APP_INIT_DATE' => $faker->dateTime(),
        'APP_DATA' => serialize(['APP_NUMBER' => $appNumber])
    ];
});

// Create a delegation with the foreign keys
$factory->state(\ProcessMaker\Model\Application::class, 'foreign_keys', function (Faker $faker) {
    // Create values in the foreign key relations
    $process = factory(\ProcessMaker\Model\Process::class)->create();
    $user = factory(\ProcessMaker\Model\User::class)->create();
    // Get other random values
    $statuses = ['DRAFT', 'TO_DO'];
    $status = $faker->randomElement($statuses);
    $statusId = array_search($status, $statuses) + 1;
    $appNumber = $faker->unique()->numberBetween(1000);
    return [
        'APP_UID' => G::generateUniqueID(),
        'APP_TITLE' => $faker->sentence(3),
        'APP_NUMBER' => $appNumber,
        'APP_STATUS' => $status,
        'APP_STATUS_ID' => $statusId,
        'PRO_UID' => $process->PRO_UID,
        'APP_PARALLEL' => 'N',
        'APP_INIT_USER' => $user->USR_UID,
        'APP_CUR_USER' => $user->USR_UID,
        'APP_PIN' => G::generateUniqueID(),
        'APP_CREATE_DATE' => $faker->dateTime(),
        'APP_UPDATE_DATE' => $faker->dateTime(),
        'APP_INIT_DATE' => $faker->dateTime(),
        'APP_DATA' => serialize(['APP_NUMBER' => $appNumber])
    ];
});