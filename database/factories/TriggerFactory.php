<?php

use Faker\Generator as Faker;
use ProcessMaker\Model\Triggers;

$factory->define(Triggers::class, function (Faker $faker) {
    return [
        'TRI_UID' => G::generateUniqueID(),
        'TRI_TITLE' => $faker->sentence(5),
        'TRI_DESCRIPTION' => $faker->text,
        'PRO_UID' => G::generateUniqueID(),
        'TRI_TYPE' => 'SCRIPT',
        'TRI_WEBBOT' => $faker->text,
        'TRI_PARAM' => '',
    ];
});