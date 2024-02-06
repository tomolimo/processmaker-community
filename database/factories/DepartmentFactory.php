<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class DepartmentFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'DEP_UID' => G::generateUniqueID(),
            'DEP_TITLE' => $this->faker->sentence(2),
            'DEP_PARENT' => '',
            'DEP_MANAGER' => '',
            'DEP_LOCATION' => 0,
            'DEP_STATUS' => 'ACTIVE',
            'DEP_REF_CODE' => '',
            'DEP_LDAP_DN' => '',
        ];
    }

}
