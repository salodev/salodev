#!/usr/bin/php
<?php

require_once(dirname(dirname(dirname(__FILE__)))) . '/autoload.php';
use salodev\GraphDB\Entities\Users;
use salodev\Mysql\Connection;
use salodev\Mysql;

Mysql::AddConnection('default', new Connection('localhost', 'root', 'root', 'graphs'));

$graphID1 = Users::Create(0, 0, [
	'name' => 'Salomón',
	'lastname' => 'Córdova',
	'birthDate' => '19850719',
]);

$ret = Users::GetList([
	'id' => $graphID1,
]);

print_r($ret); die();

$graphID2 = Users::Create(0, 0, [
	'name' => 'Rosario',
	'lastname' => 'Ayala',
	'birthDate' => '19931226',
]);
/*
Relations::Add($graphID1, 'knows', $graphID2, [
	'since' => '20180606',
	'via'   => 'happn',
], true);*/