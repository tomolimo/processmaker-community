<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class AbeRequestFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $process = \ProcessMaker\Model\Process::factory()->create();
        $abeConfiguration = \ProcessMaker\Model\AbeConfiguration::factory()->create([
            'PRO_UID' => $process->PRO_UID
        ]);
        $application = \ProcessMaker\Model\Application::factory()->create([
            'PRO_UID' => $process->PRO_UID
        ]);
        return [
            'ABE_REQ_UID' => G::generateUniqueID(),
            'ABE_UID' => $abeConfiguration->ABE_UID,
            'APP_UID' => $application->APP_UID,
            'DEL_INDEX' => 0,
            'ABE_REQ_SENT_TO' => $this->faker->email,
            'ABE_REQ_SUBJECT' => '',
            'ABE_REQ_BODY' => '',
            'ABE_REQ_DATE' => $this->faker->date(),
            'ABE_REQ_STATUS' => '',
            'ABE_REQ_ANSWERED' => $this->faker->numberBetween(1, 9)
        ];
    }

}
