<?php

use Faker\Generator as Faker;
use ProcessMaker\Model\ProcessVariables;

$factory->define(ProcessVariables::class, function (Faker $faker) {
    return [
        'VAR_UID' => G::generateUniqueID(),
        'PRJ_UID' => G::generateUniqueID(),
        'VAR_NAME' => $faker->word,
        'VAR_FIELD_TYPE' => G::generateUniqueID(),
        'VAR_FIELD_SIZE' => 10,
        'VAR_LABEL' => 'string',
        'VAR_DBCONNECTION' => 'workflow',
        'VAR_SQL' => '',
        'VAR_NULL' => 0,
        'VAR_DEFAULT' => '',
        'VAR_ACCEPTED_VALUES' => '',
        'INP_DOC_UID' => ''
    ];
});