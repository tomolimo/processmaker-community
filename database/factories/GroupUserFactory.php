<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class GroupUserFactory extends Factory
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
            'GRP_ID' => $this->faker->unique()->numberBetween(1, 2000),
            'USR_UID' => G::generateUniqueID()
        ];
    }

    /**
     * Create columns from a table with the foreign keys
     * @return type
     */
    public function foreign_keys()
    {
        $state = function (array $attributes) {
            // Create values in the foreign key relations
            $user = \ProcessMaker\Model\User::factory()->create();
            $group = \ProcessMaker\Model\Groupwf::factory()->create();
            return [
            'GRP_UID' => $group->GRP_UID,
            'GRP_ID' => $group->GRP_ID,
            'USR_UID' => $user->USR_UID,
            ];
        };
        return $this->state($state);
    }

}
