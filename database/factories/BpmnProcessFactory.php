<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\BpmnProcess::class, function(Faker $faker) {
    return [
        'PRO_UID' => $faker->regexify("/[a-zA-Z]{32}/"),
        'PRJ_UID' => function() {
            return factory(\ProcessMaker\Model\BpmnProject::class)->create()->PRJ_UID;
        },
        'DIA_UID' => function() {
            return factory(\ProcessMaker\Model\BpmnDiagram::class)->create()->DIA_UID;
        },
        'PRO_NAME' => $faker->title,
        'PRO_TYPE' => 'NONE',
        'PRO_IS_EXECUTABLE' => 0,
        'PRO_IS_CLOSED' => 0,
        'PRO_IS_SUBPROCESS' => 0,
    ];
});
