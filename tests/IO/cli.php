#!/usr/bin/php
<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/autoload.php');


use salodev\IO\Cli;

//$r = fopen('php://stdin', 'r');$ret = fread($r, 255);echo $ret;

print_r($argv);