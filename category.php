<?php

$fileContent = file('category-table.csv');

$categoryMatch = array();

foreach ($fileContent as $categoryLine) {
    $categoryLine = trim($categoryLine);
    if (empty($categoryLine)) {
        continue;
    }
    $categoryLineArray = explode(',,', $categoryLine);
    if (count($categoryLineArray) == 2) {
        $categoryLineArray2 = array($categoryLineArray[0] => $categoryLineArray[1]);
        $categoryMatch[] = $categoryLineArray2; 
    } else {
        echo "SKIP: $categoryLine\n";
    }
}

function getCategryID($category) {
    global $categoryMatch;
    $category = trim($category);
    if (isset($categoryMatch["$category"])) {
        $categoryID = $categoryMatch["$category"];
    } else {
        echo "ERROR: Category not found $category\n";
        $categoryID = 0;
    }
    return $categoryID;
}