<?php

// Get the Home Directory, snippet adapted from sysGeneric.php
$documentRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$sections = explode('/', $documentRoot);
array_pop($sections);
$pathHome = implode('/', $sections) . '/';

// Include the "paths_installed.php" file
require_once $pathHome . 'engine/config/paths_installed.php';

// Set the fonts styles file, for now the value is fixed (Maybe later we have another PDF engine)
$fileName = 'fonts.css';

// Stream the requested css file if exists and if is accessible
header('Content-Type: text/css');
if (file_exists(PATH_DATA . 'fonts/tcpdf/' . $fileName)) {
    readfile(PATH_DATA . 'fonts/tcpdf/' . $fileName);
}
