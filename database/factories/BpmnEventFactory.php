<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\BpmnEvent::class, function(Faker $faker) {
    $bpmnProcess = factory(\ProcessMaker\Model\BpmnProcess::class)->create();
    return [
        'EVN_UID' => $faker->regexify("/[a-zA-Z]{32}/"),
        'PRJ_UID' => $bpmnProcess->PRJ_UID,
        'PRO_UID' => $bpmnProcess->PRO_UID,
        'EVN_NAME' => $faker->name,
        'EVN_TYPE' => 'START',
        'EVN_MARKER' => 'EMPTY',
        'EVN_IS_INTERRUPTING' => 1,
        'EVN_ATTACHED_TO' => '',
        'EVN_CANCEL_ACTIVITY' => 0,
        'EVN_ACTIVITY_REF' => null,
        'EVN_WAIT_FOR_COMPLETION' => 0,
        'EVN_ERROR_NAME' => null,
        'EVN_ERROR_CODE' => null,
        'EVN_ESCALATION_NAME' => null,
        'EVN_ESCALATION_CODE' => null,
        'EVN_CONDITION' => null,
        'EVN_MESSAGE' => '',
        'EVN_OPERATION_NAME' => null,
        'EVN_OPERATION_IMPLEMENTATION_REF' => null,
        'EVN_TIME_DATE' => null,
        'EVN_TIME_CYCLE' => null,
        'EVN_TIME_DURATION' => null,
        'EVN_BEHAVIOR' => 'THROW',
    ];
});
