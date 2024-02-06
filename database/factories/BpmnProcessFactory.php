<?php

namespace Database\Factories;

use App\Factories\Factory;
use Illuminate\Support\Str;

class BpmnProcessFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'PRO_UID' => $this->faker->regexify("/[a-zA-Z]{32}/"),
            'PRJ_UID' => function () {
                return \ProcessMaker\Model\BpmnProject::factory()->create()->PRJ_UID;
            },
            'DIA_UID' => function () {
                return \ProcessMaker\Model\BpmnDiagram::factory()->create()->DIA_UID;
            },
            'PRO_NAME' => $this->faker->title,
            'PRO_TYPE' => 'NONE',
            'PRO_IS_EXECUTABLE' => 0,
            'PRO_IS_CLOSED' => 0,
            'PRO_IS_SUBPROCESS' => 0,
        ];
    }

}
