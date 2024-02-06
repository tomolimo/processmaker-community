<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class AppTimeoutActionFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $index = $this->faker->unique()->numberBetween(20);
        return [
            'APP_UID' => G::generateUniqueID(),
            'DEL_INDEX' => $index,
            'EXECUTION_DATE' => $this->faker->dateTime()
        ];
    }

}
