<?php

namespace Database\Factories;

use App\Factories\Factory;
use Illuminate\Support\Str;

class BpmnDiagramFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'DIA_UID' => $this->faker->regexify("/[a-zA-Z]{32}/"),
            'PRJ_UID' => function () {
                return \ProcessMaker\Model\BpmnProject::factory()->create()->PRJ_UID;
            },
            'DIA_NAME' => $this->faker->name,
            'DIA_IS_CLOSABLE' => 0,
        ];
    }

}
