<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\ProcessFiles::class, function(Faker $faker) {
    return [
        'PRF_UID' => G::generateUniqueID(),
        'PRO_UID' => '',
        'USR_UID' => '',
        'PRF_UPDATE_USR_UID' => '',
        'PRF_PATH' => '',
        'PRF_TYPE' => '',
        'PRF_EDITABLE' => 1,
        'PRF_CREATE_DATE' => $faker->dateTime(),
        'PRF_UPDATE_DATE' => $faker->dateTime(),
    ];
});
