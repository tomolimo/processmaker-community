<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;
use ProcessMaker\Model\AdditionalTables;

class FieldsFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'FLD_UID' => G::generateUniqueID(),
            'ADD_TAB_UID' => G::generateUniqueID(),
            'FLD_INDEX' => 0,
            'FLD_NAME' => 'VAR_' . $this->faker->sentence(1),
            'FLD_DESCRIPTION' => $this->faker->sentence(2),
            'FLD_TYPE' => 'VARCHAR',
            'FLD_SIZE' => 255,
            'FLD_NULL' => 1,
            'FLD_AUTO_INCREMENT' => 0,
            'FLD_KEY' => 1,
            'FLD_TABLE_INDEX' => 0,
            'FLD_FOREIGN_KEY' => 0,
            'FLD_FOREIGN_KEY_TABLE' => '',
            'FLD_DYN_NAME' => '',
            'FLD_DYN_UID' => '',
            'FLD_FILTER' => 0,
        ];
    }

    /**
     * Create columns from a table with the foreign keys
     * @return type
     */
    public function foreign_keys()
    {
        $state = function (array $attributes) {
            return [
            'FLD_UID' => G::generateUniqueID(),
            'ADD_TAB_UID' => function () {
                $table = AdditionalTables::factory()->create(['ADD_TAB_OFFLINE' => 1]);
                return $table->ADD_TAB_UID;
            },
            'FLD_INDEX' => 0,
            'FLD_NAME' => 'VAR_' . $this->faker->sentence(1),
            'FLD_DESCRIPTION' => $this->faker->sentence(2),
            'FLD_TYPE' => 'VARCHAR',
            'FLD_SIZE' => 255,
            'FLD_NULL' => 1,
            'FLD_AUTO_INCREMENT' => 0,
            'FLD_KEY' => 1,
            'FLD_TABLE_INDEX' => 0,
            'FLD_FOREIGN_KEY' => 0,
            'FLD_FOREIGN_KEY_TABLE' => '',
            'FLD_DYN_NAME' => '',
            'FLD_DYN_UID' => '',
            'FLD_FILTER' => 0,
            ];
        };
        return $this->state($state);
    }

}
