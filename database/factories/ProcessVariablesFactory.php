<?php

use Faker\Generator as Faker;
use ProcessMaker\Model\ProcessVariables;

$factory->define(ProcessVariables::class, function (Faker $faker) {
    return [
        'VAR_UID' => G::generateUniqueID(),
        'PRO_ID' => G::generateUniqueID(),
        'PRJ_UID' => G::generateUniqueID(),
        'VAR_NAME' => $faker->word,
        'VAR_FIELD_TYPE' => G::generateUniqueID(),
        'VAR_FIELD_TYPE_ID' => G::generateUniqueID(),
        'VAR_FIELD_SIZE' => 10,
        'VAR_LABEL' => 'string',
        'VAR_DBCONNECTION' => 'workflow',
        'VAR_SQL' => '',
        'VAR_NULL' => 0,
        'VAR_DEFAULT' => '',
        'VAR_ACCEPTED_VALUES' => '[]',
        'INP_DOC_UID' => ''
    ];
});

// Create a processVariables with the foreign keys
$factory->state(ProcessVariables::class, 'foreign_keys', function (Faker $faker) {
    $types = ['string', 'integer', 'float', 'boolean', 'datetime', 'grid', 'array', 'file', 'multiplefile', 'object'];
    $varType = $faker->randomElement($types);
    $varTypeId = array_search($varType, $types) + 1;
    // Create values in the foreign key relations
    $process = factory(\ProcessMaker\Model\Process::class)->create();

    return [
        'VAR_UID' => G::generateUniqueID(),
        'PRO_ID' => $process->PRO_ID,
        'PRJ_UID' => $process->PRO_UID,
        'VAR_NAME' => $faker->word,
        'VAR_FIELD_TYPE' => $varType,
        'VAR_FIELD_TYPE_ID' => $varTypeId,
        'VAR_FIELD_SIZE' => 10,
        'VAR_LABEL' => 'string',
        'VAR_DBCONNECTION' => 'workflow',
        'VAR_SQL' => '',
        'VAR_NULL' => 0,
        'VAR_DEFAULT' => '',
        'VAR_ACCEPTED_VALUES' => '[]',
        'INP_DOC_UID' => ''
    ];
});