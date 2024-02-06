<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class StepFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'STEP_UID' => G::generateUniqueID(),
            'PRO_UID' => G::generateUniqueID(),
            'TAS_UID' => G::generateUniqueID(),
            'STEP_TYPE_OBJ' => 'DYNAFORM',
            'STEP_UID_OBJ' => '0',
            'STEP_CONDITION' => 'None',
            'STEP_POSITION' => 0,
            'STEP_MODE' => 'EDIT'
        ];
    }

}
