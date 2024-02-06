<?php
/**
 * Model factory for a APP_ASSIGN_SELF_SERVICE_VALUE_GROUP
 */
use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\AppAssignSelfServiceValueGroup::class, function(Faker $faker) {
    return [
        'ID' => $faker->unique()->numberBetween(1, 2000),
        'GRP_UID' => G::generateUniqueID(),
        'ASSIGNEE_ID' => $faker->unique()->numberBetween(1, 2000),
        'ASSIGNEE_TYPE' => $faker->unique()->numberBetween(1, 2000),
    ];
});

