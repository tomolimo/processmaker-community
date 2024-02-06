<?php

namespace App\Console\Commands;

use G;
use i18n_PO;
use Illuminate\Console\Command;
use ProcessMaker\Util\Translation\I18Next;
use stdClass;

/**
 * Class PMTranslationsPlugins
 * @package ProcessMaker\Console\Commands
 */
class PMTranslationsPlugins extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'translation:plugin
                            {--a|all : Translate all plugins}
                            {--name=* : Input Plugin name}
                            {--type=po : Input translation source(po|laravel)}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Translation plugin of Processmaker';

    /**
     * Path for plugins
     * @var string
     */
    protected $pluginsPath;

    /**
     * Class I18next
     * @var I18next
     */
    protected $i18next;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->pluginsPath = realpath(base_path() . DIRECTORY_SEPARATOR . "workflow" . DIRECTORY_SEPARATOR . "engine" . DIRECTORY_SEPARATOR . "plugins");
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pluginNames = $this->option('name');
        $processAll = $this->option('all');
        $processType = $this->option('type');
        $directoryPlugins = [];
        if ($processAll) {
            $directoryPlugins = $this->filterFiles(array_diff(scandir($this->pluginsPath), ['..', '.'])) ?: [];
        } elseif ($pluginNames) {
            $directoryPlugins = $pluginNames;
        } else {
            $this->comment("Please use the --all option or introduce the plugin name (--name=namePlugin)");
            return;
        }
        $this->info('Start converting');
        $bar = $this->output->createProgressBar(count($directoryPlugins));
        foreach ($directoryPlugins as $name) {
            if ($processType == 'po') {
                $this->generateI18nFromPoFiles($name);
            } elseif ($processType == 'laravel') {
                $this->generateI18nFromLaravelLang($name);
            }
            $bar->advance();
        }
        $bar->finish();
        $this->info("\nFinish");
    }

    /**
     * Generate files i18n from .po.
     * 
     * @param object $pluginName
     * @return void
     */
    private function generateI18nFromPoFiles($pluginName)
    {
        $pluginPath = $this->pluginsPath . DIRECTORY_SEPARATOR . $pluginName;
        if (is_dir($pluginPath)) {
            // Translate for files .po in plugin
            $translationsDirectory = $pluginPath . DIRECTORY_SEPARATOR . 'translations';
            $scannedDirectory = is_dir($translationsDirectory) ? array_diff(scandir($translationsDirectory), ['..', '.']) : null;
            if ($scannedDirectory) {
                $this->i18next = new I18Next();
                foreach ($scannedDirectory as $index => $item) {
                    $filePath = $translationsDirectory . DIRECTORY_SEPARATOR . $item;
                    $pathParts = pathinfo($filePath);
                    $isPofile = !empty($pathParts['extension']) && $pathParts['extension'] === 'po';

                    if ($isPofile) {
                        $basename = explode('.', $pathParts['basename']);
                        $language = $basename[1];

                        $this->i18next->setLanguage($language);

                        //read file .po
                        $str = new stdClass();
                        $poFile = new i18n_PO($filePath);
                        $poFile->readInit();
                        while ($translation = $poFile->getTranslation()) {
                            $translatorComments = $poFile->translatorComments;
                            $references = $poFile->references;

                            $ifContinue = empty($translatorComments[0]) && empty($translatorComments[1]) && empty($references[0]);
                            if ($ifContinue) {
                                continue;
                            }
                            $ifNotTranslation = !($translatorComments[0] === 'TRANSLATION');
                            if ($ifNotTranslation) {
                                continue;
                            }
                            $key = explode("/", $translatorComments[1]);
                            $str->{$key[1]} = $translation['msgstr'];
                        }
                        $this->i18next->setPlugin($language, $pluginName, $str);
                    }
                }
                $this->saveFileJs($pluginName);
            }
        }
    }

    /**
     * Generate files i18n from resource/lang/*.
     * 
     * @param $pluginName
     * @return void
     */
    private function generateI18nFromLaravelLang($pluginName)
    {
        $pluginPath = $this->pluginsPath . DIRECTORY_SEPARATOR . $pluginName;
        if (is_dir($pluginPath)) {
            // Translate for files resources/lang in plugin
            $translationsDirectory = $pluginPath . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang';
            $scannedDirectory = is_dir($translationsDirectory) ? array_diff(scandir($translationsDirectory), ['..', '.']) : null;
            if ($scannedDirectory) {
                $this->i18next = new I18Next();
                foreach ($scannedDirectory as $lang) {
                    $dirLanguage = $pluginPath . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $lang;
                    $scannedLanguage = is_dir($dirLanguage) ? array_diff(scandir($dirLanguage), ['..', '.']) : [];
                    foreach ($scannedLanguage as $index => $item) {
                        $filePath = $dirLanguage . DIRECTORY_SEPARATOR . $item;
                        $pathParts = pathinfo($filePath);
                        $isPhpFile = !empty($pathParts['extension']) && $pathParts['extension'] === 'php';
                        if ($isPhpFile) {
                            $file = explode(".", $item);
                            array_pop($file);
                            $filename = implode("_", $file);
                            $arrayLanguage = [$filename => require_once($dirLanguage . DIRECTORY_SEPARATOR . $item)];
                            $this->i18next->setLanguage($lang);
                            $this->i18next->setPlugin($lang, $pluginName, json_decode(json_encode($arrayLanguage)));
                        }
                    }
                }
                $this->saveFileJs($pluginName);
            }
        }
    }

    /**
     * Save js file generate of translate files.
     * 
     * @param string $pluginName
     */
    private function saveFileJs($pluginName)
    {
        $folderToSave = $this->pluginsPath . DIRECTORY_SEPARATOR . $pluginName . DIRECTORY_SEPARATOR . "public_html" . DIRECTORY_SEPARATOR . "js";
        if (!is_dir($folderToSave)) {
            $create = $this->choice('The "js" folder does not exist, Do you want to create the folder?', ['Yes', 'No'], 0);
            if (strtolower($create) == 'yes') {
                G::mk_dir($folderToSave, 0775);
            }
        }
        $this->i18next->saveJs($pluginName, $folderToSave . DIRECTORY_SEPARATOR . $pluginName . ".i18n");
    }

    /**
     * Remove files, return only folders.
     * 
     * @param $scannedDirectory
     * @return array
     */
    private function filterFiles($scannedDirectory)
    {
        $onlyFolders = [];
        foreach ($scannedDirectory as $index => $item) {
            $pluginPath = $this->pluginsPath . DIRECTORY_SEPARATOR . $item;
            if (is_dir($pluginPath)) {
                array_push($onlyFolders, $item);
            }
        }
        return $onlyFolders;
    }
}
