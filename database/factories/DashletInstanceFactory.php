<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;

class DashletInstanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'DAS_INS_UID' => G::generateUniqueID(),
            'DAS_UID' => G::generateUniqueID(),
            'DAS_INS_OWNER_TYPE' => 'USER',
            'DAS_INS_OWNER_UID' => G::generateUniqueID(),
        ];
    }
}
