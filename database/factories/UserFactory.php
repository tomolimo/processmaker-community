<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\User::class, function (Faker $faker) {
    return [
        'USR_UID' => G::generateUniqueID(),
        'USR_USERNAME' => $faker->unique()->userName,
        'USR_PASSWORD' => $faker->password,
        'USR_FIRSTNAME' => $faker->firstName,
        'USR_LASTNAME' => $faker->lastName,
        'USR_EMAIL' => $faker->unique()->email,
        'USR_DUE_DATE' => new \Carbon\Carbon(2030, 1, 1),
        'USR_STATUS' => 'ACTIVE',
        'USR_ROLE' => $faker->randomElement(['PROCESSMAKER_ADMIN', 'PROCESSMAKER_OPERATOR']),
        'USR_UX' => 'NORMAL',
        'USR_TIME_ZONE' => 'America/Anguilla',
        'USR_DEFAULT_LANG' => 'en',
        'USR_LAST_LOGIN' => new \Carbon\Carbon(2019, 1, 1)
    ];
});