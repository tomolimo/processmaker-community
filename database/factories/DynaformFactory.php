<?php

namespace Database\Factories;

use App\Factories\Factory;
use G;
use Illuminate\Support\Str;
use ProcessMaker\Model\Process;

class DynaformFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = $this->faker->dateTime();
        return [
            'DYN_UID' => G::generateUniqueID(),
            'DYN_TITLE' => $this->faker->sentence(2),
            'DYN_DESCRIPTION' => $this->faker->sentence(5),
            'PRO_UID' => function () {
                $process = Process::factory()->create();
                return $process->PRO_UID;
            },
            'DYN_TYPE' => 'xmlform',
            'DYN_FILENAME' => '',
            'DYN_CONTENT' => '',
            'DYN_LABEL' => '',
            'DYN_VERSION' => 2,
            'DYN_UPDATE_DATE' => $date->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Create a dynaform with the foreign keys
     * @return type
     */
    public function foreign_keys()
    {
        $state = function (array $attributes) {
            $date = $this->faker->dateTime();
            return [
            'DYN_UID' => G::generateUniqueID(),
            'DYN_TITLE' => $this->faker->sentence(2),
            'DYN_DESCRIPTION' => $this->faker->sentence(5),
            'PRO_UID' => function () {
                $process = Process::factory()->create();
                return $process->PRO_UID;
            },
            'DYN_TYPE' => 'xmlform',
            'DYN_FILENAME' => '',
            'DYN_CONTENT' => '',
            'DYN_LABEL' => '',
            'DYN_VERSION' => 2,
            'DYN_UPDATE_DATE' => $date->format('Y-m-d H:i:s'),
            ];
        };
        return $this->state($state);
    }

    /**
     * Create a dynaform with translations defined: ["es", "es-Es"]
     * @return type
     */
    public function translations()
    {
        $state = function (array $attributes) {
            $date = $this->faker->dateTime();
            return [
            'DYN_UID' => G::generateUniqueID(),
            'DYN_TITLE' => $this->faker->sentence(2),
            'DYN_DESCRIPTION' => $this->faker->sentence(5),
            'PRO_UID' => function () {
                $process = Process::factory()->create();
                return $process->PRO_UID;
            },
            'DYN_TYPE' => 'xmlform',
            'DYN_FILENAME' => '',
            'DYN_CONTENT' => '',
            'DYN_LABEL' => '{"es":{"Project-Id-Version":"PM 4.0.1","POT-Creation-Date":"","PO-Revision-Date":"2019-09-11 12:02-0400","Last-Translator":"Colosa <colosa@colosa.com>","Language-Team":"Colosa Developers Team <developers@colosa.com>","MIME-Version":"1.0","Content-Type":"text\/plain; charset=utf-8","Content-Transfer_Encoding":"8bit","X-Poedit-SourceCharset":"utf-8","Content-Transfer-Encoding":"8bit","File-Name":"Test-v2.es.po","X-Generator":"Poedit 1.8.11","X-Poedit-Language":"en","X-Poedit-Country":".","Labels":[{"msgid":"Test without dependent fields","msgstr":"Ejemplo sin campos dependientes"},{"msgid":"Incident Type:","msgstr":"Tipo de incidente:"},{"msgid":"- Select -","msgstr":"- Seleccionar -"},{"msgid":"Incident Sub Type:","msgstr":"Sub tipo de incidente:"},{"msgid":"Test with dependent fields","msgstr":"Ejemplo con campos dependientes"},{"msgid":"Health\/Safety","msgstr":"Salud\/Seguridad"},{"msgid":"Environment","msgstr":"Ambiente"},{"msgid":"Fatality","msgstr":"Ambiente"},{"msgid":"Lost Time Injury","msgstr":"Ambiente"},{"msgid":"Environment","msgstr":"Ambiente"},{"msgid":"Medical Treatment Injury","msgstr":"Lesiones de tratamiento m\u00e9dico"},{"msgid":"Chemical\/Substance Spill","msgstr":"Derrame qu\u00edmico \/ de sustancias"},{"msgid":"Fire\/Explosion","msgstr":"Fuego\/Explosion"},{"msgid":"Offsite Release","msgstr":"Lanzamiento fuera del sitio"}]},"es-Es":{"Project-Id-Version":"PM 4.0.1","POT-Creation-Date":"","PO-Revision-Date":"2019-09-11 12:02-0400","Last-Translator":"Colosa <colosa@colosa.com>","Language-Team":"Colosa Developers Team <developers@colosa.com>","MIME-Version":"1.0","Content-Type":"text\/plain; charset=utf-8","Content-Transfer_Encoding":"8bit","X-Poedit-SourceCharset":"utf-8","Content-Transfer-Encoding":"8bit","File-Name":"Test-v2.es-Es.po","X-Generator":"Poedit 1.8.11","X-Poedit-Language":"en","X-Poedit-Country":".","Labels":[{"msgid":"Test without dependent fields","msgstr":"Ejemplo sin campos dependientes"},{"msgid":"Incident Type:","msgstr":"Tipo de incidente:"},{"msgid":"- Select -","msgstr":"- Seleccionar -"},{"msgid":"Incident Sub Type:","msgstr":"Sub tipo de incidente:"},{"msgid":"Test with dependent fields","msgstr":"Ejemplo con campos dependientes"},{"msgid":"Health\/Safety","msgstr":"Salud\/Seguridad"},{"msgid":"Environment","msgstr":"Ambiente"},{"msgid":"Fatality","msgstr":"Ambiente"},{"msgid":"Lost Time Injury","msgstr":"Ambiente"},{"msgid":"Environment","msgstr":"Ambiente"},{"msgid":"Medical Treatment Injury","msgstr":"Lesiones de tratamiento m\u00e9dico"},{"msgid":"Chemical\/Substance Spill","msgstr":"Derrame qu\u00edmico \/ de sustancias"},{"msgid":"Fire\/Explosion","msgstr":"Fuego\/Explosion"},{"msgid":"Offsite Release","msgstr":"Lanzamiento fuera del sitio"}]}}',
            'DYN_VERSION' => 2,
            'DYN_UPDATE_DATE' => $date->format('Y-m-d H:i:s'),
            ];
        };
        return $this->state($state);
    }

}
