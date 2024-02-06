<?php
/**
 * Model factory for web entries
 */
use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\WebEntry::class, function(Faker $faker) {
    return [
        'WE_UID' => G::generateUniqueID(),
        'PRO_UID' => G::generateUniqueID(),
        'TAS_UID' => G::generateUniqueID(),
        'DYN_UID' => G::generateUniqueID(),
        'USR_UID' => G::generateUniqueID(),
        'WE_METHOD' =>  $faker->randomElement(['WS', 'HTML']),
        'WE_INPUT_DOCUMENT_ACCESS' => $faker->randomElement([0, 1]),
        'WE_DATA' => G::generateUniqueID() . '.php',
        'WE_CREATE_USR_UID' => G::generateUniqueID(),
        'WE_UPDATE_USR_UID' => G::generateUniqueID(),
        'WE_CREATE_DATE' => $faker->date('Y-m-d H:i:s', 'now'),
        'WE_UPDATE_DATE' => $faker->date('Y-m-d H:i:s', 'now'),
        'WE_TYPE' => $faker->randomElement(['SINGLE', 'MULTIPLE']),
        'WE_CUSTOM_TITLE' => $faker->words(5, true),
        'WE_AUTHENTICATION' => $faker->randomElement(['LOGIN_REQUIRED', 'ANONYMOUS']),
        'WE_HIDE_INFORMATION_BAR' => $faker->randomElement(['0', '1']),
        'WE_CALLBACK' => $faker->randomElement(['PROCESSMAKER', 'CUSTOM', 'CUSTOM_CLEAR']),
        'WE_CALLBACK_URL' => $faker->url,
        'WE_LINK_GENERATION' => $faker->randomElement(['DEFAULT', 'ADVANCED']),
        'WE_LINK_SKIN' => 'classic',
        'WE_LINK_LANGUAGE' => 'en',
        'WE_LINK_DOMAIN' => $faker->domainName,
        'WE_SHOW_IN_NEW_CASE' => $faker->randomElement(['0', '1'])
    ];
});
