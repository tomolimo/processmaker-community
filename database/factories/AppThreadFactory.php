<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class AppThreadFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'APP_UID' => G::generateUniqueID(),
            'APP_THREAD_INDEX' => $this->faker->unique()->numberBetween(1, 2000),
            'APP_THREAD_PARENT' => $this->faker->unique()->numberBetween(1, 2000),
            'APP_THREAD_STATUS' => $this->faker->randomElement(['OPEN', 'CLOSED']),
            'DEL_INDEX' => $this->faker->unique()->numberBetween(1, 2000)
        ];
    }

}
