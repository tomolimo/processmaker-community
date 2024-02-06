<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\AppDelay::class, function (Faker $faker) {
    $actions = ['CANCEL', 'PAUSE', 'REASSIGN'];
    return [
        'APP_DELAY_UID' => G::generateUniqueID(),
        'PRO_UID' => G::generateUniqueID(),
        'APP_UID' => G::generateUniqueID(),
        'APP_NUMBER' => $faker->unique()->numberBetween(1000),
        'APP_THREAD_INDEX' => 1,
        'APP_DEL_INDEX' => $faker->unique()->numberBetween(10),
        'APP_TYPE' => $faker->randomElement($actions),
        'APP_STATUS' => 'TO_DO',
        'APP_NEXT_TASK' => 0,
        'APP_DELEGATION_USER' => G::generateUniqueID(),
        'APP_ENABLE_ACTION_USER' => G::generateUniqueID(),
        'APP_ENABLE_ACTION_DATE' => $faker->dateTime(),
        'APP_DISABLE_ACTION_USER' => G::generateUniqueID(),
        'APP_DISABLE_ACTION_DATE' => $faker->dateTime(),
        'APP_AUTOMATIC_DISABLED_DATE' => '',
        'APP_DELEGATION_USER_ID' => $faker->unique()->numberBetween(1000),
        'PRO_ID' => $faker->unique()->numberBetween(1000),
    ];
});
