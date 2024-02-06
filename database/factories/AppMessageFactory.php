<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class AppMessageFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'APP_MSG_UID' => G::generateUniqueID(),
            'MSG_UID' => '',
            'APP_UID' => function () {
                return \ProcessMaker\Model\Application::factory()->create()->APP_UID;
            },
            'DEL_INDEX' => 1,
            'APP_MSG_TYPE' => 'ROUTING',
            'APP_MSG_TYPE_ID' => 0,
            'APP_MSG_SUBJECT' => $this->faker->title,
            'APP_MSG_FROM' => $this->faker->email,
            'APP_MSG_TO' => $this->faker->email,
            'APP_MSG_BODY' => $this->faker->text,
            'APP_MSG_DATE' => $this->faker->dateTime(),
            'APP_MSG_CC' => '',
            'APP_MSG_BCC' => '',
            'APP_MSG_TEMPLATE' => '',
            'APP_MSG_STATUS' => 'pending',
            'APP_MSG_STATUS_ID' => 1,
            'APP_MSG_ATTACH' => '',
            'APP_MSG_SEND_DATE' => $this->faker->dateTime(),
            'APP_MSG_SHOW_MESSAGE' => 1,
            'APP_MSG_ERROR' => '',
            'PRO_ID' => function () {
                return \ProcessMaker\Model\Process::factory()->create()->PRO_ID;
            },
            'TAS_ID' => function () {
                return \ProcessMaker\Model\Task::factory()->create()->TAS_ID;
            },
            'APP_NUMBER' => 1
        ];
    }

}
