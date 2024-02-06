<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class AbeConfigurationFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $process = \ProcessMaker\Model\Process::factory()->create();
        $dynaform = \ProcessMaker\Model\Dynaform::factory()->create([
            'PRO_UID' => $process->PRO_UID
        ]);
        $task = \ProcessMaker\Model\Task::factory()->create([
            'PRO_UID' => $process->PRO_UID
        ]);
        $emailServer = \ProcessMaker\Model\EmailServerModel::factory()->create();
        return [
            'ABE_UID' => G::generateUniqueID(),
            'PRO_UID' => $process->PRO_UID,
            'TAS_UID' => $task->TAS_UID,
            'ABE_TYPE' => $this->faker->randomElement(['', 'LINK']),
            'ABE_TEMPLATE' => 'actionByEmail.html',
            'ABE_DYN_TYPE' => 'NORMAL',
            'DYN_UID' => $dynaform->DYN_UID,
            'ABE_EMAIL_FIELD' => 'admin@processmaker.com',
            'ABE_ACTION_FIELD' => '',
            'ABE_CASE_NOTE_IN_RESPONSE' => $this->faker->randomElement(['0', '1']),
            'ABE_FORCE_LOGIN' => $this->faker->randomElement(['0', '1']),
            'ABE_CREATE_DATE' => $this->faker->dateTime(),
            'ABE_UPDATE_DATE' => $this->faker->dateTime(),
            'ABE_SUBJECT_FIELD' => '',
            'ABE_MAILSERVER_OR_MAILCURRENT' => 0,
            'ABE_CUSTOM_GRID' => serialize([]),
            'ABE_EMAIL_SERVER_UID' => $emailServer->MESS_UID,
            'ABE_ACTION_BODY_FIELD' => '',
            'ABE_EMAIL_SERVER_RECEIVER_UID' => ''
        ];
    }

}
