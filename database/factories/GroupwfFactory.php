<?php
/**
 * Model factory for a groups
 */
use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\Groupwf::class, function(Faker $faker) {
    return [
        'GRP_UID' => G::generateUniqueID(),
        'GRP_ID' => $faker->unique()->numberBetween(1, 2000),
        'GRP_TITLE' => $faker->sentence(2),
        'GRP_STATUS' => 'ACTIVE',
        'GRP_LDAP_DN' => '',
        'GRP_UX' => 'NORMAL',
    ];
});

