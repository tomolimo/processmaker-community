<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

class ProcessFactory extends Factory
{

    public function definition(): array
    {
        $category = \ProcessMaker\Model\ProcessCategory::factory()->create();
        return [
            'PRO_UID' => G::generateUniqueID(),
            'PRO_ID' => $this->faker->unique()->numberBetween(2000),
            'PRO_TITLE' => $this->faker->sentence(3),
            'PRO_DESCRIPTION' => $this->faker->paragraph(3),
            'PRO_PARENT' => G::generateUniqueID(),
            'PRO_STATUS' => 'ACTIVE',
            'PRO_STATUS_ID' => 1,
            'PRO_TYPE' => 'NORMAL',
            'PRO_ASSIGNMENT' => 'FALSE',
            'PRO_TYPE_PROCESS' => 'PUBLIC',
            'PRO_UPDATE_DATE' => $this->faker->dateTime(),
            'PRO_CREATE_DATE' => $this->faker->dateTime(),
            'PRO_CREATE_USER' => '00000000000000000000000000000001',
            'PRO_DEBUG' => 0,
            'PRO_DYNAFORMS' => serialize([]),
            'PRO_ITEE' => 1,
            'PRO_ACTION_DONE' => serialize([]),
            'PRO_SUBPROCESS' => 0,
            'PRO_CATEGORY' => $category->CATEGORY_UID,
            'CATEGORY_ID' => $category->CATEGORY_ID
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

            return [
            'PRO_UID' => G::generateUniqueID(),
            'PRO_ID' => $this->faker->unique()->numberBetween(1000),
            'PRO_TITLE' => $this->faker->sentence(3),
            'PRO_DESCRIPTION' => $this->faker->paragraph(3),
            'PRO_PARENT' => G::generateUniqueID(),
            'PRO_STATUS' => 'ACTIVE',
            'PRO_STATUS_ID' => 1,
            'PRO_TYPE' => 'NORMAL',
            'PRO_ASSIGNMENT' => 'FALSE',
            'PRO_TYPE_PROCESS' => 'PUBLIC',
            'PRO_UPDATE_DATE' => $this->faker->dateTime(),
            'PRO_CREATE_DATE' => $this->faker->dateTime(),
            'PRO_CREATE_USER' => $user->USR_UID,
            'PRO_DEBUG' => 0,
            'PRO_DYNAFORMS' => serialize([]),
            'PRO_ITEE' => 1,
            'PRO_ACTION_DONE' => serialize([]),
            'PRO_SUBPROCESS' => 0,
            'PRO_CATEGORY' => function () {
                return \ProcessMaker\Model\ProcessCategory::factory()->create()->CATEGORY_UID;
            },
            ];
        };
        return $this->state($state);
    }
}
