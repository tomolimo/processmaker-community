<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class SubApplicationFactory extends Factory
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
            'APP_PARENT' => G::generateUniqueID(),
            'DEL_INDEX_PARENT' => 2,
            'DEL_THREAD_PARENT' => 1,
            'SA_STATUS' => 'ACTIVE',
            'SA_VALUES_OUT' => 'a:0:{}',
            'SA_VALUES_IN' => 'a:0:{}',
            'SA_INIT_DATE' => $this->faker->dateTime(),
            'SA_FINISH_DATE' => $this->faker->dateTime(),
        ];
    }

}
