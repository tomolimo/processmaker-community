<?php

// Get the Home Directory, snippet adapted from sysGeneric.php
$documentRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$sections = explode('/', $documentRoot);
array_pop($sections);
$pathHome = implode('/', $sections) . '/';

// Include the "paths_installed.php" file
require_once $pathHome . 'engine/config/paths_installed.php';

// Get font file name requested
$fileName = $_REQUEST['file'] ?? '';

// Check if the requested font file exists and if is accessible
if (empty($fileName) || !file_exists(PATH_DATA . 'fonts/' . $fileName)) {
    // Redirect to error page 404
    header('Location: /errors/error404.php');
    die();
} else {
    // Stream the font file
    header('Content-Disposition: inline; filename="' . $fileName . '"');
    header('Content-Type: font/ttf');
    readfile(PATH_DATA . 'fonts/' . $fileName);
}
