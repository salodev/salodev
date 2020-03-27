<?php

namespace salodev\GraphDB;

use salodev\Mysql;

class Graphs {
	
	static public function PrepareDB() {
		Mysql::Query("
			SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";
			SET time_zone = \"+00:00\";

			CREATE TABLE `graphs` (
			  `id` int(11) NOT NULL,
			  `type` varchar(64) NOT NULL,
			  `creationTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;

			ALTER TABLE `graphs`
			  ADD PRIMARY KEY (`id`),
			  ADD KEY `type` (`type`),

			ALTER TABLE `graphs`
			  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


			-- tabla graphsDAta --

			CREATE TABLE `graphsData` (
			  `id` int(11) NOT NULL,
			  `graphId` int(11) NOT NULL,
			  `name` varchar(128) NOT NULL,
			  `value` varchar(255) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;

			ALTER TABLE `graphsData`
			  ADD PRIMARY KEY (`id`),
			  ADD UNIQUE KEY `graphId_name` (`graphId`,`name`),
			  ADD KEY `graphId` (`graphId`),
			  ADD KEY `name_value` (`name`,`value`);

			ALTER TABLE `graphsData`
			  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

			-- tabla relations --

			CREATE TABLE `relations` (
			  `id` int(11) NOT NULL,
			  `type` varchar(60) NOT NULL,
			  `fromGraphId` int(11) NOT NULL,
			  `toGraphId` int(11) NOT NULL,
			  `creationTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `finishTime` timestamp NULL DEFAULT NULL,
			  `status` enum('ACTIVE','FINISHED') NOT NULL DEFAULT 'ACTIVE'
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;


			ALTER TABLE `relations`
			  ADD PRIMARY KEY (`id`),
			  ADD KEY `type` (`type`);

			ALTER TABLE `relations`
			  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
		");
	}
	
	static public function GetList(int $offset = 0, int $limit = 0, array $options = []): array {
		$query = Mysql::Table('graphs g');
		$query->column('g.id');
		$query->column('g.type');
		$query->column('g.creationTime');
		
		foreach($options as $option => $value) {
			
			if ($option == 'id') {
				$query->filter($option, $value);
			}
			
			if ($option == 'type') {
				$query->filter($option, $value);
			}
			
			if ($option == 'data') {
				foreach($value as $attr => $attrValue) {
					$aliasName = "gd_{$attr}";
					$query->innerJoin("
						graphsData {$aliasName} ON (
							{$aliasName}.name = '{$attr}' AND 
							{$aliasName}.graphId = g.id
						)
					");
					$query->filter("{$aliasName}.value", $attrValue);
				}
			}
			
			if ($option == 'relationTo') {
				foreach($value as $relationName => $graphId) {
					$aliasName = "grt_{$relationName}";
					$query->innerJoin("graphsRelations {$aliasName} ON (
						{$aliasName}.name = '{$relationName}' AND 
						{$aliasName}.fromGraphId = g.id
					)");
					$query->filter("{$aliasName}.toGraphId", $graphId);
				}
			}
			
			if ($option == 'relationFrom') {
				foreach($value as $relationName => $graphId) {
					$aliasName = "grf_{$relationName}";
					$query->innerJoin("graphsRelations {$aliasName} ON (
						{$aliasName}.name = '{$relationName}' AND 
						{$aliasName}.toGraphId = g.id
					)");
					$query->filter("{$aliasName}.fromGraphId", $graphId);
				}
			}
		}
		$query->offset($offset);
		$query->limit($limit);
		$query->groupBy('g.id');
		$rs =  $query->select();
		
		static::_AttachData($rs);
		if (isset($options['largeData']) && $options['largeData']) {
			static::_AttachLargeData($rs);
		}
		
		return $rs;
	}
	
	static private function _AttachData(&$rs) {
		$ids = [];
		foreach($rs as $row) {
			$ids[] = $row['id'];
		}
		$data = Mysql::Table('graphsData')
			->graphId($ids)
			->select();
		$indexedData = [];
		foreach($data as $rowData) {
			$graphId = $rowData['graphId'];
			$name = $rowData['name'];
			$value = $rowData['value'];
			if (!isset($indexedData[$graphId])) {
				$indexedData[$graphId] = [];
			}
			$indexedData[$graphId][$name] = $value;
		}
		foreach($rs as &$row) {
			$graphId = $row['id'];
			$row = array_merge($indexedData[$graphId], $row);
		}
	}
	
	static private function _AttachLargeData(&$rs) {
		$ids = [];
		foreach($rs as $row) {
			$ids[] = $row['id'];
		}
		$data = Mysql::Table('graphsLargeData')
			->graphId($ids)
			->select();
		$indexedData = [];
		foreach($data as $rowData) {
			$graphId = $rowData['graphId'];
			$name = $rowData['name'];
			$value = $rowData['value'];
			if (!isset($indexedData[$graphId])) {
				$indexedData[$graphId] = [];
			}
			$indexedData[$graphId][$name] = $value;
		}
		foreach($rs as &$row) {
			$graphId = $row['id'];
			$row = array_merge($indexedData[$graphId], $row);
		}
	}
	
	static public function GetOne(array $options = []): array {
		$return = static::GetList(0, 2, $options);
		if (count($return) < 1) {
			throw new NotFound('Request graph was not found');
		}
		if (count($return) > 1) {
			throw new NotFound('More than one graph found');
		}
		return $return[0];
	}
	
	static public function GetData(int $graphId, array $options = []): array {
		return static::GetOne(array_merge($options, ['id' => $graphId]));
	}
	
	static private function validateDataAndLargeData(array $data, array $largeData): void {
		foreach($largeData as $key => $value) {
			if (isset($data[$key])) {
				throw new Exception("You can not use same property data name '{$key}' for data and large data");
			}
		}
	}
	
	static public function Create(string $type, array $data = [], array $largeData = []): int {
		
		static::validateDataAndLargeData($data, $largeData);
		
		$graphId = Mysql::Table('graphs')
				->type($type)
				->insert();
		
		foreach($data as $name => $value) {
			Mysql::Table('graphsData')
					->graphId($graphId)
					->name($name)
					->value($value)
					->insert();
		}
		
		foreach($largeData as $name => $value) {
			Mysql::Table('graphsLargeData')
					->graphId($graphId)
					->name($name)
					->value($value)
					->insert();
		}
		return $graphId/1;
	}
	
	static public function Update(int $graphId, array $data = [], array $largeData = []): bool {
		static::validateDataAndLargeData($data, $largeData);
		if (count($data)) {
			$graphId = Mysql::Table('graphsData')
					->filter('graphId', $graphId)
					->update($data);
		}
		if (count($largeData)) {
			$graphId = Mysql::Table('graphsLargeData')
					->filter('graphId', $graphId)
					->update($largeData);
		}
		
		return true;
	}
	
	static public function Query(): GraphSelect {
		return new GraphSelect;
	}
	
	static public function Test() {
		GDB::Select()->data([])->relationTo()->get();
		
	}
}

