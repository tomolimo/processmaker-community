<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class RouteFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'PRO_UID' => function () {
                $process = \ProcessMaker\Model\Process::factory()->create();
                return $process->PRO_UID;
            },
            'TAS_UID' => function () {
                $task = \ProcessMaker\Model\Task::factory()->create();
                return $task->TAS_UID;
            },
            'ROU_UID' => G::generateUniqueID(),
            'ROU_PARENT' => 0,
            'ROU_CASE' => 1,
            'ROU_TYPE' => 'SEQUENTIAL'
        ];
    }

}
