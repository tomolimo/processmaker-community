<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\Documents::class, function (Faker $faker) {
    $types = ['INPUT', 'OUTPUT', 'ATTACHED'];
    $type = $faker->randomElement($types);
    return [
        'APP_DOC_UID' => G::generateUniqueID(),
        'APP_DOC_FILENAME' => 'image.png',
        'APP_DOC_TITLE' => '',
        'APP_DOC_COMMENT' => '',
        'DOC_VERSION' => 1,
        'APP_UID' => G::generateUniqueID(),
        'DEL_INDEX' => 1,
        'DOC_UID' => G::generateUniqueID(),
        'USR_UID' => G::generateUniqueID(),
        'APP_DOC_TYPE' => $type,
        'APP_DOC_CREATE_DATE' => $faker->date(),
        'APP_DOC_INDEX' => 1,
        'FOLDER_UID' => G::generateUniqueID(),
        'APP_DOC_PLUGIN' => '',
        'APP_DOC_TAGS' => '',
        'APP_DOC_STATUS' => 'ACTIVE',
        'APP_DOC_STATUS_DATE' => $faker->date(),
        'APP_DOC_FIELDNAME' => '',
        'APP_DOC_DRIVE_DOWNLOAD' => '',
    ];
});

// Create a document related to the case notes
$factory->state(\ProcessMaker\Model\Documents::class, 'case_notes', function (Faker $faker) {
    return [
        'APP_DOC_UID' => G::generateUniqueID(),
        'APP_DOC_FILENAME' => 'image.png',
        'APP_DOC_TITLE' => '',
        'APP_DOC_COMMENT' => '',
        'DOC_VERSION' => 1,
        'APP_UID' => G::generateUniqueID(),
        'DEL_INDEX' => 1,
        'DOC_UID' => G::generateUniqueID(),
        'USR_UID' => G::generateUniqueID(),
        'APP_DOC_TYPE' => 'CASE_NOTE',
        'APP_DOC_CREATE_DATE' => $faker->date(),
        'APP_DOC_INDEX' => 1,
        'FOLDER_UID' => G::generateUniqueID(),
        'APP_DOC_PLUGIN' => '',
        'APP_DOC_TAGS' => '',
        'APP_DOC_STATUS' => 'ACTIVE',
        'APP_DOC_STATUS_DATE' => $faker->date(),
        'APP_DOC_FIELDNAME' => '',
        'APP_DOC_DRIVE_DOWNLOAD' => '',
    ];
});
