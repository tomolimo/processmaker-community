<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class RbacRolesFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'ROL_UID' => G::generateUniqueID(),
            'ROL_PARENT' => '', // This value is empty because actually don't exists this type of relations between roles
            'ROL_SYSTEM' => '00000000000000000000000000000002', // Hardcoded value, this value refers to ProcessMaker
            'ROL_CODE' => strtoupper($this->faker->word),
            'ROL_CREATE_DATE' => $this->faker->date('Y-m-d H:i:s', 'now'),
            'ROL_UPDATE_DATE' => $this->faker->date('Y-m-d H:i:s', 'now'),
            'ROL_STATUS' => $this->faker->randomElement([0, 1])
        ];
    }

}
