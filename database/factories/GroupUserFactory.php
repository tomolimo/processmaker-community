<?php
/**
 * Model factory for a group user relation
 */
use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\GroupUser::class, function(Faker $faker) {
    return [
        'GRP_UID' => G::generateUniqueID(),
        'GRP_ID' => $faker->unique()->numberBetween(1, 2000),
        'USR_UID' => G::generateUniqueID()
    ];
});

