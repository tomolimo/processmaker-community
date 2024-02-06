<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\AdditionalTables::class, function(Faker $faker) {
    $name = $faker->regexify("/[a-zA-Z]{6}/");
    return [
        'ADD_TAB_UID' => G::generateUniqueID(),
        'ADD_TAB_NAME' => 'PMT_' . strtoupper($name),
        'ADD_TAB_CLASS_NAME' => 'Pmt' . $name,
        'ADD_TAB_DESCRIPTION' => $faker->text,
        'ADD_TAB_SDW_LOG_INSERT' => 0,
        'ADD_TAB_SDW_LOG_UPDATE' => 0,
        'ADD_TAB_SDW_LOG_DELETE' => 0,
        'ADD_TAB_SDW_LOG_SELECT' => 0,
        'ADD_TAB_SDW_MAX_LENGTH' => 0,
        'ADD_TAB_SDW_AUTO_DELETE' => 0,
        'ADD_TAB_PLG_UID' => '',
        'DBS_UID' => 'workflow',
        'PRO_UID' => function() {
            return factory(\ProcessMaker\Model\Process::class)->create()->PRO_UID;
        },
        'ADD_TAB_TYPE' => '',
        'ADD_TAB_GRID' => '',
        'ADD_TAB_TAG' => '',
        'ADD_TAB_OFFLINE' => 0,
        'ADD_TAB_UPDATE_DATE' => $faker->dateTime()
    ];
});
