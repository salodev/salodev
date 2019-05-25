#!/usr/bin/php
<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/autoload.php');

$data = [
	['product' => 'anana',  'year'=>'2018', 'month' => 'nov', 'sales' => 12, 'price' => 8, 'qty' => 1],
	['product' => 'anana',  'year'=>'2018', 'month' => 'dec', 'sales' => 15, 'price' => 8, 'qty' => 1],
	
	['product' => 'anana',  'year'=>'2019', 'month' => 'jun', 'sales' => 15, 'price' => 8, 'qty' => 1],
	['product' => 'anana',  'year'=>'2019', 'month' => 'feb', 'sales' => 12, 'price' => 8, 'qty' => 1],
	['product' => 'anana',  'year'=>'2019', 'month' => 'mar', 'sales' => 10, 'price' => 8, 'qty' => 1],
	['product' => 'anana',  'year'=>'2019', 'month' => 'apr', 'sales' =>  8, 'price' => 8, 'qty' => 1],
	['product' => 'anana',  'year'=>'2019', 'month' => 'may', 'sales' =>  2, 'price' => 8, 'qty' => 1],
	
	['product' => 'orange', 'year'=>'2019', 'month' => 'jun', 'sales' => 15, 'price' => 8, 'qty' => 1],
	['product' => 'orange', 'year'=>'2019', 'month' => 'feb', 'sales' => 12, 'price' => 8, 'qty' => 1],
	['product' => 'orange', 'year'=>'2019', 'month' => 'mar', 'sales' => 10, 'price' => 8, 'qty' => 1],
	['product' => 'orange', 'year'=>'2019', 'month' => 'apr', 'sales' => 10, 'price' => 8, 'qty' => 1],
	['product' => 'orange', 'year'=>'2019', 'month' => 'may', 'sales' => 10, 'price' => 8, 'qty' => 1],
];

$pt = new salodev\PivotTableGenerator;
$pt->setData($data)->groupBy('product','year')->columnsBy('month')->valuesBy('sales');
$result = $pt->transform();

print_r($result);

(new salodev\PivotTableGenerator())->setData([['A'=>1]])->transform();
