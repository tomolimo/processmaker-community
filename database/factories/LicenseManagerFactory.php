<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\LicenseManager::class, function(Faker $faker) {
    return [
        "LICENSE_UID" => $faker->regexify("/[a-zA-Z]{32}/"),
        "LICENSE_USER" => $faker->name,
        "LICENSE_START" => 0,
        "LICENSE_END" => 0,
        "LICENSE_SPAN" => 0,
        "LICENSE_STATUS" => 'ACTIVE',
        "LICENSE_DATA" => '',
        "LICENSE_PATH" => '',
        "LICENSE_WORKSPACE" => '',
        "LICENSE_TYPE" => 'ONPREMISE'
    ];
});
