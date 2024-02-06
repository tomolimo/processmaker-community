<?php

namespace Database\Factories;

use App\Factories\Factory;
use Illuminate\Support\Str;

class DelegationFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = \ProcessMaker\Model\User::factory()->create();
        $process = \ProcessMaker\Model\Process::factory()->create();
        $task = \ProcessMaker\Model\Task::factory()->create([
            'PRO_UID' => $process->PRO_UID,
            'PRO_ID' => $process->PRO_ID
        ]);
        $application = \ProcessMaker\Model\Application::factory()->create([
            'PRO_UID' => $process->PRO_UID,
            'APP_INIT_USER' => $user->USR_UID,
            'APP_CUR_USER' => $user->USR_UID
        ]);
        // Return with default values
        return [
            'DELEGATION_ID' => $this->faker->unique()->numberBetween(5000),
            'APP_UID' => $application->APP_UID,
            'DEL_INDEX' => 1,
            'APP_NUMBER' => $application->APP_NUMBER,
            'DEL_PREVIOUS' => 0,
            'PRO_UID' => $process->PRO_UID,
            'TAS_UID' => $task->TAS_UID,
            'USR_UID' => $user->USR_UID,
            'DEL_TYPE' => 'NORMAL',
            'DEL_THREAD' => 1,
            'DEL_THREAD_STATUS' => 'OPEN',
            'DEL_THREAD_STATUS_ID' => 1,
            'DEL_PRIORITY' => 3,
            'DEL_DELEGATE_DATE' => $this->faker->dateTime(),
            'DEL_INIT_DATE' => $this->faker->dateTime(),
            'DEL_TASK_DUE_DATE' => $this->faker->dateTime(),
            'DEL_RISK_DATE' => $this->faker->dateTime(),
            'DEL_LAST_INDEX' => 0,
            'USR_ID' => $user->USR_ID,
            'PRO_ID' => $process->PRO_ID,
            'TAS_ID' => $task->TAS_ID,
            'DEL_DATA' => '',
            'DEL_TITLE' => $this->faker->word()
        ];
    }

    /**
     * Create a delegation with the foreign keys
     * @return type
     */
    public function foreign_keys()
    {
        $state = function (array $attributes) {
            // Create values in the foreign key relations
            $user = \ProcessMaker\Model\User::factory()->create();
            $category = \ProcessMaker\Model\ProcessCategory::factory()->create();
            $process = \ProcessMaker\Model\Process::factory()->create([
                'PRO_CATEGORY' => $category->CATEGORY_UID,
                'CATEGORY_ID' => $category->CATEGORY_ID
            ]);
            $task = \ProcessMaker\Model\Task::factory()->create([
                'PRO_UID' => $process->PRO_UID,
                'PRO_ID' => $process->PRO_ID
            ]);
            $application = \ProcessMaker\Model\Application::factory()->create([
                'PRO_UID' => $process->PRO_UID,
                'APP_INIT_USER' => $user->USR_UID,
                'APP_CUR_USER' => $user->USR_UID
            ]);

            $delegateDate = $this->faker->dateTime();
            $initDate = $this->faker->dateTimeInInterval($delegateDate, '+30 minutes');
            $riskDate = $this->faker->dateTimeInInterval($initDate, '+1 day');
            $taskDueDate = $this->faker->dateTimeInInterval($riskDate, '+2 day');
            $index = $this->faker->unique()->numberBetween(2000);

            // Return with default values
            return [
                'DELEGATION_ID' => $this->faker->unique()->numberBetween(5000),
                'APP_UID' => $application->APP_UID,
                'DEL_INDEX' => $index,
                'APP_NUMBER' => $application->APP_NUMBER,
                'DEL_PREVIOUS' => $index - 1,
                'PRO_UID' => $process->PRO_UID,
                'TAS_UID' => $task->TAS_UID,
                'USR_UID' => $user->USR_UID,
                'DEL_TYPE' => 'NORMAL',
                'DEL_THREAD' => 1,
                'DEL_THREAD_STATUS' => 'OPEN',
                'DEL_THREAD_STATUS_ID' => 1,
                'DEL_PRIORITY' => 3,
                'DEL_DELEGATE_DATE' => $delegateDate,
                'DEL_INIT_DATE' => $initDate,
                'DEL_TASK_DUE_DATE' => $taskDueDate,
                'DEL_RISK_DATE' => $riskDate,
                'DEL_LAST_INDEX' => 1,
                'USR_ID' => $user->USR_ID,
                'PRO_ID' => $process->PRO_ID,
                'TAS_ID' => $task->TAS_ID,
                'DEL_DATA' => '',
                'DEL_TITLE' => $this->faker->word()
            ];
        };
        return $this->state($state);
    }

    /**
     * Create a delegation with the foreign keys
     * @return type
     */
    public function web_entry()
    {
        $state = function (array $attributes) {
            // Create values in the foreign key relations
            $user = \ProcessMaker\Model\User::factory()->create();
            $category = \ProcessMaker\Model\ProcessCategory::factory()->create();
            $process = \ProcessMaker\Model\Process::factory()->create([
                'PRO_CATEGORY' => $category->CATEGORY_UID,
                'CATEGORY_ID' => $category->CATEGORY_ID
            ]);
            $task = \ProcessMaker\Model\Task::factory()->create([
                'PRO_UID' => $process->PRO_UID,
                'PRO_ID' => $process->PRO_ID
            ]);
            $application = \ProcessMaker\Model\Application::factory()->web_entry()->create([
                'PRO_UID' => $process->PRO_UID,
                'APP_INIT_USER' => $user->USR_UID,
                'APP_CUR_USER' => $user->USR_UID
            ]);

            // Return with default values
            return [
                'DELEGATION_ID' => $this->faker->unique()->numberBetween(5000),
                'APP_UID' => $application->APP_UID,
                'DEL_INDEX' => 1,
                'APP_NUMBER' => $application->APP_NUMBER,
                'DEL_PREVIOUS' => 0,
                'PRO_UID' => $process->PRO_UID,
                'TAS_UID' => $task->TAS_UID,
                'USR_UID' => $user->USR_UID,
                'DEL_TYPE' => 'NORMAL',
                'DEL_THREAD' => 1,
                'DEL_THREAD_STATUS' => 'OPEN',
                'DEL_THREAD_STATUS_ID' => 1,
                'DEL_PRIORITY' => 3,
                'DEL_DELEGATE_DATE' => $this->faker->dateTime(),
                'DEL_INIT_DATE' => $this->faker->dateTime(),
                'DEL_TASK_DUE_DATE' => $this->faker->dateTime(),
                'DEL_RISK_DATE' => $this->faker->dateTime(),
                'USR_ID' => $user->USR_ID,
                'PRO_ID' => $process->PRO_ID,
                'TAS_ID' => $task->TAS_ID,
                'DEL_DATA' => '',
                'DEL_TITLE' => $this->faker->word()
            ];
        };
        return $this->state($state);
    }

    /**
     * Create a open delegation
     * @return type
     */
    public function open()
    {
        $state = function (array $attributes) {
            // Create dates with sense
            $delegateDate = $this->faker->dateTime();
            $initDate = $this->faker->dateTimeInInterval($delegateDate, '+30 minutes');
            $riskDate = $this->faker->dateTimeInInterval($initDate, '+1 day');
            $taskDueDate = $this->faker->dateTimeInInterval($riskDate, '+2 day');

            return [
                'DEL_THREAD_STATUS' => 'OPEN',
                'DEL_THREAD_STATUS_ID' => 1,
                'DEL_DELEGATE_DATE' => $delegateDate,
                'DEL_INIT_DATE' => $initDate,
                'DEL_RISK_DATE' => $riskDate,
                'DEL_TASK_DUE_DATE' => $taskDueDate,
                'DEL_FINISH_DATE' => null
            ];
        };
        return $this->state($state);
    }

    /**
     * Create a closed delegation
     * @return type
     */
    public function closed()
    {
        $state = function (array $attributes) {
            // Create dates with sense
            $delegateDate = $this->faker->dateTime();
            $initDate = $this->faker->dateTimeInInterval($delegateDate, '+30 minutes');
            $riskDate = $this->faker->dateTimeInInterval($initDate, '+1 day');
            $taskDueDate = $this->faker->dateTimeInInterval($riskDate, '+2 day');
            $finishDate = $this->faker->dateTimeInInterval($initDate, '+10 days');

            return [
                'DEL_THREAD_STATUS' => 'CLOSED',
                'DEL_DELEGATE_DATE' => $delegateDate,
                'DEL_INIT_DATE' => $initDate,
                'DEL_RISK_DATE' => $riskDate,
                'DEL_TASK_DUE_DATE' => $taskDueDate,
                'DEL_FINISH_DATE' => $finishDate
            ];
        };
        return $this->state($state);
    }

    /**
     * Create a last delegation
     * @return type
     */
    public function last_thread()
    {
        $state = function (array $attributes) {
            return [
                'DEL_LAST_INDEX' => 1,
            ];
        };
        return $this->state($state);
    }

    /**
     * Create a first delegation
     * @return type
     */
    public function first_thread()
    {
        $state = function (array $attributes) {
            return [
                'DEL_INDEX' => 1,
                'DEL_PREVIOUS' => 0,
            ];
        };
        return $this->state($state);
    }
}
