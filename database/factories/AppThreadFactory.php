<?php
/**
 * Model factory for a APP_THREAD
 */

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\AppThread::class, function (Faker $faker) {
    return [
        'APP_UID' => G::generateUniqueID(),
        'APP_THREAD_INDEX' => $faker->unique()->numberBetween(1, 2000),
        'APP_THREAD_PARENT' => $faker->unique()->numberBetween(1, 2000),
        'APP_THREAD_STATUS' => $faker->randomElement(['OPEN', 'CLOSED']),
        'DEL_INDEX' => $faker->unique()->numberBetween(1, 2000)
    ];
});