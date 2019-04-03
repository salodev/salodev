#!/usr/bin/php
<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/autoload.php');

use salodev\Entities\Entities;
use salodev\Entities\Collection;

class Contract extends Entities {
	public function getData(array $filters = array(), array $options = array()): array {
		$data = [];
		$data[] = ['id'=>1, 'clientID'=>1, 'concept' => 'SG Mensual'];
		$data[] = ['id'=>2, 'clientID'=>1, 'concept' => 'SG Mensual'];
		$data[] = ['id'=>3, 'clientID'=>2, 'concept' => 'SG Mensual'];
		$data[] = ['id'=>4, 'clientID'=>2, 'concept' => 'SG Mensual'];
		$filtered = [];
		foreach($filters as $key => $value) {
			if ($key=='clientID') {
				foreach($data as $row) {
					if ($row['clientID']==$value) {
						$filtered[] = $row;
					}
				}
			}
		}
		return $filtered;
	}
}

class Client extends Entities {
	public function getData(array $filters = array(), array $options = array()): array {
		$data = [];
		$data[] = ['id'=>1, 'name'=>'Salomon'];
		$data[] = ['id'=>2, 'name'=>'David'];
		$data[] = ['id'=>3, 'name'=>'Benjamin'];
		$data[] = ['id'=>4, 'name'=>'Jemima'];
		$data[] = ['id'=>5, 'name'=>'Cesia'];
		$filtered = [];
		
		foreach($filters as $key => $values) {
			if ($key=='id') {
				foreach($data as $row) {
					
					if (!is_array($values)) {
						$values = [$values];
					}
					$values = array_unique($values);
					foreach($values as $value) {
						if ($row[$key]==$value) {
							$filtered[] = $row;
						}
					}
				}
			}
		}
		
		return $filtered;
	}
}

class Service extends Entities {
	public function getData(array $filters = array(), array $options = array()): array {
		return [];
	}
}

$contracts = Contract::Instance()->getList(['clientID'=>1]);
$contracts->joinWithForeign('Client');
$contracts->joinWithRelated('Service');
$array = $contracts->getArrayCopy();
print_r($array);