<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class ProcessCategoryFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'CATEGORY_UID' => G::generateUniqueID(),
            'CATEGORY_ID' => $this->faker->unique()->numberBetween(1000),
            'CATEGORY_PARENT' => '',
            'CATEGORY_NAME' => $this->faker->sentence(5),
            'CATEGORY_ICON' => '',
        ];
    }

}
