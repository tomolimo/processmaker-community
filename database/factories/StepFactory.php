<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\Step::class, function (Faker $faker) {
    return [
        'STEP_UID' => G::generateUniqueID(),
        'PRO_UID' => G::generateUniqueID(),
        'TAS_UID' => G::generateUniqueID(),
        'STEP_TYPE_OBJ' => 'DYNAFORM',
        'STEP_UID_OBJ' => '0',
        'STEP_CONDITION' => 'None',
        'STEP_POSITION' => 0,
        'STEP_MODE' => 'EDIT'
    ];
});