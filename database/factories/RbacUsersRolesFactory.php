<?php

namespace Database\Factories;

use App\Factories\Factory;
use Illuminate\Support\Str;

class RbacUsersRolesFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'USR_UID' => function () {
                $rbacUser = \ProcessMaker\Model\RbacUsers::factory()->create();
                return $rbacUser->USR_UID;
            },
            'ROL_UID' => function () {
                $rbacRole = \ProcessMaker\Model\RbacRoles::factory()->create();
                return $rbacRole->ROL_UID;
            }
        ];
    }

}
