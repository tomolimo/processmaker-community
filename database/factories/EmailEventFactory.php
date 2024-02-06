<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\EmailEvent::class, function(Faker $faker) {
    $bpmnEvent = factory(\ProcessMaker\Model\BpmnEvent::class)->create();
    return [
        'EMAIL_EVENT_UID' => $faker->regexify("/[a-zA-Z]{32}/"),
        'PRJ_UID' => $bpmnEvent->PRJ_UID,
        'EVN_UID' => $bpmnEvent->EVN_UID,
        'EMAIL_EVENT_FROM' => $faker->email,
        'EMAIL_EVENT_TO' => $faker->email,
        'EMAIL_EVENT_SUBJECT' => $faker->title,
        'PRF_UID' => function() {
            return factory(\ProcessMaker\Model\ProcessFiles::class)->create()->PRF_UID;
        },
        'EMAIL_SERVER_UID' => function() {
            return factory(\ProcessMaker\Model\EmailServerModel::class)->create()->MESS_UID;
        },
    ];
});
