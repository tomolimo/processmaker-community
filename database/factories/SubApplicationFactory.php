<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\SubApplication::class, function (Faker $faker) {
    return [
        'APP_UID' => G::generateUniqueID(),
        'APP_PARENT' => G::generateUniqueID(),
        'DEL_INDEX_PARENT' => 2,
        'DEL_THREAD_PARENT' => 1,
        'SA_STATUS' => 'ACTIVE',
        'SA_VALUES_OUT' => 'a:0:{}',
        'SA_VALUES_IN' => 'a:0:{}',
        'SA_INIT_DATE' => $faker->dateTime(),
        'SA_FINISH_DATE' => $faker->dateTime(),
    ];
});
