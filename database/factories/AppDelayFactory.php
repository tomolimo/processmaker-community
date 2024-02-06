<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class AppDelayFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $actions = ['CANCEL', 'PAUSE', 'REASSIGN'];
        return [
            'APP_DELAY_UID' => G::generateUniqueID(),
            'PRO_UID' => G::generateUniqueID(),
            'APP_UID' => G::generateUniqueID(),
            'APP_NUMBER' => $this->faker->unique()->numberBetween(1000),
            'APP_THREAD_INDEX' => $this->faker->unique()->numberBetween(100),
            'APP_DEL_INDEX' => $this->faker->unique()->numberBetween(100),
            'APP_TYPE' => $this->faker->randomElement($actions),
            'APP_STATUS' => 'TO_DO',
            'APP_NEXT_TASK' => 0,
            'APP_DELEGATION_USER' => G::generateUniqueID(),
            'APP_ENABLE_ACTION_USER' => G::generateUniqueID(),
            'APP_ENABLE_ACTION_DATE' => $this->faker->dateTime(),
            'APP_DISABLE_ACTION_USER' => G::generateUniqueID(),
            'APP_DISABLE_ACTION_DATE' => $this->faker->dateTime(),
            'APP_AUTOMATIC_DISABLED_DATE' => '',
            'APP_DELEGATION_USER_ID' => $this->faker->unique()->numberBetween(1000),
            'PRO_ID' => $this->faker->unique()->numberBetween(1000),
        ];
    }

    /**
     * Create a delegation with the foreign keys
     * @return type
     */
    public function paused_foreign_keys()
    {
        $state = function (array $attributes) {
            // Create values in the foreign key relations
            $delegation1 = \ProcessMaker\Model\Delegation::factory()->closed()->create();
            $delegation2 = \ProcessMaker\Model\Delegation::factory()->foreign_keys()->create([
                'PRO_UID' => $delegation1->PRO_UID,
                'PRO_ID' => $delegation1->PRO_ID,
                'TAS_UID' => $delegation1->TAS_UID,
                'TAS_ID' => $delegation1->TAS_ID,
                'APP_NUMBER' => $delegation1->APP_NUMBER,
                'APP_UID' => $delegation1->APP_UID,
                'DEL_THREAD_STATUS' => 'OPEN',
                'USR_UID' => $delegation1->USR_UID,
                'USR_ID' => $delegation1->USR_ID,
                'DEL_PREVIOUS' => $delegation1->DEL_INDEX,
                'DEL_INDEX' => $this->faker->unique()->numberBetween(2000),
            ]);

            // Return with default values
            return [
            'APP_DELAY_UID' => G::generateUniqueID(),
            'PRO_UID' => $delegation2->PRO_UID,
            'PRO_ID' => $delegation2->PRO_ID,
            'APP_UID' => $delegation2->APP_UID,
            'APP_NUMBER' => $delegation2->APP_NUMBER,
            'APP_DEL_INDEX' => $delegation2->DEL_INDEX,
            'APP_TYPE' => 'PAUSE',
            'APP_STATUS' => 'TO_DO',
            'APP_DELEGATION_USER' => $delegation2->USR_UID,
            'APP_DELEGATION_USER_ID' => $delegation2->USR_ID,
            'APP_ENABLE_ACTION_USER' => G::generateUniqueID(),
            'APP_ENABLE_ACTION_DATE' => $this->faker->dateTime(),
            'APP_DISABLE_ACTION_USER' => 0,
            ];
        };
        return $this->state($state);
    }

}
