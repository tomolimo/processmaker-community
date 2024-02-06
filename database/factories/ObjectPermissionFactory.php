<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class ObjectPermissionFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'OP_UID' => G::generateUniqueID(),
            'PRO_UID' => '',
            'TAS_UID' => '',
            'USR_UID' => '',
            'OP_USER_RELATION' => 1,
            'OP_TASK_SOURCE' => '',
            'OP_PARTICIPATE' => 0,
            'OP_OBJ_TYPE' => 'MSGS_HISTORY',
            'OP_OBJ_UID' => '',
            'OP_ACTION' => 'VIEW',
            'OP_CASE_STATUS' => 'ALL'
        ];
    }

}
