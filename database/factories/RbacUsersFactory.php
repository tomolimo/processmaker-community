<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class RbacUsersFactory extends Factory
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
            'USR_DUE_DATE' => $this->faker->dateTimeInInterval('now', '+1 year')->format('Y-m-d H:i:s'),
            'USR_CREATE_DATE' => $this->faker->date('Y-m-d H:i:s', 'now'),
            'USR_UPDATE_DATE' => $this->faker->date('Y-m-d H:i:s', 'now'),
            'USR_STATUS' => $this->faker->randomElement([0, 1]),
            'USR_AUTH_TYPE' => 'MYSQL', // Authentication type, by default is MySQL
            'UID_AUTH_SOURCE' => '00000000000000000000000000000000', // When the type is "MYSQL" the value for this field is this...
            'USR_AUTH_USER_DN' => '', // Don't required for now
            'USR_AUTH_SUPERVISOR_DN' => '' // Don't required for now
        ];
    }

    /**
     * Create a deleted user
     * @return type
     */
    public function deleted()
    {
        $state = function (array $attributes) {
            return [
            'USR_USERNAME' => '',
            'USR_STATUS' => 0,
            'USR_AUTH_TYPE' => '',
            'UID_AUTH_SOURCE' => ''
            ];
        };
        return $this->state($state);
    }

    /**
     * Create an active user
     * @return type
     */
    public function active()
    {
        $state = function (array $attributes) {
            return [
            'USR_STATUS' => 1
            ];
        };
        return $this->state($state);
    }

    /**
     * Create an inactive user
     * @return type
     */
    public function inactive()
    {
        $state = function (array $attributes) {
            return [
            'USR_STATUS' => 0
            ];
        };
        return $this->state($state);
    }

}
