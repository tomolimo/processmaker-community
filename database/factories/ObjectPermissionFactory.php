<?php

/**
 * Model factory for a process
 */
use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\ObjectPermission::class, function(Faker $faker) {
    return [
        'OP_UID' => G::generateUniqueID(),
        'PRO_UID' => '',
        'TAS_UID' => '',
        'USR_UID' => '',
        'OP_USER_RELATION' => 1,
        'OP_TASK_SOURCE' => '',
        'OP_PARTICIPATE' => 0,
        'OP_OBJ_TYPE' => 'MSGS_HISTORY',
        'OP_OBJ_UID' => '',
        'OP_ACTION' => 'VIEW',
        'OP_CASE_STATUS' => 'ALL'
    ];
});
