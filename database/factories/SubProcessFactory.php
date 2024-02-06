<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class SubProcessFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'SP_UID' => G::generateUniqueID(),
            'PRO_UID' => G::generateUniqueID(),
            'TAS_UID' => G::generateUniqueID(),
            'PRO_PARENT' => G::generateUniqueID(),
            'TAS_PARENT' => G::generateUniqueID(),
            'SP_TYPE' => '',
            'SP_SYNCHRONOUS' => 0,
            'SP_SYNCHRONOUS_TYPE' => '',
            'SP_SYNCHRONOUS_WAIT' => 0,
            'SP_VARIABLES_OUT' => '',
            'SP_VARIABLES_IN' => '',
            'SP_GRID_IN' => ''
        ];
    }

}
