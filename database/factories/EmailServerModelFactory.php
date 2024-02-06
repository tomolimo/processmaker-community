<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class EmailServerModelFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'MESS_UID' => G::generateUniqueID(),
            'MESS_ENGINE' => 'MAIL',
            'MESS_SERVER' => '',
            'MESS_PORT' => 0,
            'MESS_INCOMING_SERVER' => '',
            'MESS_INCOMING_PORT' => 0,
            'MESS_RAUTH' => 0,
            'MESS_ACCOUNT' => '',
            'MESS_PASSWORD' => '',
            'MESS_FROM_MAIL' => '',
            'MESS_FROM_NAME' => '',
            'SMTPSECURE' => 'No',
            'MESS_TRY_SEND_INMEDIATLY' => 0,
            'MAIL_TO' => '',
            'MESS_DEFAULT' => 0,
            'OAUTH_CLIENT_ID' => '',
            'OAUTH_CLIENT_SECRET' => '',
            'OAUTH_REFRESH_TOKEN' => ''
        ];
    }

    /**
     * 
     * @return type
     */
    public function PHPMAILER()
    {
        $state = function (array $attributes) {
            return [
            'MESS_UID' => G::generateUniqueID(),
            'MESS_ENGINE' => 'PHPMAILER',
            'MESS_PORT' => $this->faker->numberBetween(400, 500),
            'MESS_INCOMING_SERVER' => '',
            'MESS_INCOMING_PORT' => 0,
            'MESS_RAUTH' => 1,
            'MESS_ACCOUNT' => $this->faker->email,
            'MESS_PASSWORD' => $this->faker->password,
            'MESS_FROM_MAIL' => $this->faker->email,
            'MESS_FROM_NAME' => $this->faker->name,
            'SMTPSECURE' => 'ssl',
            'MESS_TRY_SEND_INMEDIATLY' => 0,
            'MAIL_TO' => $this->faker->email,
            'MESS_DEFAULT' => 0,
            'OAUTH_CLIENT_ID' => '',
            'OAUTH_CLIENT_SECRET' => '',
            'OAUTH_REFRESH_TOKEN' => ''
            ];
        };
        return $this->state($state);
    }

    /**
     * 
     * @return type
     */
    public function IMAP()
    {
        $state = function (array $attributes) {
            return [
            'MESS_UID' => G::generateUniqueID(),
            'MESS_ENGINE' => 'IMAP',
            'MESS_PORT' => $this->faker->numberBetween(400, 500),
            'MESS_INCOMING_SERVER' => 'imap.' . $this->faker->domainName,
            'MESS_INCOMING_PORT' => $this->faker->numberBetween(400, 500),
            'MESS_RAUTH' => 1,
            'MESS_ACCOUNT' => $this->faker->email,
            'MESS_PASSWORD' => $this->faker->password,
            'MESS_FROM_MAIL' => $this->faker->email,
            'MESS_FROM_NAME' => $this->faker->name,
            'SMTPSECURE' => 'ssl',
            'MESS_TRY_SEND_INMEDIATLY' => 0,
            'MAIL_TO' => $this->faker->email,
            'MESS_DEFAULT' => 0,
            'OAUTH_CLIENT_ID' => '',
            'OAUTH_CLIENT_SECRET' => '',
            'OAUTH_REFRESH_TOKEN' => ''
            ];
        };
        return $this->state($state);
    }

    /**
     * 
     * @return type
     */
    public function GMAILAPI()
    {
        $state = function (array $attributes) {
            return [
            'MESS_UID' => G::generateUniqueID(),
            'MESS_ENGINE' => 'GMAILAPI',
            'MESS_PORT' => 0,
            'MESS_INCOMING_SERVER' => '',
            'MESS_INCOMING_PORT' => 0,
            'MESS_RAUTH' => 1,
            'MESS_ACCOUNT' => $this->faker->email,
            'MESS_PASSWORD' => '',
            'MESS_FROM_MAIL' => $this->faker->email,
            'MESS_FROM_NAME' => $this->faker->name,
            'SMTPSECURE' => 'No',
            'MESS_TRY_SEND_INMEDIATLY' => 0,
            'MAIL_TO' => $this->faker->email,
            'MESS_DEFAULT' => 0,
            'OAUTH_CLIENT_ID' => $this->faker->regexify("/[0-9]{12}-[a-z]{32}\.apps\.googleusercontent\.com/"),
            'OAUTH_CLIENT_SECRET' => $this->faker->regexify("/[a-z]{24}/"),
            'OAUTH_REFRESH_TOKEN' => $this->faker->regexify("/[a-z]{7}[a-zA-Z0-9]{355}==/")
            ];
        };
        return $this->state($state);
    }

}
