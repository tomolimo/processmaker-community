<?php

use Faker\Generator as Faker;

$factory->define(\ProcessMaker\Model\OauthClients::class, function(Faker $faker) {
    return [
        "CLIENT_ID" => $faker->word,
        "CLIENT_SECRET" => $faker->regexify("/[a-zA-Z]{6}/"),
        "CLIENT_NAME" => $faker->regexify("/[a-zA-Z]{6}/"),
        "CLIENT_DESCRIPTION" => $faker->text,
        "CLIENT_WEBSITE" => $faker->url,
        "REDIRECT_URI" => $faker->url,
        "USR_UID" => function() {
            return factory(\ProcessMaker\Model\User::class)->create()->USR_UID;
        }
    ];
});
