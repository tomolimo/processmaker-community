<?php

/**
 * Model factory for a input document.
 */
use Faker\Generator as Faker;
use ProcessMaker\Model\InputDocument;
use ProcessMaker\Model\Process;

$factory->define(InputDocument::class, function(Faker $faker) {
    return [
        'INP_DOC_UID' => G::generateUniqueID(),
        'INP_DOC_ID' => $faker->unique()->numberBetween(1, 10000),
        'PRO_UID' => function() {
            $process = factory(Process::class)->create();
            return $process->PRO_UID;
        },
        'INP_DOC_TITLE' => $faker->sentence(2),
        'INP_DOC_DESCRIPTION' => $faker->sentence(10),
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
});
