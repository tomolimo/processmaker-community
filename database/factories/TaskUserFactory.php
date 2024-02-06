<?php

namespace Database\Factories;

use App\Factories\Factory;
use Illuminate\Support\Str;

class TaskUserFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'TAS_UID' => function () {
                $task = \ProcessMaker\Model\Task::factory()->create();
                return $task->TAS_UID;
            },
            'TU_TYPE' => 1,
            'TU_RELATION' => 1
        ];
    }

    /**
     * Create a delegation with the foreign keys.
     */
    public function foreign_keys()
    {
        $state = function (array $attributes) {
            $user = \ProcessMaker\Model\User::factory()->create();
            $task = \ProcessMaker\Model\Task::factory()->create();
            return [
            'TAS_UID' => $task->TAS_UID,
            'USR_UID' => $user->USR_UID,
            'TU_TYPE' => 1,
            'TU_RELATION' => 1
            ];
        };
        return $this->state($state);
    }

    public function normal_assigment_user()
    {
        $state = function (array $attributes) {
            $user = \ProcessMaker\Model\User::factory()->create();
            $task = \ProcessMaker\Model\Task::factory()->create();
            return [
            'TAS_UID' => $task->TAS_UID,
            'USR_UID' => $user->USR_UID,
            'TU_RELATION' => 1,
            'TU_TYPE' => 1,
            ];
        };
        return $this->state($state);
    }

    public function normal_assigment_group()
    {
        $state = function (array $attributes) {
            $group = \ProcessMaker\Model\Groupwf::factory()->create();
            $task = \ProcessMaker\Model\Task::factory()->create();
            return [
            'TAS_UID' => $task->TAS_UID,
            'USR_UID' => $group->GRP_UID,
            'TU_RELATION' => 2,
            'TU_TYPE' => 1,
            ];
        };
        return $this->state($state);
    }

    public function adhoc_assigment_user()
    {
        $state = function (array $attributes) {
            $user = \ProcessMaker\Model\User::factory()->create();
            $task = \ProcessMaker\Model\Task::factory()->create();
            return [
            'TAS_UID' => $task->TAS_UID,
            'USR_UID' => $user->USR_UID,
            'TU_RELATION' => 1,
            'TU_TYPE' => 2,
            ];
        };
        return $this->state($state);
    }

    public function adhoc_assigment_group()
    {
        $state = function (array $attributes) {
            $group = \ProcessMaker\Model\Groupwf::factory()->create();
            $task = \ProcessMaker\Model\Task::factory()->create();
            return [
            'TAS_UID' => $task->TAS_UID,
            'USR_UID' => $group->GRP_UID,
            'TU_RELATION' => 2,
            'TU_TYPE' => 2,
            ];
        };
        return $this->state($state);
    }

}
