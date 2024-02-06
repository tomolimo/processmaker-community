<?php

namespace Database\Factories;

use App\Factories\Factory;
use Illuminate\Support\Str;

class BpmnEventFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $bpmnProcess = \ProcessMaker\Model\BpmnProcess::factory()->create();
        return [
            'EVN_UID' => $this->faker->regexify("/[a-zA-Z]{32}/"),
            'PRJ_UID' => $bpmnProcess->PRJ_UID,
            'PRO_UID' => $bpmnProcess->PRO_UID,
            'EVN_NAME' => $this->faker->name,
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
    }

}
