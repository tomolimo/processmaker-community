<?php

namespace ProcessMaker\Validation;

use Bootstrap;
use G;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use ProcessMaker\Core\System;
use ProcessMaker\Services\OAuth2\Server;
use ProcessMaker\Util\PhpShorthandByte;
use Symfony\Component\HttpFoundation\File\File;

class ValidationUploadedFiles
{
    /**
     * Single object instance to be used in the entire environment.
     * 
     * @var object 
     */
    private static $validationUploadedFiles = null;

    /**
     * List of evaluated items that have not passed the validation rules.
     * 
     * @var array 
     */
    private $fails = [];

    /**
     * Check if the loaded files comply with the validation rules, add here if you 
     * want more validation rules. 
     * Accept per argument an array or object that contains a "filename" and "path" values.
     * The rules are verified in the order in which they have been added.
     * 
     * @param array|object $file 
     * @return Validator
     */
    public function runRules($file)
    {
        $validator = new Validator();

        //rule: disable_php_upload_execution
        $validator->addRule()
                ->validate($file, function($file) {
                    $filesystem = new Filesystem();
                    $extension = $filesystem->extension($file->filename);

                    return Bootstrap::getDisablePhpUploadExecution() === 1 && $extension === 'php';
                })
                ->status(550)
                ->message(G::LoadTranslation('ID_THE_UPLOAD_OF_PHP_FILES_WAS_DISABLED'))
                ->log(function($rule) {
                    /**
                     * Levels supported by MonologProvider is:
                     * 100 "DEBUG"
                     * 200 "INFO"
                     * 250 "NOTICE"
                     * 300 "WARNING"
                     * 400 "ERROR"
                     * 500 "CRITICAL"
                     * 550 "ALERT"
                     * 600 "EMERGENCY"
                     */
                    Bootstrap::registerMonologPhpUploadExecution('phpUpload', 550, $rule->getMessage(), $rule->getData()->filename);
                });

        //rule: upload_attempts_limit_per_user
        $validator->addRule()
                ->validate($file, function($file) {
                    $systemConfiguration = System::getSystemConfiguration('', '', config("system.workspace"));
                    $filesWhiteList = explode(',', $systemConfiguration['upload_attempts_limit_per_user']);
                    $userId = Server::getUserId();
                    $key = config("system.workspace") . '/' . $userId;
                    $attemps = (int) trim($filesWhiteList[0]);
                    $minutes = (int) trim($filesWhiteList[1]);
                    $pastAttemps = Cache::remember($key, $minutes, function() {
                                return 1;
                            });
                    //We only increase when the file path exists, useful when pre-validation is done.
                    if (isset($file->path)) {
                        Cache::increment($key, 1);
                    }
                    if ($pastAttemps <= $attemps) {
                        return false;
                    }
                    return true;
                })
                ->status(429)
                ->message(G::LoadTranslation('ID_TOO_MANY_REQUESTS'))
                ->log(function($rule) {
                    /**
                     * Levels supported by MonologProvider is:
                     * 100 "DEBUG"
                     * 200 "INFO"
                     * 250 "NOTICE"
                     * 300 "WARNING"
                     * 400 "ERROR"
                     * 500 "CRITICAL"
                     * 550 "ALERT"
                     * 600 "EMERGENCY"
                     */
                    Bootstrap::registerMonologPhpUploadExecution('phpUpload', 250, $rule->getMessage(), $rule->getData()->filename);
                });

        //rule: mimeType
        $validator->addRule()
                ->validate($file, function($file) {
                    $path = isset($file->path) ? $file->path : "";
                    $filesystem = new Filesystem();
                    if (!$filesystem->exists($path)) {
                        return false;
                    }

                    $extension = $filesystem->extension($file->filename);
                    $mimeType = $filesystem->mimeType($path);

                    $file = new File($path);
                    $guessExtension = $file->guessExtension();
                    $mimeTypeFile = $file->getMimeType();

                    //mimeType known
                    if ($extension === $guessExtension && $mimeType === $mimeTypeFile) {
                        return false;
                    }
                    //mimeType custom
                    $customMimeTypes = config("customMimeTypes");
                    $customMimeType = isset($customMimeTypes[$extension]) ? $customMimeTypes[$extension] : null;
                    if (is_string($customMimeType)) {
                        if ($customMimeType === $mimeType) {
                            return false;
                        }
                    }
                    if (is_array($customMimeType)) {
                        foreach ($customMimeType as $value) {
                            if ($value === $mimeType) {
                                return false;
                            }
                        }
                    }
                    //files_white_list
                    $systemConfiguration = System::getSystemConfiguration('', '', config("system.workspace"));
                    $filesWhiteList = explode(',', $systemConfiguration['files_white_list']);
                    if (in_array($extension, $filesWhiteList)) {
                        return false;
                    }
                    return true;
                })
                ->status(415)
                ->message(G::LoadTranslation('ID_THE_MIMETYPE_EXTENSION_ERROR'))
                ->log(function($rule) {
                    /**
                     * Levels supported by MonologProvider is:
                     * 100 "DEBUG"
                     * 200 "INFO"
                     * 250 "NOTICE"
                     * 300 "WARNING"
                     * 400 "ERROR"
                     * 500 "CRITICAL"
                     * 550 "ALERT"
                     * 600 "EMERGENCY"
                     */
                    Bootstrap::registerMonologPhpUploadExecution('phpUpload', 250, $rule->getMessage(), $rule->getData()->filename);
                });

        return $validator->validate();
    }

    /**
     * File upload validation.
     * 
     * @return $this
     * 
     * @see workflow/public_html/sysGeneric.php
     */
    public function runRulesToAllUploadedFiles()
    {
        $files = $_FILES;
        if (!is_array($files)) {
            return;
        }
        $this->fails = [];

        $validator = $this->runRulesForFileEmpty();
        if ($validator->fails()) {
            $this->fails[] = $validator;
        }

        foreach ($files as $file) {
            $data = (object) $file;
            if (!is_array($data->name) || !is_array($data->tmp_name)) {
                $data->name = [$data->name];
                $data->tmp_name = [$data->tmp_name];
            }
            foreach ($data->name as $key => $value) {
                if (empty($value)) {
                    continue;
                }
                if (is_array($value)) {
                    foreach ($value as $rowKey => $rowValue) {
                        foreach ($rowValue as $cellKey => $cellValue) {
                            if (empty($cellValue)) {
                                continue;
                            }
                            $validator = $this->runRules(['filename' => $cellValue, 'path' => $data->tmp_name[$key][$rowKey][$cellKey]]);
                            if ($validator->fails()) {
                                $this->fails[] = $validator;
                            }
                        }
                    }
                    continue;
                }
                $validator = $this->runRules(['filename' => $value, 'path' => $data->tmp_name[$key]]);
                if ($validator->fails()) {
                    $this->fails[] = $validator;
                }
            }
        }

        return $this;
    }

    /**
     * Run rules if files is empty.
     * 
     * @see ProcessMaker\Validation\ValidationUploadedFiles->runRulesToAllUploadedFiles()
     * @see Luracast\Restler\Format\UploadFormat->decode()
     */
    public function runRulesForFileEmpty()
    {
        $validator = new Validator();

        //rule: validate $_SERVER['CONTENT_LENGTH']
        $rule = $validator->addRule();
        $rule->validate(null, function($file) use ($rule) {
                    //according to the acceptance criteria the information is always shown in MBytes
                    $phpShorthandByte = new PhpShorthandByte();
                    $postMaxSize = ini_get("post_max_size");
                    $postMaxSizeBytes = $phpShorthandByte->valueToBytes($postMaxSize);
                    $uploadMaxFileSize = ini_get("upload_max_filesize");
                    $uploadMaxFileSizeBytes = $phpShorthandByte->valueToBytes($uploadMaxFileSize);

                    if ($postMaxSizeBytes < $uploadMaxFileSizeBytes) {
                        $uploadMaxFileSize = $postMaxSize;
                        $uploadMaxFileSizeBytes = $postMaxSizeBytes;
                    }
                    //according to the acceptance criteria the information is always shown in MBytes
                    $uploadMaxFileSizeMBytes = $uploadMaxFileSizeBytes / (1024 ** 2); //conversion constant

                    $message = G::LoadTranslation('ID_THE_FILE_SIZE_IS_BIGGER_THAN_THE_MAXIMUM_ALLOWED', [$uploadMaxFileSizeMBytes]);
                    $rule->message($message);
                    /**
                     * If you can, you may want to set post_max_size to a low value (say 1M) to make
                     * testing easier. First test to see how your script behaves. Try uploading a file
                     * that is larger than post_max_size. If you do you will get a message like this
                     * in your error log:
                     *
                     * [09-Jun-2010 19:28:01] PHP Warning:  POST Content-Length of 30980857 bytes exceeds
                     * the limit of 2097152 bytes in Unknown on line 0
                     *
                     * This makes the script is not completed.
                     *
                     * Solving the problem:
                     * The PHP documentation http://php.net/manual/en/ini.core.php#ini.post-max-size
                     * provides a hack to solve this problem:
                     *
                     * If the size of post data is greater than post_max_size, the $_POST and $_FILES
                     * superglobals are empty.
                     */
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
                        return true;
                    }
                    return false;
                })
                ->status(400)
                ->log(function($rule) {
                    Bootstrap::registerMonologPhpUploadExecution('phpUpload', 400, $rule->getMessage(), "");
                });

        return $validator->validate();
    }

    /**
     * Get the first error and call the argument function.
     * 
     * @param function $callback
     * @return $this
     */
    public function dispatch($callback)
    {
        if (!empty($this->fails[0])) {
            if (!empty($callback) && is_callable($callback)) {
                $callback($this->fails[0], $this->fails);
            }
        }
        return $this;
    }

    /**
     * It obtains a single object to be used as a record of the whole environment.
     * 
     * @return object
     */
    public static function getValidationUploadedFiles()
    {
        if (self::$validationUploadedFiles === null) {
            self::$validationUploadedFiles = new ValidationUploadedFiles();
        }
        return self::$validationUploadedFiles;
    }
}
