<?php
/**
 * Model factory for a configuration
 */
use Faker\Generator as Faker;
use ProcessMaker\Model\Configuration;
use ProcessMaker\Model\User;

$factory->define(Configuration::class, function(Faker $faker) {
    return [
        'CFG_UID' => $faker->randomElement(['AUDIT_LOG', 'EE']),
        'OBJ_UID' => '',
        'CFG_VALUE' => '',
        'PRO_UID' => G::generateUniqueID(),
        'USR_UID' => G::generateUniqueID(),
        'APP_UID' => G::generateUniqueID(),
    ];
});

$factory->state(Configuration::class, 'userPreferencesEmpty', function (Faker $faker) {
    // Grab a user if random
    $users = User::all();
    if (!empty($users)) {
        $user = factory(User::class)->create();
    } else {
        $user = $users->random();
    }
    return [
        'CFG_UID' => 'USER_PREFERENCES',
        'OBJ_UID' => '',
        'CFG_VALUE' => '',
        'PRO_UID' => '',
        'USR_UID' => $user->USR_UID,
        'APP_UID' => '',
    ];
});

