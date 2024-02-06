<?php
global $RBAC;
$RBAC->allows(basename(__FILE__), $_GET['MAIN_DIRECTORY']);

$mainDirectory = !empty($_GET['MAIN_DIRECTORY']) ? $_GET['MAIN_DIRECTORY'] : '';
$proUid = !empty($_GET['PRO_UID']) ? $_GET['PRO_UID'] : '';
$currentDirectory = !empty($_GET['CURRENT_DIRECTORY']) ? $_GET['CURRENT_DIRECTORY'] . PATH_SEP : '';
$file = !empty($_GET['FILE']) ? $_GET['FILE'] : '';
$extension = (!empty($_GET['sFilextension']) && $_GET['sFilextension'] === 'javascript') ? '.js' : '';

// Validate the main directory
switch ($mainDirectory) {
    case 'mailTemplates':
        $directory = PATH_DATA_MAILTEMPLATES;
        break;
    case 'public':
        $directory = PATH_DATA_PUBLIC;
        break;
    default:
        die();
        break;
}

// Validate if process exists, an exception is throwed if not exists
$process = new Process();
$process->load($proUid);

// Validate directory and file requested
$filter = new InputFilter();
$currentDirectory = $filter->validatePath($currentDirectory);
$file = $filter->validatePath($file);

// Build requested path
$directory .= $proUid . PATH_SEP . $currentDirectory;
$file .= $extension;

// Stream the file if path exists
if (file_exists($directory . $file)) {
    G::streamFile($directory . $file, true);
}
