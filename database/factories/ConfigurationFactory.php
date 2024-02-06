<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;
use ProcessMaker\Model\Configuration;
use ProcessMaker\Model\User;

class ConfigurationFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'CFG_UID' => $this->faker->randomElement(['AUDIT_LOG', 'EE']),
            'OBJ_UID' => '',
            'CFG_VALUE' => '',
            'PRO_UID' => G::generateUniqueID(),
            'USR_UID' => G::generateUniqueID(),
            'APP_UID' => G::generateUniqueID(),
        ];
    }

    /**
     * 
     * @return type
     */
    public function userPreferencesEmpty()
    {
        $state = function (array $attributes) {
            // Grab a user if random
            $users = User::all();
            if (!empty($users)) {
                $user = User::factory()->create();
            } else {
                $user = $users->random();
            }
            return [
            'CFG_UID' => 'USER_PREFERENCES',
            'OBJ_UID' => '',
            'CFG_VALUE' => '',
            'PRO_UID' => '',
            'USR_UID' => $user->USR_UID,
            'APP_UID' => '',
            ];
        };
        return $this->state($state);
    }

}
