<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class GroupwfFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'GRP_UID' => G::generateUniqueID(),
            'GRP_ID' => $this->faker->unique()->numberBetween(2000),
            'GRP_TITLE' => $this->faker->sentence(2),
            'GRP_STATUS' => 'ACTIVE',
            'GRP_LDAP_DN' => '',
            'GRP_UX' => 'NORMAL',
        ];
    }

}
