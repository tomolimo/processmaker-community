<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class AppNotesFactory extends Factory
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
            'APP_NUMBER' => $this->faker->unique()->numberBetween(5000),
            'USR_UID' => G::generateUniqueID(),
            'NOTE_DATE' => $this->faker->dateTime(),
            'NOTE_CONTENT' => $this->faker->sentence(3),
            'NOTE_TYPE' => 'USER',
            'NOTE_AVAILABILITY' => 'PUBLIC',
            'NOTE_ORIGIN_OBJ' => '',
            'NOTE_AFFECTED_OBJ1' => '',
            'NOTE_AFFECTED_OBJ2' => '',
            'NOTE_RECIPIENTS' => '',
        ];
    }

    /**
     * Create a case notes with the foreign keys
     * @return type
     */
    public function foreign_keys()
    {
        $state = function (array $attributes) {
            // Create values in the foreign key relations
            $application = \ProcessMaker\Model\Application::factory()->create();
            $user = \ProcessMaker\Model\User::factory()->create();

            // Return with default values
            return [
            'APP_UID' => $application->APP_UID,
            'APP_NUMBER' => $application->APP_NUMBER,
            'USR_UID' => $user->USR_UID,
            'NOTE_DATE' => $this->faker->dateTime(),
            'NOTE_CONTENT' => $this->faker->sentence(3),
            'NOTE_TYPE' => 'USER',
            'NOTE_AVAILABILITY' => 'PUBLIC',
            'NOTE_ORIGIN_OBJ' => '',
            'NOTE_AFFECTED_OBJ1' => '',
            'NOTE_AFFECTED_OBJ2' => '',
            'NOTE_RECIPIENTS' => '',
            ];
        };
        return $this->state($state);
    }

}
