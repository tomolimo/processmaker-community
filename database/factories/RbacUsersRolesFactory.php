<?php
/**
 * Model factory for a role assigned to an user
 */

$factory->define(\ProcessMaker\Model\RbacUsersRoles::class, function() {
    return [
        'USR_UID' => function() {
            $rbacUser = factory(\ProcessMaker\Model\RbacUsers::class)->create();
            return $rbacUser->USR_UID;
        },
        'ROL_UID' => function() {
            $rbacRole = factory(\ProcessMaker\Model\RbacRoles::class)->create();
            return $rbacRole->ROL_UID;
        }
    ];
});
