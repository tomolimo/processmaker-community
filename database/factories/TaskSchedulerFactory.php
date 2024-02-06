<?php

/**
 * Model factory for a task scheduler
 */
use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\TaskScheduler::class, function (Faker $faker) {
    return [
        'id' => G::generateUniqueID(),
        'title' => $faker->title,
        'startingTime' => $faker->dateTime(),
        'endingTime' => $faker->dateTime(),
        'everyOn' => "",
        'interval' => "",
        'description' => "",
        'expression' => "",
        'body' => "",
        'type' => "",
        'category' => "emails_notifications", //emails_notifications, case_actions, plugins, processmaker_sync
        'system' => "",
        'timezone' => "",
        'enable' => "",
        'creation_date' => $faker->dateTime(),
        'last_update' => $faker->dateTime()
    ];
});
