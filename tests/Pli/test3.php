#!/usr/bin/php
<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/autoload.php');

$code = <<<CODE
		!(si(3 > 2, "roo", "salo"))
CODE;

try {
	$ce = new \salodev\Pli\ComputingEngine(salodev\Pli\Tokens\Code::class);
	
	$ce->defineFunctionCb('si', function($nombre, $nombre2, $nombre3) {
		return $nombre ? $nombre2 : $nombre3;
		// return 'Hola ' . $nombre . ' y ' . $nombre2;
	});
	
	$result = $ce->evaluate($code);
	
	// echo "{$result}\n";
	die();
} catch (\Exception $e) {
	echo $e->getMessage() . "\n\nCode:\n";
	$ce->showCurrentParsing(300);
}
