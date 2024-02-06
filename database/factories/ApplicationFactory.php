<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class ApplicationFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $user = \ProcessMaker\Model\User::factory()->create();
        $appNumber = $this->faker->unique()->numberBetween(1000);
        // APP_TITLE field is used in 'MYSQL: MATCH() AGAINST()' function, string size should not be less than 3.
        $appTitle = $this->faker->lexify(str_repeat('?', rand(3, 5)) . ' ' . str_repeat('?', rand(3, 5)));
        return [
            'APP_UID' => G::generateUniqueID(),
            'APP_TITLE' => $appTitle,
            'APP_DESCRIPTION' => $this->faker->text,
            'APP_NUMBER' => $appNumber,
            'APP_STATUS' => 'TO_DO',
            'APP_STATUS_ID' => 2,
            'PRO_UID' => function () {
                return \ProcessMaker\Model\Process::factory()->create()->PRO_UID;
            },
            'APP_PROC_STATUS' => '',
            'APP_PROC_CODE' => '',
            'APP_PARALLEL' => 'N',
            'APP_INIT_USER' => $user->USR_UID,
            'APP_CUR_USER' => $user->USR_UID,
            'APP_PIN' => G::generateUniqueID(),
            'APP_CREATE_DATE' => $this->faker->dateTimeBetween('now', '+30 minutes'),
            'APP_INIT_DATE' => $this->faker->dateTimeBetween('now', '+30 minutes'),
            'APP_UPDATE_DATE' => $this->faker->dateTimeBetween('now', '+30 minutes'),
            'APP_FINISH_DATE' => $this->faker->dateTimeBetween('now', '+30 minutes'),
            'APP_DATA' => serialize(['APP_NUMBER' => $appNumber])
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
            $process = \ProcessMaker\Model\Process::factory()->create();
            $user = \ProcessMaker\Model\User::factory()->create();
            $appNumber = $this->faker->unique()->numberBetween(1000);

            // APP_TITLE field is used in 'MYSQL: MATCH() AGAINST()' function, string size should not be less than 3.
            $appTitle = $this->faker->lexify(str_repeat('?', rand(3, 5)) . ' ' . str_repeat('?', rand(3, 5)));

            $statuses = ['DRAFT', 'TO_DO', 'COMPLETED', 'CANCELLED'];
            $status = $this->faker->randomElement($statuses);
            $statusId = array_search($status, $statuses) + 1;

            return [
            'APP_UID' => G::generateUniqueID(),
            'APP_TITLE' => $appTitle,
            'APP_NUMBER' => $appNumber,
            'APP_STATUS' => $status,
            'APP_STATUS_ID' => $statusId,
            'PRO_UID' => $process->PRO_UID,
            'APP_PROC_STATUS' => '',
            'APP_PROC_CODE' => '',
            'APP_PARALLEL' => 'N',
            'APP_INIT_USER' => $user->USR_UID,
            'APP_INIT_USER_ID' => $user->USR_ID,
            'APP_CUR_USER' => $user->USR_UID,
            'APP_PIN' => G::generateUniqueID(),
            'APP_CREATE_DATE' => $this->faker->dateTime(),
            'APP_INIT_DATE' => $this->faker->dateTime(),
            'APP_UPDATE_DATE' => $this->faker->dateTime(),
            'APP_FINISH_DATE' => $this->faker->dateTime(),
            'APP_DATA' => serialize(['APP_NUMBER' => $appNumber])
            ];
        };
        return $this->state($state);
    }

    /**
     * 
     * @return type
     */
    public function web_entry()
    {
        $state = function (array $attributes) {
            $appNumber = $this->faker->unique()->numberBetween(5000);
            return [
            'APP_NUMBER' => $appNumber * -1,
            'APP_STATUS_ID' => 2,
            'APP_STATUS' => 'TO_DO'
            ];
        };
        return $this->state($state);
    }

    /**
     * 
     * @return type
     */
    public function todo()
    {
        $state = function (array $attributes) {
            return [
            'APP_NUMBER' => $this->faker->unique()->numberBetween(1000),
            'APP_STATUS_ID' => 2,
            'APP_STATUS' => 'TO_DO'
            ];
        };
        return $this->state($state);
    }

    /**
     * 
     * @return type
     */
    public function draft()
    {
        $state = function (array $attributes) {
            $user = \ProcessMaker\Model\User::factory()->create();

            return [
            'APP_NUMBER' => $this->faker->unique()->numberBetween(1000),
            'APP_STATUS_ID' => 1,
            'APP_STATUS' => 'DRAFT',
            'APP_INIT_USER' => $user->USR_UID,
            'APP_INIT_USER_ID' => $user->USR_ID,
            ];
        };
        return $this->state($state);
    }

    /**
     * 
     * @return type
     */
    public function paused()
    {
        $state = function (array $attributes) {
            $user = \ProcessMaker\Model\User::factory()->create();

            return [
            'APP_NUMBER' => $this->faker->unique()->numberBetween(1000),
            'APP_STATUS_ID' => 1,
            'APP_STATUS' => 'PAUSED',
            'APP_INIT_USER' => $user->USR_UID,
            'APP_INIT_USER_ID' => $user->USR_ID,
            ];
        };
        return $this->state($state);
    }

    /**
     * 
     * @return type
     */
    public function completed()
    {
        $state = function (array $attributes) {
            return [
            'APP_NUMBER' => $this->faker->unique()->numberBetween(1000),
            'APP_STATUS_ID' => 3,
            'APP_STATUS' => 'COMPLETED'
            ];
        };
        return $this->state($state);
    }

    /**
     * 
     * @return type
     */
    public function canceled()
    {
        $state = function (array $attributes) {
            return [
            'APP_NUMBER' => $this->faker->unique()->numberBetween(1000),
            'APP_STATUS_ID' => 4,
            'APP_STATUS' => 'CANCELLED'
            ];
        };
        return $this->state($state);
    }

    /**
     * 
     * @return type
     */
    public function draft_minor_case()
    {
        $state = function (array $attributes) {
            $caseNumber = $this->faker->unique()->numberBetween(1, 1000);
            return [
            'APP_NUMBER' => $caseNumber,
            'APP_TITLE' => 'Case # ' . $caseNumber,
            'APP_STATUS_ID' => 1,
            'APP_STATUS' => 'DRAFT',
            'APP_UPDATE_DATE' => $this->faker->dateTimeBetween('-2 year', '-1 year')
            ];
        };
        return $this->state($state);
    }

    /**
     * 
     * @return type
     */
    public function draft_major_case()
    {
        $state = function (array $attributes) {
            $caseNumber = $this->faker->unique()->numberBetween(2000, 3000);
            return [
            'APP_NUMBER' => $caseNumber,
            'APP_TITLE' => 'Case # ' . $caseNumber,
            'APP_STATUS_ID' => 1,
            'APP_STATUS' => 'DRAFT',
            'APP_UPDATE_DATE' => $this->faker->dateTimeBetween('now', '+1 year')
            ];
        };
        return $this->state($state);
    }

}
