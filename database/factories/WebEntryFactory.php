<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class WebEntryFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'WE_UID' => G::generateUniqueID(),
            'PRO_UID' => G::generateUniqueID(),
            'TAS_UID' => G::generateUniqueID(),
            'DYN_UID' => G::generateUniqueID(),
            'USR_UID' => G::generateUniqueID(),
            'WE_METHOD' => $this->faker->randomElement(['WS', 'HTML']),
            'WE_INPUT_DOCUMENT_ACCESS' => $this->faker->randomElement([0, 1]),
            'WE_DATA' => G::generateUniqueID() . '.php',
            'WE_CREATE_USR_UID' => G::generateUniqueID(),
            'WE_UPDATE_USR_UID' => G::generateUniqueID(),
            'WE_CREATE_DATE' => $this->faker->date('Y-m-d H:i:s', 'now'),
            'WE_UPDATE_DATE' => $this->faker->date('Y-m-d H:i:s', 'now'),
            'WE_TYPE' => $this->faker->randomElement(['SINGLE', 'MULTIPLE']),
            'WE_CUSTOM_TITLE' => $this->faker->words(5, true),
            'WE_AUTHENTICATION' => $this->faker->randomElement(['LOGIN_REQUIRED', 'ANONYMOUS']),
            'WE_HIDE_INFORMATION_BAR' => $this->faker->randomElement(['0', '1']),
            'WE_HIDE_ACTIVE_SESSION_WARNING' => $this->faker->randomElement(['0', '1']),
            'WE_CALLBACK' => $this->faker->randomElement(['PROCESSMAKER', 'CUSTOM', 'CUSTOM_CLEAR']),
            'WE_CALLBACK_URL' => $this->faker->url,
            'WE_LINK_GENERATION' => $this->faker->randomElement(['DEFAULT', 'ADVANCED']),
            'WE_LINK_SKIN' => 'classic',
            'WE_LINK_LANGUAGE' => 'en',
            'WE_LINK_DOMAIN' => $this->faker->domainName,
            'WE_SHOW_IN_NEW_CASE' => $this->faker->randomElement(['0', '1'])
        ];
    }
}
