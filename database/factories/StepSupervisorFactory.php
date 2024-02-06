<?php

namespace Database\Factories;

use App\Factories\Factory;

class StepSupervisorFactory extends Factory
{
    public function definition()
    {
        return [
            'STEP_UID' => $this->faker->regexify("/[a-zA-Z]{32}/"),
            'PRO_UID' => function () {
                return \ProcessMaker\Model\Process::factory()->create()->PRO_UID;
            },
            'STEP_TYPE_OBJ' => 'DYNAFORM',
            'STEP_UID_OBJ' => function () {
                return \ProcessMaker\Model\Dynaform::factory()->create()->DYN_UID;
            },
            'STEP_POSITION' => 1
        ];
    }
}
