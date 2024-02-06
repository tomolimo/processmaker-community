<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class UserReportingFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'USR_UID' => G::generateUniqueID(),
            'TAS_UID' => G::generateUniqueID(),
            'PRO_UID' => G::generateUniqueID(),
            'MONTH' => 12,
            'YEAR' => 2020,
        ];
    }

}
