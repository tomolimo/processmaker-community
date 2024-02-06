<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\BpmnDiagram::class, function(Faker $faker) {
    return [
        'DIA_UID' => $faker->regexify("/[a-zA-Z]{32}/"),
        'PRJ_UID' => function() {
            return factory(\ProcessMaker\Model\BpmnProject::class)->create()->PRJ_UID;
        },
        'DIA_NAME' => $faker->name,
        'DIA_IS_CLOSABLE' => 0,
    ];
});
