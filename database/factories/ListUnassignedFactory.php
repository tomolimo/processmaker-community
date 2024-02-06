<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;
use ProcessMaker\Model\Application;
use ProcessMaker\Model\Process;
use ProcessMaker\Model\Task;
use ProcessMaker\Model\User;

class ListUnassignedFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'APP_UID' => G::generateUniqueID(),
            'DEL_INDEX' => 2,
            'TAS_UID' => G::generateUniqueID(),
            'PRO_UID' => G::generateUniqueID(),
            'APP_NUMBER' => $this->faker->unique()->numberBetween(1000),
            'APP_TITLE' => $this->faker->sentence(3),
            'APP_PRO_TITLE' => $this->faker->sentence(3),
            'APP_TAS_TITLE' => $this->faker->sentence(3),
            'DEL_PREVIOUS_USR_USERNAME' => $this->faker->name,
            'DEL_PREVIOUS_USR_FIRSTNAME' => $this->faker->firstName,
            'DEL_PREVIOUS_USR_LASTNAME' => $this->faker->lastName,
            'APP_UPDATE_DATE' => $this->faker->dateTime(),
            'DEL_PREVIOUS_USR_UID' => G::generateUniqueID(),
            'DEL_DELEGATE_DATE' => $this->faker->dateTime(),
            'DEL_DUE_DATE' => $this->faker->dateTime(),
            'DEL_PRIORITY' => 3,
            'PRO_ID' => $this->faker->unique()->numberBetween(1000),
            'TAS_ID' => $this->faker->unique()->numberBetween(1000),
        ];
    }

    /**
     * 
     * @return type
     */
    public function foreign_keys()
    {
        $state = function (array $attributes) {
            $process = Process::factory()->create();
            $app = Application::factory()->create(['PRO_UID' => $process->PRO_UID]);
            $user = User::factory()->create();
            $task = Task::factory()->create([
                'TAS_ASSIGN_TYPE' => 'SELF_SERVICE', // Define a self-service type
                'TAS_GROUP_VARIABLE' => '',
                'PRO_UID' => $process->PRO_UID
            ]);

            return [
            'APP_UID' => $app->APP_UID,
            'DEL_INDEX' => 2,
            'TAS_UID' => $task->TAS_UID,
            'PRO_UID' => $process->PRO_UID,
            'APP_NUMBER' => $app->APP_NUMBER,
            'APP_TITLE' => $app->APP_TITLE,
            'APP_PRO_TITLE' => $process->PRO_TITLE,
            'APP_TAS_TITLE' => $task->TAS_TITLE,
            'DEL_PREVIOUS_USR_USERNAME' => $user->USR_USERNAME,
            'DEL_PREVIOUS_USR_FIRSTNAME' => $user->USR_FIRSTNAME,
            'DEL_PREVIOUS_USR_LASTNAME' => $user->USR_LASTNAME,
            'APP_UPDATE_DATE' => $this->faker->dateTime(),
            'DEL_PREVIOUS_USR_UID' => G::generateUniqueID(),
            'DEL_DELEGATE_DATE' => $this->faker->dateTime(),
            'DEL_DUE_DATE' => $this->faker->dateTime(),
            'DEL_PRIORITY' => 3,
            'PRO_ID' => $process->PRO_ID,
            'TAS_ID' => $task->TAS_ID,
            ];
        };
        return $this->state($state);
    }

}
