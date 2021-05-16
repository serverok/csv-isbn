<?php

require 'vendor/autoload.php';

$isbn = new Isbn\Isbn();

$isbnNew = $isbn->translate->to13('8889527191'); 

echo $isbnNew;

$isbnNewWithHyphen = $isbn->hyphens->addHyphens($isbnNew);

echo "\nISB with hyphen = $isbnNewWithHyphen\n";
