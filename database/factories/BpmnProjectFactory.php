<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\BpmnProject::class, function (Faker $faker) {
    return [
        'PRJ_UID' => G::generateUniqueID(),
        'PRJ_NAME' => '',
        'PRJ_DESCRIPTION' => $faker->text,
        'PRJ_EXPRESION_LANGUAGE' => '',
        'PRJ_TYPE_LANGUAGE' => '',
        'PRJ_EXPORTER' => '',
        'PRJ_EXPORTER_VERSION' => '',
        'PRJ_CREATE_DATE' => new \Carbon\Carbon(2030, 1, 1),
        'PRJ_UPDATE_DATE' => new \Carbon\Carbon(2030, 1, 1),
        'PRJ_AUTHOR' => '',
        'PRJ_AUTHOR_VERSION' => '',
        'PRJ_ORIGINAL_SOURCE' => '',
    ];
});
