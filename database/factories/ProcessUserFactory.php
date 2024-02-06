<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class ProcessUserFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'PU_UID' => G::generateUniqueID(),
            'PRO_UID' => G::generateUniqueID(),
            'USR_UID' => G::generateUniqueID(),
            'PU_TYPE' => 'SUPERVISOR'
        ];
    }

    /**
     * Create a process with the foreign keys
     * @return type
     */
    public function foreign_keys()
    {
        $state = function (array $attributes) {
            // Create user
            $user = \ProcessMaker\Model\User::factory()->create();
            $process = \ProcessMaker\Model\Process::factory()->create();

            return [
            'PU_UID' => G::generateUniqueID(),
            'PRO_UID' => $process->PRO_UID,
            'USR_UID' => $user->USR_UID,
            'PU_TYPE' => 'SUPERVISOR'
            ];
        };
        return $this->state($state);
    }

}
