<?php

/**
 * Model factory for a process
 */
use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\Task::class, function(Faker $faker) {
    return [
        'PRO_UID' => G::generateUniqueID(),
        'PRO_ID' => $faker->unique()->numberBetween(),
        'TAS_UID' => G::generateUniqueID(),
        'TAS_ID' => $faker->unique()->numberBetween(),
        'TAS_TITLE' => $faker->sentence(2),
        'TAS_TYPE' => 'NORMAL',
        'TAS_TYPE_DAY' => 1,
        'TAS_DURATION' => 1,
        'TAS_ASSIGN_TYPE' => 'BALANCED',
        'TAS_ASSIGN_VARIABLE' => '@@SYS_NEXT_USER_TO_BE_ASSIGNED',
        'TAS_MI_INSTANCE_VARIABLE' => '@@SYS_VAR_TOTAL_INSTANCE',
        'TAS_MI_COMPLETE_VARIABLE' => '@@SYS_VAR_TOTAL_INSTANCES_COMPLETE',
        'TAS_ASSIGN_LOCATION' => 'FALSE',
        'TAS_ASSIGN_LOCATION_ADHOC' => 'FALSE',
        'TAS_TRANSFER_FLY' => 'FALSE',
        'TAS_LAST_ASSIGNED' => 0,
        'TAS_USER' => 0,
        'TAS_CAN_UPLOAD' => 'FALSE',
        'TAS_CAN_CANCEL' => 'FALSE',
        'TAS_OWNER_APP' => 'FALSE',
        'TAS_CAN_SEND_MESSAGE' => 'FALSE',
        'TAS_SEND_LAST_EMAIL' => 'FALSE',
    ];
});

// Create a delegation with the foreign keys
$factory->state(\ProcessMaker\Model\Task::class, 'foreign_keys', function (Faker $faker) {
    $process = factory(\ProcessMaker\Model\Process::class)->create();
    return [
        'PRO_UID' => $process->PRO_UID,
        'PRO_ID' => $process->PRO_ID,
        'TAS_UID' => G::generateUniqueID(),
        'TAS_ID' => $faker->unique()->numberBetween(1, 200000),
        'TAS_TITLE' => $faker->sentence(2),
        'TAS_TYPE' => 'NORMAL',
        'TAS_TYPE_DAY' => 1,
        'TAS_DURATION' => 1,
        'TAS_ASSIGN_TYPE' => 'BALANCED',
        'TAS_ASSIGN_VARIABLE' => '@@SYS_NEXT_USER_TO_BE_ASSIGNED',
        'TAS_MI_INSTANCE_VARIABLE' => '@@SYS_VAR_TOTAL_INSTANCE',
        'TAS_MI_COMPLETE_VARIABLE' => '@@SYS_VAR_TOTAL_INSTANCES_COMPLETE',
        'TAS_ASSIGN_LOCATION' => 'FALSE',
        'TAS_ASSIGN_LOCATION_ADHOC' => 'FALSE',
        'TAS_TRANSFER_FLY' => 'FALSE',
        'TAS_LAST_ASSIGNED' => 0,
        'TAS_USER' => 0,
        'TAS_CAN_UPLOAD' => 'FALSE',
        'TAS_CAN_CANCEL' => 'FALSE',
        'TAS_OWNER_APP' => 'FALSE',
        'TAS_CAN_SEND_MESSAGE' => 'FALSE',
        'TAS_SEND_LAST_EMAIL' => 'FALSE',
    ];
});
