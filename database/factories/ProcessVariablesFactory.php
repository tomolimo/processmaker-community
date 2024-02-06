<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;
use ProcessMaker\Model\ProcessVariables;

class ProcessVariablesFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'VAR_UID' => G::generateUniqueID(),
            'PRO_ID' => G::generateUniqueID(),
            'PRJ_UID' => G::generateUniqueID(),
            'VAR_NAME' => $this->faker->word,
            'VAR_FIELD_TYPE' => G::generateUniqueID(),
            'VAR_FIELD_TYPE_ID' => G::generateUniqueID(),
            'VAR_FIELD_SIZE' => 10,
            'VAR_LABEL' => 'string',
            'VAR_DBCONNECTION' => 'workflow',
            'VAR_SQL' => '',
            'VAR_NULL' => 0,
            'VAR_DEFAULT' => '',
            'VAR_ACCEPTED_VALUES' => '[]',
            'INP_DOC_UID' => ''
        ];
    }

    /**
     * Create a processVariables with the foreign keys
     * @return type
     */
    public function foreign_keys()
    {
        $state = function (array $attributes) {
            $types = ['string', 'integer', 'float', 'boolean', 'datetime', 'grid', 'array', 'file', 'multiplefile', 'object'];
            $varType = $this->faker->randomElement($types);
            $varTypeId = array_search($varType, $types) + 1;
            // Create values in the foreign key relations
            $process = \ProcessMaker\Model\Process::factory()->create();

            return [
            'VAR_UID' => G::generateUniqueID(),
            'PRO_ID' => $process->PRO_ID,
            'PRJ_UID' => $process->PRO_UID,
            'VAR_NAME' => $this->faker->word,
            'VAR_FIELD_TYPE' => $varType,
            'VAR_FIELD_TYPE_ID' => $varTypeId,
            'VAR_FIELD_SIZE' => 10,
            'VAR_LABEL' => 'string',
            'VAR_DBCONNECTION' => 'workflow',
            'VAR_SQL' => '',
            'VAR_NULL' => 0,
            'VAR_DEFAULT' => '',
            'VAR_ACCEPTED_VALUES' => '[]',
            'INP_DOC_UID' => ''
            ];
        };
        return $this->state($state);
    }

}
