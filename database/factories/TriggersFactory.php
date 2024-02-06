<?php

namespace Database\Factories;

use App\Factories\Factory;
use Illuminate\Support\Str;
use ProcessMaker\Model\Triggers;

class TriggersFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'TRI_UID' => $this->faker->regexify("/[a-zA-Z]{32}/"),
            'TRI_TITLE' => $this->faker->sentence(5),
            'TRI_DESCRIPTION' => $this->faker->text,
            'PRO_UID' => function () {
                return \ProcessMaker\Model\Process::factory()->create()->PRO_UID;
            },
            'TRI_TYPE' => 'SCRIPT',
            'TRI_WEBBOT' => '$var = 1;',
            'TRI_PARAM' => '',
        ];
    }

}
