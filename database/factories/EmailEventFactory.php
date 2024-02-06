<?php

namespace Database\Factories;

use App\Factories\Factory;
use Illuminate\Support\Str;

class EmailEventFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $bpmnEvent = \ProcessMaker\Model\BpmnEvent::factory()->create();
        return [
            'EMAIL_EVENT_UID' => $this->faker->regexify("/[a-zA-Z]{32}/"),
            'PRJ_UID' => $bpmnEvent->PRJ_UID,
            'EVN_UID' => $bpmnEvent->EVN_UID,
            'EMAIL_EVENT_FROM' => $this->faker->email,
            'EMAIL_EVENT_TO' => $this->faker->email,
            'EMAIL_EVENT_SUBJECT' => $this->faker->title,
            'PRF_UID' => function () {
                return \ProcessMaker\Model\ProcessFiles::factory()->create()->PRF_UID;
            },
            'EMAIL_SERVER_UID' => function () {
                return \ProcessMaker\Model\EmailServerModel::factory()->create()->MESS_UID;
            },
        ];
    }

}
