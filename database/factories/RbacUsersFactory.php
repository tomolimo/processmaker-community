<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\RbacUsers::class, function (Faker $faker) {
    return [
        'USR_UID' => G::generateUniqueID(),
        'USR_USERNAME' => $faker->unique()->userName,
        'USR_PASSWORD' => $faker->password,
        'USR_FIRSTNAME' => $faker->firstName,
        'USR_LASTNAME' => $faker->lastName,
        'USR_EMAIL' => $faker->unique()->email,
        'USR_DUE_DATE' => $faker->dateTimeInInterval('now', '+1 year')->format('Y-m-d H:i:s'),
        'USR_CREATE_DATE' => $faker->date('Y-m-d H:i:s', 'now'),
        'USR_UPDATE_DATE' => $faker->date('Y-m-d H:i:s', 'now'),
        'USR_STATUS' => $faker->randomElement([0, 1]),
        'USR_AUTH_TYPE' => 'MYSQL', // Authentication type, by default is MySQL
        'UID_AUTH_SOURCE' => '00000000000000000000000000000000', // When the type is "MYSQL" the value for this field is this...
        'USR_AUTH_USER_DN' => '', // Don't required for now
        'USR_AUTH_SUPERVISOR_DN' => '' // Don't required for now
    ];
});

// Create a deleted user
$factory->state(\ProcessMaker\Model\RbacUsers::class, 'deleted', function () {
    return [
        'USR_USERNAME' => '',
        'USR_STATUS' => 0,
        'USR_AUTH_TYPE' => '',
        'UID_AUTH_SOURCE' => ''
    ];
});

// Create an active user
$factory->state(\ProcessMaker\Model\RbacUsers::class, 'active', function () {
    return [
        'USR_STATUS' => 1
    ];
});

// Create an inactive user
$factory->state(\ProcessMaker\Model\RbacUsers::class, 'inactive', function () {
    return [
        'USR_STATUS' => 0
    ];
});
