<?php
/**
 * Model factory for a process
 */
use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\TaskUser::class, function(Faker $faker) {
    return [
        'TAS_UID' => function() {
            $task = factory(\ProcessMaker\Model\Task::class)->create();
            return $task->TAS_UID;
        },
        'TU_TYPE' => 1,
        'TU_RELATION' => 1
    ];
});