<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\AbeRequest::class, function (Faker $faker) {
    $process = factory(\ProcessMaker\Model\Process::class)->create();
    $abeConfiguration = factory(\ProcessMaker\Model\AbeConfiguration::class)->create([
        'PRO_UID' => $process->PRO_UID
    ]);
    $application = factory(\ProcessMaker\Model\Application::class)->create([
        'PRO_UID' => $process->PRO_UID
    ]);
    return [
        'ABE_REQ_UID' => G::generateUniqueID(),
        'ABE_UID' => $abeConfiguration->ABE_UID,
        'APP_UID' => $application->APP_UID,
        'DEL_INDEX' => 0,
        'ABE_REQ_SENT_TO' => $faker->email,
        'ABE_REQ_SUBJECT' => '',
        'ABE_REQ_BODY' => '',
        'ABE_REQ_DATE' => $faker->date(),
        'ABE_REQ_STATUS' => '',
        'ABE_REQ_ANSWERED' => $faker->numberBetween(1, 9)
    ];
});
