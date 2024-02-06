<?php

namespace Database\Factories;

use App\Factories\Factory;
use Illuminate\Support\Str;

class TaskSchedulerFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->unique()->numberBetween(5000),
            'title' => $this->faker->title,
            'startingTime' => $this->faker->dateTime(),
            'endingTime' => $this->faker->dateTime(),
            'everyOn' => "",
            'interval' => "",
            'description' => "",
            'expression' => "",
            'body' => "",
            'type' => "",
            'category' => "emails_notifications", //emails_notifications, case_actions, plugins, processmaker_sync
            'system' => "",
            'timezone' => "",
            'enable' => "",
            'creation_date' => $this->faker->dateTime(),
            'last_update' => $this->faker->dateTime()
        ];
    }

}
