<?php

namespace Database\Factories;

use App\Factories\Factory;
use Illuminate\Support\Str;

class LicenseManagerFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "LICENSE_UID" => $this->faker->regexify("/[a-zA-Z]{32}/"),
            "LICENSE_USER" => $this->faker->name,
            "LICENSE_START" => 0,
            "LICENSE_END" => 0,
            "LICENSE_SPAN" => 0,
            "LICENSE_STATUS" => 'ACTIVE',
            "LICENSE_DATA" => '',
            "LICENSE_PATH" => '',
            "LICENSE_WORKSPACE" => '',
            "LICENSE_TYPE" => 'ONPREMISE'
        ];
    }

}
