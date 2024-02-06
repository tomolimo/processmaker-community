<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;
use ProcessMaker\Model\InputDocument;
use ProcessMaker\Model\Process;

class InputDocumentFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'INP_DOC_UID' => G::generateUniqueID(),
            'PRO_UID' => function () {
                $process = Process::factory()->create();
                return $process->PRO_UID;
            },
            'INP_DOC_TITLE' => $this->faker->sentence(2),
            'INP_DOC_DESCRIPTION' => $this->faker->sentence(10),
            'INP_DOC_FORM_NEEDED' => 'VIRTUAL',
            'INP_DOC_ORIGINAL' => 'ORIGINAL',
            'INP_DOC_PUBLISHED' => 'PRIVATE',
            'INP_DOC_VERSIONING' => 0,
            'INP_DOC_DESTINATION_PATH' => '',
            'INP_DOC_TAGS' => 'INPUT',
            'INP_DOC_TYPE_FILE' => '.*',
            'INP_DOC_MAX_FILESIZE' => 0,
            'INP_DOC_MAX_FILESIZE_UNIT' => 'KB'
        ];
    }

}
