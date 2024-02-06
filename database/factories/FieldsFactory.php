<?php

use Faker\Generator as Faker;
use ProcessMaker\Model\AdditionalTables;

$factory->define(\ProcessMaker\Model\Fields::class, function (Faker $faker) {
    return [
        'FLD_UID' => G::generateUniqueID(),
        'ADD_TAB_UID' => G::generateUniqueID(),
        'FLD_INDEX' => 0,
        'FLD_NAME' => 'VAR_' . $faker->sentence(1),
        'FLD_DESCRIPTION' => $faker->sentence(2),
        'FLD_TYPE' => 'VARCHAR',
        'FLD_SIZE' => 255,
        'FLD_NULL' => 1,
        'FLD_AUTO_INCREMENT' => 0,
        'FLD_KEY' => 1,
        'FLD_TABLE_INDEX' => 0,
        'FLD_FOREIGN_KEY' => 0,
        'FLD_FOREIGN_KEY_TABLE' => '',
        'FLD_DYN_NAME' => '',
        'FLD_DYN_UID' => '',
        'FLD_FILTER' => 0,
    ];
});

// Create columns from a table with the foreign keys
$factory->state(\ProcessMaker\Model\Fields::class, 'foreign_keys', function (Faker $faker) {
    return [
        'FLD_UID' => G::generateUniqueID(),
        'ADD_TAB_UID' => function() {
            $table = factory(AdditionalTables::class)->create(['ADD_TAB_OFFLINE' => 1]);
            return $table->ADD_TAB_UID;
        },
        'FLD_INDEX' => 0,
        'FLD_NAME' => 'VAR_' . $faker->sentence(1),
        'FLD_DESCRIPTION' => $faker->sentence(2),
        'FLD_TYPE' => 'VARCHAR',
        'FLD_SIZE' => 255,
        'FLD_NULL' => 1,
        'FLD_AUTO_INCREMENT' => 0,
        'FLD_KEY' => 1,
        'FLD_TABLE_INDEX' => 0,
        'FLD_FOREIGN_KEY' => 0,
        'FLD_FOREIGN_KEY_TABLE' => '',
        'FLD_DYN_NAME' => '',
        'FLD_DYN_UID' => '',
        'FLD_FILTER' => 0,
    ];
});
