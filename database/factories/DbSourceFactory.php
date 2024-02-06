<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;

class DbSourceFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        /**
         * @todo Determine if we need more base columns populated
         */
        $dbName = $this->faker->word;
        return [
            'DBS_UID' => G::generateUniqueID(),
            'PRO_UID' => function () {
                return \ProcessMaker\Model\Process::factory()->create()->PRO_UID;
            },
            'DBS_TYPE' => 'mysql',
            'DBS_SERVER' => $this->faker->localIpv4,
            'DBS_DATABASE_NAME' => $this->faker->word,
            'DBS_USERNAME' => $this->faker->userName,
            /**
             * @todo WHY figure out there's a magic value to the encryption here
             */
            'DBS_PASSWORD' => \G::encrypt($this->faker->password, $dbName, false, false) . "_2NnV3ujj3w",
            'DBS_PORT' => $this->faker->numberBetween(1000, 9000),
            'DBS_ENCODE' => 'utf8', // @todo Perhaps grab this from our definitions in DbConnections
            'DBS_CONNECTION_TYPE' => 'NORMAL', // @todo Determine what this value means
            'DBS_TNS' => null // @todo Determine what this value means
        ];
    }

}
