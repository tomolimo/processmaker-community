<?php

namespace Database\Factories;

use App\Factories\Factory;
use Illuminate\Support\Str;

class OauthClientsFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "CLIENT_ID" => $this->faker->unique()->word(),
            "CLIENT_SECRET" => $this->faker->regexify("/[a-zA-Z]{6}/"),
            "CLIENT_NAME" => $this->faker->regexify("/[a-zA-Z]{6}/"),
            "CLIENT_DESCRIPTION" => $this->faker->text,
            "CLIENT_WEBSITE" => $this->faker->url,
            "REDIRECT_URI" => $this->faker->url,
            "USR_UID" => function () {
                return \ProcessMaker\Model\User::factory()->create()->USR_UID;
            }
        ];
    }

}
