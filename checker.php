<?php

require_once __DIR__ . "/vendor/autoload.php";

$finder = new \Rozeo\Checker\Finder(__DIR__ . "/sample.php");
$result = $finder->setTargets([
    'var_dump',
    'a', 'b',
])->execute();

var_dump($result);