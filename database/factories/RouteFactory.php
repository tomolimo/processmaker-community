<?php
/**
 * Model factory for a process
 */
use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\Route::class, function(Faker $faker) {
    return [
        'PRO_UID' => function() {
            $process = factory(\ProcessMaker\Model\Process::class)->create();
            return $process->PRO_UID;
        },
        'TAS_UID' => function() {
            $task = factory(\ProcessMaker\Model\Task::class)->create();
            return $task->TAS_UID;
        },
        'ROU_UID' => G::generateUniqueID(),
        'ROU_PARENT' => 0,
        'ROU_CASE' => 1,
        'ROU_TYPE' => 'SEQUENTIAL'
    ];
});