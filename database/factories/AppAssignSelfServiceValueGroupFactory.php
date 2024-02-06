<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class AppAssignSelfServiceValueGroupFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'ID' => $this->faker->unique()->numberBetween(5000),
            'GRP_UID' => G::generateUniqueID(),
            'ASSIGNEE_ID' => $this->faker->unique()->numberBetween(1, 2000),
            'ASSIGNEE_TYPE' => $this->faker->unique()->numberBetween(1, 2000),
        ];
    }

}
