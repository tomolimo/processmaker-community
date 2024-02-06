<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class BpmnProjectFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Create user
        $user = \ProcessMaker\Model\User::factory()->create();
        // Create process
        $process = \ProcessMaker\Model\Process::factory()->create();

        return [
            'PRJ_UID' => G::generateUniqueID(),
            'PRJ_NAME' => $this->faker->sentence(5),
            'PRJ_DESCRIPTION' => $this->faker->text,
            'PRJ_EXPRESION_LANGUAGE' => '',
            'PRJ_TYPE_LANGUAGE' => '',
            'PRJ_EXPORTER' => '',
            'PRJ_EXPORTER_VERSION' => '',
            'PRJ_CREATE_DATE' => $this->faker->dateTime(),
            'PRJ_UPDATE_DATE' => $this->faker->dateTime(),
            'PRJ_AUTHOR' => $user->USR_UID,
            'PRJ_AUTHOR_VERSION' => '',
            'PRJ_ORIGINAL_SOURCE' => '',
        ];
    }

}
