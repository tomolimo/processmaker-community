<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class ElementTaskRelationFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'ETR_UID' => G::generateUniqueID(),
            'PRJ_UID' => G::generateUniqueID(),
            'ELEMENT_UID' => G::generateUniqueID(),
            'ELEMENT_TYPE' => 'bpmnEvent',
            'TAS_UID' => G::generateUniqueID(),
        ];
    }

}
