<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\AppNotes::class, function (Faker $faker) {
    return [
        'APP_UID' => G::generateUniqueID(),
        'USR_UID' => G::generateUniqueID(),
        'NOTE_DATE' => $faker->dateTime(),
        'NOTE_CONTENT' => $faker->sentence(3),
        'NOTE_TYPE' => 'USER',
        'NOTE_AVAILABILITY' => 'PUBLIC',
        'NOTE_ORIGIN_OBJ' => '',
        'NOTE_AFFECTED_OBJ1' => '',
        'NOTE_AFFECTED_OBJ2' => '',
        'NOTE_RECIPIENTS' => '',
    ];
});

// Create a case notes with the foreign keys
$factory->state(\ProcessMaker\Model\AppNotes::class, 'foreign_keys', function (Faker $faker) {
    // Create values in the foreign key relations
    $application = factory(\ProcessMaker\Model\Application::class)->create();
    $user = factory(\ProcessMaker\Model\User::class)->create();

    // Return with default values
    return [
        'APP_UID' => $application->APP_UID,
        'USR_UID' => $user->USR_UID,
        'NOTE_DATE' => $faker->dateTime(),
        'NOTE_CONTENT' => $faker->sentence(3),
        'NOTE_TYPE' => 'USER',
        'NOTE_AVAILABILITY' => 'PUBLIC',
        'NOTE_ORIGIN_OBJ' => '',
        'NOTE_AFFECTED_OBJ1' => '',
        'NOTE_AFFECTED_OBJ2' => '',
        'NOTE_RECIPIENTS' => '',
    ];
});

