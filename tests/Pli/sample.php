#!/usr/bin/php
<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/autoload.php');


try {
	$ce = new \salodev\Pli\ComputingEngine(salodev\Pli\Tokens\Code::class);
	$result = $ce->evaluate(file_get_contents(dirname(__FILE__) . '/sample1.code'));
	
	// echo "{$result}\n";
	die();
} catch (\Exception $e) {
	echo $e->getMessage() . "\n\nCode:\n";
	$ce->showCurrentParsing(300);
	// throw $e;
}