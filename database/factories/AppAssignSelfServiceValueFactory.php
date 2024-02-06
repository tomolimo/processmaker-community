<?php
/**
 * Model factory for a APP_ASSIGN_SELF_SERVICE_VALUE
 */
use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\AppAssignSelfServiceValue::class, function(Faker $faker) {
    return [
        'ID' => $faker->unique()->numberBetween(1, 2000),
        'APP_UID' => G::generateUniqueID(),
        'DEL_INDEX' => 2,
        'PRO_UID' => G::generateUniqueID(),
        'TAS_UID' => G::generateUniqueID(),
        'TAS_ID' => $faker->unique()->numberBetween(1, 2000),
        'GRP_UID' => G::generateUniqueID(),
    ];
});

