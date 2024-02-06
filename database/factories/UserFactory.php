<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class UserFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'USR_UID' => G::generateUniqueID(),
            'USR_USERNAME' => $this->faker->unique()->userName,
            'USR_PASSWORD' => $this->faker->password,
            'USR_FIRSTNAME' => $this->faker->firstName,
            'USR_LASTNAME' => $this->faker->lastName,
            'USR_EMAIL' => $this->faker->unique()->email,
            'USR_DUE_DATE' => new \Carbon\Carbon(2030, 1, 1),
            'USR_STATUS' => 'ACTIVE',
            'USR_ROLE' => $this->faker->randomElement(['PROCESSMAKER_ADMIN', 'PROCESSMAKER_OPERATOR']),
            'USR_UX' => 'NORMAL',
            'USR_TIME_ZONE' => 'America/Anguilla',
            'USR_DEFAULT_LANG' => 'en',
            'USR_LAST_LOGIN' => new \Carbon\Carbon(2019, 1, 1)
        ];
    }

}
