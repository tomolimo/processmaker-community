<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class AppAssignSelfServiceValueFactory extends Factory
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
            'APP_UID' => G::generateUniqueID(),
            'DEL_INDEX' => 2,
            'PRO_UID' => G::generateUniqueID(),
            'TAS_UID' => G::generateUniqueID(),
            'TAS_ID' => $this->faker->unique()->numberBetween(1, 2000),
            'GRP_UID' => G::generateUniqueID(),
        ];
    }

}
