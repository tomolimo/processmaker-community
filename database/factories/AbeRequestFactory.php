<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\AbeRequest::class, function (Faker $faker) {
    $process = \ProcessMaker\Model\Application::all()->random();
    $abeConfiguration = \ProcessMaker\Model\AbeConfiguration::all()->random();
    return [
        'ABE_REQ_UID' => G::generateUniqueID(),
        'ABE_UID' => $abeConfiguration->ABE_UID,
        'APP_UID' => $process->APP_UID,
        'DEL_INDEX' => 0,
        'ABE_REQ_SENT_TO' => $faker->email,
        'ABE_REQ_SUBJECT' => '',
        'ABE_REQ_BODY' => '',
        'ABE_REQ_DATE' => $faker->date(),
        'ABE_REQ_STATUS' => '',
        'ABE_REQ_ANSWERED' => $faker->numberBetween(1, 9)
    ];
});
