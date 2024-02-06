<?php

namespace Database\Factories;

use App\Factories\Factory;
use Illuminate\Support\Str;

class CaseListFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'CAL_ID' => $this->faker->unique()->numberBetween(1, 5000),
            'CAL_TYPE' => 'inbox',
            'CAL_NAME' => $this->faker->title,
            'CAL_DESCRIPTION' => $this->faker->text,
            'ADD_TAB_UID' => function () {
                $table = \ProcessMaker\Model\AdditionalTables::factory()->create();
                return $table->ADD_TAB_UID;
            },
            'CAL_COLUMNS' => '[]',
            'USR_ID' => function () {
                $user = \ProcessMaker\Model\User::factory()->create();
                return $user->USR_ID;
            },
            'CAL_ICON_LIST' => 'deafult.png',
            'CAL_ICON_COLOR' => 'red',
            'CAL_ICON_COLOR_SCREEN' => 'blue',
            'CAL_CREATE_DATE' => $this->faker->dateTime(),
            'CAL_UPDATE_DATE' => $this->faker->dateTime()
        ];
    }

}
