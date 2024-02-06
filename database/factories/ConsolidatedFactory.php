<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class ConsolidatedFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'TAS_UID' => G::generateUniqueID(),
            'DYN_UID' => G::generateUniqueID(),
            'REP_TAB_UID' => G::generateUniqueID(),
            'CON_STATUS' => 'ACTIVE',
        ];
    }

    /**
     * Create a consolidated task with the foreign keys
     * @return type
     */
    public function foreign_keys()
    {
        $state = function (array $attributes) {
            $task = \ProcessMaker\Model\Task::factory()->create();
            $dynaform = \ProcessMaker\Model\Dynaform::factory()->create();
            return [
            'TAS_UID' => $task->TAS_UID,
            'DYN_UID' => $dynaform->DYN_UID,
            'REP_TAB_UID' => G::generateUniqueID(),
            'CON_STATUS' => 'ACTIVE',
            ];
        };
        return $this->state($state);
    }

}
