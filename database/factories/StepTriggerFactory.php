<?php

namespace Database\Factories;

use App\Factories\Factory;
use Illuminate\Support\Str;

class StepTriggerFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'STEP_UID' => $this->faker->regexify("/[a-zA-Z]{32}/"),
            'TAS_UID' => function () {
                return \ProcessMaker\Model\Task::factory()->create()->TAS_UID;
            },
            'TRI_UID' => function () {
                return \ProcessMaker\Model\Triggers::factory()->create()->TRI_UID;
            },
            'ST_TYPE' => 'BEFORE',
            'ST_CONDITION' => '',
            'ST_POSITION' => 1,
        ];
    }

}
