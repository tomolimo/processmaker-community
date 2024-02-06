<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class RbacAuthenticationSourceFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'AUTH_SOURCE_UID' => G::generateUniqueID(),
            'AUTH_SOURCE_NAME' => $this->faker->title,
            'AUTH_SOURCE_PROVIDER' => 'ldapAdvanced',
            'AUTH_SOURCE_SERVER_NAME' => $this->faker->domainName,
            'AUTH_SOURCE_PORT' => $this->faker->numberBetween(100, 1000),
            'AUTH_SOURCE_ENABLED_TLS' => 0,
            'AUTH_SOURCE_VERSION' => 3,
            'AUTH_SOURCE_BASE_DN' => 'dc=processmaker,dc=test',
            'AUTH_ANONYMOUS' => 0,
            'AUTH_SOURCE_SEARCH_USER' => $this->faker->userName,
            'AUTH_SOURCE_PASSWORD' => $this->faker->password,
            'AUTH_SOURCE_ATTRIBUTES' => '',
            'AUTH_SOURCE_OBJECT_CLASSES' => '',
            'AUTH_SOURCE_DATA' => 'a:8:{s:9:"LDAP_TYPE";s:4:"ldap";s:25:"AUTH_SOURCE_AUTO_REGISTER";s:1:"0";s:31:"AUTH_SOURCE_IDENTIFIER_FOR_USER";s:3:"uid";s:24:"AUTH_SOURCE_USERS_FILTER";s:0:"";s:22:"AUTH_SOURCE_RETIRED_OU";s:0:"";s:20:"AUTH_SOURCE_SHOWGRID";s:2:"on";s:26:"AUTH_SOURCE_GRID_ATTRIBUTE";a:1:{i:1;a:2:{s:13:"attributeLdap";s:4:"test";s:13:"attributeUser";s:13:"USR_FIRSTNAME";}}s:20:"LDAP_PAGE_SIZE_LIMIT";i:1000;}'
        ];
    }

}
