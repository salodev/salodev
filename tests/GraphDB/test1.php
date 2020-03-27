#!/usr/bin/php
<?php

require_once(dirname(dirname(dirname(__FILE__)))) . '/autoload.php';
use salodev\GraphDB\Entities\Users;
use salodev\GraphDB\Graphs;
use salodev\GraphDB\Relations;
use salodev\Mysql\Connection;
use salodev\Mysql;

Mysql::AddConnection('default', new Connection('localhost', 'root', 'root', 'graphs'));

$graphId = Graphs::Create('project', 0, 0, [
	'name' => 'GraphsDB',
	'purpose' => 'Provide an easy way to handle data in graph format and their relations'
], [
	'history' => 'Esta idea nacio con varios proyectos. finalmente como amante del conflicto por las Islas Malvindas decidi hacer mi enciclopedia personal basada en investigaciones sobre el conficto.'
]);
$ret = Graphs::GetData($graphId, ['largeData'=>true]);
print_r($ret);die();

$graphID1 = Users::Create(0, 0, [
	'name' => 'Salomón',
	'lastname' => 'Córdova',
	'birthDate' => '19850719',
]);

$ret = Users::GetList([
	'id' => $graphID1,
]);

print_r($ret);die();

$graphID2 = Users::Create(0, 0, [
	'name' => 'Rosario',
	'lastname' => 'Ayala',
	'birthDate' => '19931226',
]);

$ret = Users::GetList([
	'id' => $graphID2,
]);
print_r($ret);

Relations::Add($graphID1, 'knows', $graphID2, [
	'since' => '20180606',
	'via'   => 'happn',
], true);