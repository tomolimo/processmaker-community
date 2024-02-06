<?php

/**
 * Class PmPluginCompatibility
 *
 * This class was created in order to keep the compatibility of the plugins that uses the method "PMPlugin"
 */
class PmPluginCompatibility
{
    // The name of these properties should be equal that the properties in class "PMPlugin"
    public $sNamespace;
    public $sClassName;
    public $sPluginFolder = '';
    public $sFilename = null;

    /**
     * Method similar to PMPlugin::__construct, this method was created in order to keep the compatibility for the plugins,
     * the plugins uses something similar to: parent::PMPlugin($namespace, $filename);
     *
     * @param string $namespace
     * @param string $filename
     */
    public function PMPlugin($namespace, $filename = null)
    {
        $this->sNamespace = $namespace;
        $this->sClassName = $namespace . 'Plugin';
        $this->sPluginFolder = $namespace;
        $this->sFilename = $filename;
    }
}
