<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\Application::class, function(Faker $faker) {
    $user = factory(\ProcessMaker\Model\User::class)->create();
    $appNumber = $faker->unique()->numberBetween(1000);

    //APP_TITLE field is used in 'MYSQL: MATCH() AGAINST()' function, string size should not be less than 3.
    $appTitle = $faker->lexify(str_repeat('?', rand(3, 5)) . ' ' . str_repeat('?', rand(3, 5)));

    //APP_STATUS must start in TO_DO because all tests require this state.

    return [
        'APP_UID' => G::generateUniqueID(),
        'APP_TITLE' => $appTitle,
        'APP_NUMBER' => $appNumber,
        'APP_STATUS' => 'TO_DO',
        'APP_STATUS_ID' => 2,
        'PRO_UID' => function() {
            return factory(\ProcessMaker\Model\Process::class)->create()->PRO_UID;
        },
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

// Create a delegation with the foreign keys
$factory->state(\ProcessMaker\Model\Application::class, 'foreign_keys', function (Faker $faker) {
    // Create values in the foreign key relations
    $process = factory(\ProcessMaker\Model\Process::class)->create();
    $user = factory(\ProcessMaker\Model\User::class)->create();
    $appNumber = $faker->unique()->numberBetween(1000);

    //APP_TITLE field is used in 'MYSQL: MATCH() AGAINST()' function, string size should not be less than 3.
    $appTitle = $faker->lexify(str_repeat('?', rand(3, 5)) . ' ' . str_repeat('?', rand(3, 5)));

    //APP_STATUS must start in TO_DO because all tests require this state.

    return [
        'APP_UID' => G::generateUniqueID(),
        'APP_TITLE' => $appTitle,
        'APP_NUMBER' => $appNumber,
        'APP_STATUS' => 'TO_DO',
        'APP_STATUS_ID' => 2,
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
