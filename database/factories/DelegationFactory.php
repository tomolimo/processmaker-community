<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\Delegation::class, function(Faker $faker) {
    // Return with default values
    return [
        'APP_UID' => G::generateUniqueID(),
        'DEL_INDEX' => 1,
        'APP_NUMBER' => $faker->unique()->numberBetween(1, 100000),
        'DEL_PREVIOUS' => 0,
        'PRO_UID' => G::generateUniqueID(),
        'TAS_UID' => G::generateUniqueID(),
        'USR_UID' => G::generateUniqueID(),
        'DEL_TYPE' => 'NORMAL',
        'DEL_THREAD' => 1,
        'DEL_THREAD_STATUS' => 'OPEN',
        'DEL_PRIORITY' => 3,
        'DEL_DELEGATE_DATE' => $faker->dateTime(),
        'DEL_INIT_DATE' => $faker->dateTime(),
        'DEL_TASK_DUE_DATE' => $faker->dateTime(),
        'DEL_RISK_DATE' => $faker->dateTime(),
        'USR_ID' => 0,
        'PRO_ID' => 0,
        'TAS_ID' => 0,
        'DEL_DATA' => ''
    ];
});

// Create a delegation with the foreign keys
$factory->state(\ProcessMaker\Model\Delegation::class, 'foreign_keys', function (Faker $faker) {
    // Create values in the foreign key relations
    $application = factory(\ProcessMaker\Model\Application::class)->create();
    $process = factory(\ProcessMaker\Model\Process::class)->create();
    $task = factory(\ProcessMaker\Model\Task::class)->create();
    $user = factory(\ProcessMaker\Model\User::class)->create();

    // Return with default values
    return [
        'APP_UID' => $application->APP_UID,
        'DEL_INDEX' => 1,
        'APP_NUMBER' => $application->APP_NUMBER,
        'DEL_PREVIOUS' => 0,
        'PRO_UID' => $process->PRO_UID,
        'TAS_UID' => $task->TAS_UID,
        'USR_UID' => $user->USR_UID,
        'DEL_TYPE' => 'NORMAL',
        'DEL_THREAD' => 1,
        'DEL_THREAD_STATUS' => 'OPEN',
        'DEL_PRIORITY' => 3,
        'DEL_DELEGATE_DATE' => $faker->dateTime(),
        'DEL_INIT_DATE' => $faker->dateTime(),
        'DEL_TASK_DUE_DATE' => $faker->dateTime(),
        'DEL_RISK_DATE' => $faker->dateTime(),
        'USR_ID' => $user->USR_ID,
        'PRO_ID' => $process->PRO_ID,
        'TAS_ID' => $task->TAS_ID,
        'DEL_DATA' => ''
    ];
});

// Create a open delegation
$factory->state(\ProcessMaker\Model\Delegation::class, 'open', function (Faker $faker) {
    // Create dates with sense
    $delegateDate = $faker->dateTime();
    $initDate = $faker->dateTimeInInterval($delegateDate, '+30 minutes');
    $riskDate = $faker->dateTimeInInterval($initDate, '+1 day');
    $taskDueDate = $faker->dateTimeInInterval($riskDate, '+1 day');

    return [
        'DEL_THREAD_STATUS' => 'OPEN',
        'DEL_DELEGATE_DATE' => $delegateDate,
        'DEL_INIT_DATE' => $initDate,
        'DEL_RISK_DATE' => $riskDate,
        'DEL_TASK_DUE_DATE' => $taskDueDate,
        'DEL_FINISH_DATE' => null
    ];
});

// Create a closed delegation
$factory->state(\ProcessMaker\Model\Delegation::class, 'closed', function (Faker $faker) {
    // Create dates with sense
    $delegateDate = $faker->dateTime();
    $initDate = $faker->dateTimeInInterval($delegateDate, '+30 minutes');
    $riskDate = $faker->dateTimeInInterval($initDate, '+1 day');
    $taskDueDate = $faker->dateTimeInInterval($riskDate, '+1 day');
    $finishDate = $faker->dateTimeInInterval($initDate, '+10 days');

    return [
        'DEL_THREAD_STATUS' => 'CLOSED',
        'DEL_DELEGATE_DATE' => $delegateDate,
        'DEL_INIT_DATE' => $initDate,
        'DEL_RISK_DATE' => $riskDate,
        'DEL_TASK_DUE_DATE' => $taskDueDate,
        'DEL_FINISH_DATE' => $finishDate
    ];
});
