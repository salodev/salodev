#!/usr/bin/php
<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/autoload.php');

/**
 * Tenemos una lista de precios y debemos actualizarla con una formula introducida por el usuario.
 * En la pantalla de debe mostrar la lista original
 * Solicitar la formula
 * Realizar el cálculo
 * Mostrar la lista modificada.
 */

$precios = [];
$precios[] = ['Modelado 3D /hora',  300];
$precios[] = ['Modelado 2D /hora',  200];
$precios[] = ['Impresion 3D /hora', 200];
$precios[] = ['Asesoría /hora',     500];

try {
	foreach($precios as $datos) {
		echo "{$datos[0]}\t{$datos[1]}\n";
	}
	$formula = readline('Introduzca la fórmula de cálculo: ');
	$ce = new \salodev\Pli\ComputingEngine(salodev\Pli\Tokens\Math\Expression::class);
	
	$ce->defineFunctionCb('saludo', function($nombre) {
		return 'Hola ' . $nombre;
	});
	// $ce->defineFunction(new \salodev\Pli\Definitions\NativeFunctions\FunctionIF());
	foreach($precios as &$datos) {
		$datos[1] = $ce->evaluate($formula, ['precio'=>$datos[1]]);
	} unset ($datos);
	foreach($precios as $datos) {
		echo "{$datos[0]}\t{$datos[1]}\n";
	}
	die();
} catch (\Exception $e) {
	echo $e->getMessage() . "\n\nCode:\n";
	$ce->showCurrentParsing(300);
	// throw $e;
}