<?php

include_once 'NaturalNumbersSum.php';

$nns = new NaturalNumbersSum();
$nns->connectDB('', '', ''); // use your access parameters
$nns->createTable();
echo "<pre>";
var_dump($nns->getNaturalNumbersSum(uniqid(), rand(1,100))); 
var_dump($nns->getNaturalNumbersSum('qwerty', 8));
var_dump($nns->getNaturalNumbersSum(uniqid(), ''));
var_dump($nns->getNaturalNumbersSum(uniqid()));
var_dump($nns->getNaturalNumbersSum());
var_dump($nns->getNaturalNumbersSum(uniqid(), 12.1));
var_dump($nns->getNaturalNumbersSum(uniqid(), -8));
var_dump($nns->getNaturalNumbersSum(uniqid(), rand(1,100)));
var_dump($nns->getNaturalNumbersSum('qwerty', 18));
var_dump($nns->getNaturalNumbersSum(uniqid(), rand(1,100)));
var_dump($nns->getNaturalNumbersSum('qwerty', 8));
var_dump($nns->getNaturalNumbersSum('', 328));
var_dump($nns->getNaturalNumbersSum(uniqid(), rand(1,100)));
var_dump($nns->getNaturalNumbersSum('', 12));