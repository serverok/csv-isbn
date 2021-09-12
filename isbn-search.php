#!/usr/bin/php -q
<?php
################################################################################
# Author: ServerOK Software
# Web: https://serverok.in
# Email: admin@serverok.in
################################################################################

require __DIR__ . '/config.php';

if (isset($argv[1])) {
    $searchString  = $argv[1];
} else {
    echo "Usage: isbn-search ISBN_HERE\n";
    exit;
}

if (! is_dir(DIR_CSV_DONE)) {
    die("ERROR:Directory not found: " . DIR_CSV_DONE . "\n");
}

$csvFiles = scandir(DIR_CSV_DONE);
$fileFound = array();

foreach ($csvFiles as $csvFile) {
    if ($csvFile == '.' || $csvFile == '..') {
        continue;
    }
    $csvFilePath = DIR_CSV_DONE . $csvFile;
    if (is_dir($csvFilePath)) {
        echo "SKIP $csvFile is not file\n";
        continue;
    }
    $fileContent =  file("$csvFilePath");
    foreach ($fileContent as $fileLine) {
        $found = strpos($fileLine, $searchString);
        if ($found !== false) {
            $fileFound[] = $csvFilePath;
            echo "$csvFilePath\n";
            break;
        }
    }
}

if (empty($fileFound)) {
    echo "No files found with isbn\n";
}