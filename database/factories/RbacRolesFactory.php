<?php
/**
 * Model factory for a role
 */
use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\RbacRoles::class, function(Faker $faker) {
    return [
        'ROL_UID' => G::generateUniqueID(),
        'ROL_PARENT' => '', // This value is empty because actually don't exists this type of relations between roles
        'ROL_SYSTEM' => '00000000000000000000000000000002', // Hardcoded value, this value refers to ProcessMaker
        'ROL_CODE' => strtoupper($faker->word),
        'ROL_CREATE_DATE' => $faker->date('Y-m-d H:i:s', 'now'),
        'ROL_UPDATE_DATE' => $faker->date('Y-m-d H:i:s', 'now'),
        'ROL_STATUS' => $faker->randomElement([0, 1])
    ];
});
