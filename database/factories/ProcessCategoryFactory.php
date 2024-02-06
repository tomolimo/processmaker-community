<?php
/**
 * Model factory for a process category
 */
use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\ProcessCategory::class, function (Faker $faker) {
    return [
        'CATEGORY_UID' => G::generateUniqueID(),
        'CATEGORY_PARENT' => '',
        'CATEGORY_NAME' => $faker->sentence(5),
        'CATEGORY_ICON' => '',
    ];
});
