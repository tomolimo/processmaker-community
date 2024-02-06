<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\StepTrigger::class, function (Faker $faker) {
    return [
        'STEP_UID' => $faker->regexify("/[a-zA-Z]{32}/"),
        'TAS_UID' => function() {
            return factory(\ProcessMaker\Model\Task::class)->create()->TAS_UID;
        },
        'TRI_UID' => function() {
            return factory(\ProcessMaker\Model\Triggers::class)->create()->TRI_UID;
        },
        'ST_TYPE' => 'BEFORE',
        'ST_CONDITION' => '',
        'ST_POSITION' => 1,
    ];
});
