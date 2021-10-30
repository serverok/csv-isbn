<?php
################################################################################
# Author: ServerOK Software
# Web: https://serverok.in
# Email: admin@serverok.in
################################################################################

require __DIR__ . '/config.php';
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/category.php';

verifyDir(DIR_CSV_DONE);
verifyDir(DIR_CSV_OUT);
verifyDir(DIR_CSV_MODIFIED);

$lock_file = DIR_CSV_DONE . '/cron.lock';

if (file_exists($lock_file)) {
    $contents = file_get_contents($lock_file);
    if (empty($contents)) {
        file_put_contents($lock_file, time());
    } else {
        $time_old = time() - 86400;
        if ($contents < $time_old) {
            unlink($lock_file);
        }
    }
    echo "Preious operations not finished yet.\n";
    echo "Lock file exists: $lock_file\n";
    exit;
} else {
    touch($lock_file);
}

verifyDir(DIR_CSV_OUT. '/add/');
verifyDir(DIR_CSV_OUT. '/delete/');

$ISBN = new Isbn\Isbn();

$csvFiles = scandir(DIR_CSV_IN);

foreach ($csvFiles as $csvFile) {
    if ($csvFile == '.' || $csvFile == '..') {
        continue;
    }
    $csvFilePath = DIR_CSV_IN . $csvFile;
    if (is_dir($csvFilePath)) {
        echo "SKIP $csvFile is not file\n";
        continue;
    }
    convertCSV($csvFile);
}

echo "Deleting lock file: $lock_file\n";
unlink($lock_file);


function convertCSV($csvFile) {
    global $ISBN;

    if (strpos($csvFile, 'addmodify') !== false) {
        $csvFilePathOut = DIR_CSV_OUT. '/add/' . changeFileExtension($csvFile);
    }

    if (strpos($csvFile, 'delete') !== false) {
        $csvFilePathOut = DIR_CSV_OUT. '/delete/' . changeFileExtension($csvFile);
    }

    $csvFilePath = DIR_CSV_IN . $csvFile;
    $csvFilePathDone = DIR_CSV_DONE. $csvFile;
    $csvFilePathModified = DIR_CSV_MODIFIED . $csvFile;

    if (!file_exists($csvFilePath)) {
        die("File not found: $csvFilePath\n");
    }
    
    echo "Converting $csvFilePath\n";
    
    $file_handle = fopen($csvFilePath, 'r');
    $file_handle2 = fopen($csvFilePathOut, 'w');

    while (!feof($file_handle)) {
        $csvRow = fgetcsv($file_handle);
        if (empty($csvRow)) {
            continue;
        }
        
        $csvRow = trimCSVData($csvRow);

        $isbn = $csvRow[9];
        $category = $csvRow[19];

        if ($isbn == 'ISBN') {
            $csvRow[] = 'ISBN13';
            $csvRow[] = 'CAT_ID';
        } else {
            $isbnNew = $ISBN->translate->to13($isbn); 
            $csvRow[] = $isbnNew;
            $csvRow[] = getCategryID($category);
        }

        fputcsv($file_handle2,$csvRow,",", '"');
        
    }

    fclose($file_handle);
    fclose($file_handle2);
    echo "File saved to : $csvFilePathOut\n";
    
    if (! rename($csvFilePath, $csvFilePathDone)) {
        echo "File remame failed: $csvFilePath => $csvFilePathDone\n";
        exit;
    }

    if(! copy($csvFilePathOut, $csvFilePathModified) ) {
        echo "ERROR: failed to copy $csvFilePathOut -> $csvFilePathModified\n";
        exit;
    }
    
    // write csv
}

function trimCSVData($csvRow) {
    array_walk($csvRow, 'trim_array'); 
    return $csvRow;
}

function trim_array(&$val) {
    $val = trim($val);
}

function verifyDir($dirName) {
    if (! is_dir($dirName)) {
        die("Directory not found: "  . $dirName);
    }
}

function changeFileExtension($fileName) {
    $pattern = '/(.*)\.txt$/i';
    if (preg_match($pattern, $fileName, $matches)) {
        $newName = $matches[1] . '.csv';
    } else {
        $newName = $fileName . '.csv';
    }
    return $newName;
}
