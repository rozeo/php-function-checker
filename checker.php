<?php

require_once __DIR__ . "/vendor/autoload.php";

$finder = new \Rozeo\Checker\Finder(__DIR__ . "/sample.php");
$finder->findFunctions(['HHH\GGG\Hoge::fuga']);