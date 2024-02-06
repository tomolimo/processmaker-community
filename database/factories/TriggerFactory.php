<?php

use Faker\Generator as Faker;
use ProcessMaker\Model\Triggers;

$factory->define(Triggers::class, function (Faker $faker) {
    return [
        'TRI_UID' => $faker->regexify("/[a-zA-Z]{32}/"),
        'TRI_TITLE' => $faker->sentence(5),
        'TRI_DESCRIPTION' => $faker->text,
        'PRO_UID' => function() {
            return factory(\ProcessMaker\Model\Process::class)->create()->PRO_UID;
        },
        'TRI_TYPE' => 'SCRIPT',
        'TRI_WEBBOT' => $faker->text,
        'TRI_PARAM' => '',
    ];
});
