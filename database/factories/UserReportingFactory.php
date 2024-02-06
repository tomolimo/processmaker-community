<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\UserReporting::class, function (Faker $faker) {
    return [
        'USR_UID' => G::generateUniqueID(),
        'TAS_UID' => G::generateUniqueID(),
        'PRO_UID' => G::generateUniqueID(),
        'MONTH' => 12,
        'YEAR' => 2020,
    ];
});
