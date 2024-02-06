<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class ProcessFilesFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'PRF_UID' => G::generateUniqueID(),
            'PRO_UID' => '',
            'USR_UID' => '',
            'PRF_UPDATE_USR_UID' => '',
            'PRF_PATH' => 'dummy_path',
            'PRF_TYPE' => '',
            'PRF_EDITABLE' => 1,
            'PRF_CREATE_DATE' => $this->faker->dateTime(),
            'PRF_UPDATE_DATE' => $this->faker->dateTime(),
        ];
    }

}
