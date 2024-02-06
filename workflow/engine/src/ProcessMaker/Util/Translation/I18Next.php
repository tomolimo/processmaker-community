<?php

namespace ProcessMaker\Util\Translation;

use stdClass;

/**
 * Class I18next
 * @package ProcessMaker\Util\Translation
 */
class I18Next
{
    protected $languages;

    /**
     * I18next constructor.
     */
    public function __construct()
    {
        $this->languages = new stdClass();
    }

    /**
     * Add translate globals.
     * 
     * @param string $lan
     * @param array $translation
     */
    public function setTranslation($lan, array $translation)
    {
        foreach ($translation as $index => $item) {
            $this->languages->{$lan}->translation->{$index} = $item;
        }
    }

    /**
     * Get a language.
     * 
     * @param string $lan
     * @return stdClass|null
     */
    public function getLanguage($lan)
    {
        if (property_exists($this->languages, $lan)) {
            return $this->languages->{$lan};
        }
        return null;
    }

    /**
     * Add language in object.
     * 
     * @param string $lan
     */
    public function setLanguage($lan)
    {
        if (!property_exists($this->languages, $lan)) {
            $this->languages->{$lan} = new stdClass();
            $this->languages->{$lan}->translation = new stdClass();
        }
    }

    /**
     * Get plugin.
     * 
     * @param string $lan
     * @param string $pluginName
     * @return stdClass
     */
    public function getPlugin($lan, $pluginName)
    {
        return $this->languages->{$lan}->translation->{$pluginName};
    }

    /**
     * Set plugin.
     * 
     * @param string $lan
     * @param string $pluginName
     * @param object $plugin
     */
    public function setPlugin($lan, $pluginName, $plugin)
    {
        if (!property_exists($this->languages->{$lan}->translation, $pluginName)) {
            $this->languages->{$lan}->translation->{$pluginName} = new stdClass();
        }
        $objMerged = (object) array_merge(
                        (array) $this->languages->{$lan}->translation->{$pluginName}, (array) $plugin
        );
        $this->languages->{$lan}->translation->{$pluginName} = $objMerged;
    }

    /**
     * Generate file i18n in json.
     * 
     * @param string $filename
     * @return bool
     */
    public function saveJson($filename)
    {
        if ($filename) {
            return file_put_contents($filename, json_encode($this->languages)) !== false;
        }
        return false;
    }

    /**
     * Generate file i18n in js.
     * 
     * @param string $pluginName
     * @param string $filename
     * @return bool
     */
    public function saveJs($pluginName, $filename)
    {
        if ($filename) {
            $fileContent = "$pluginName = {};";
            $fileContent .= "$pluginName.i18n = function() { return " . json_encode($this->languages) . "; }";
            return file_put_contents($filename . ".js", $fileContent) !== false;
        }
        return false;
    }
}
