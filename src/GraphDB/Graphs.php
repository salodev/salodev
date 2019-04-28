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
			  `ownerID` int(11) DEFAULT NULL,
			  `containerID` int(11) DEFAULT NULL,
			  `creationTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;

			ALTER TABLE `graphs`
			  ADD PRIMARY KEY (`id`),
			  ADD KEY `type` (`type`),
			  ADD KEY `ownerID` (`ownerID`),
			  ADD KEY `containerID` (`containerID`);

			ALTER TABLE `graphs`
			  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


			-- tabla graphsDAta --

			CREATE TABLE `graphsData` (
			  `id` int(11) NOT NULL,
			  `graphID` int(11) NOT NULL,
			  `name` varchar(128) NOT NULL,
			  `value` varchar(255) NOT NULL
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;

			ALTER TABLE `graphsData`
			  ADD PRIMARY KEY (`id`),
			  ADD UNIQUE KEY `graphID_name` (`graphID`,`name`),
			  ADD KEY `graphID` (`graphID`),
			  ADD KEY `name_value` (`name`,`value`);

			ALTER TABLE `graphsData`
			  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

			-- tabla graphsRelations --

			CREATE TABLE `graphsRelations` (
			  `id` int(11) NOT NULL,
			  `type` varchar(60) NOT NULL,
			  `fromGraphID` int(11) NOT NULL,
			  `toGraphID` int(11) NOT NULL,
			  `creationTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			  `finishTime` timestamp NULL DEFAULT NULL,
			  `status` enum('ACTIVE','FINISHED') NOT NULL DEFAULT 'ACTIVE'
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;


			ALTER TABLE `graphsRelations`
			  ADD PRIMARY KEY (`id`),
			  ADD KEY `type` (`type`);

			ALTER TABLE `graphsRelations`
			  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
		");
	}
	
	static public function GetList(array $options = []): array {
		$query = Mysql::Table('graphs g');
		$query->column('g.*');
		
		if (!isset($options['limit'])) {
			$options['limit'] = 10;
		}
		
		foreach($options as $option => $value) {
			
			if ($option == 'type') {
				$query->filter($option, $value);
			}
			
			if ($option == 'ownerID') {
				$query->filter($option, $value);
			}
			if ($option == 'containerID') {
				$query->filter($option, $value);
			}
			
			if ($option == 'data') {
				foreach($value as $attr => $attrValue) {
					$aliasName = "gd_{$attr}";
					$query->innerJoin("
						graphsData {$aliasName} ON (
							{$aliasName}.name = '{$attr}' AND 
							{$aliasName}.graphID = g.id
						)
					");
					$query->filter("{$aliasName}.value", $attrValue);
				}
			}
			
			if ($option == 'relationTo') {
				foreach($value as $relationName => $graphID) {
					$aliasName = "grt_{$relationName}";
					$query->innerJoin("graphsRelations {$aliasName} ON (
						{$aliasName}.name = '{$relationName}' AND 
						{$aliasName}.fromGraphID = g.id
					)");
					$query->filter("{$aliasName}.toGraphID", $graphID);
				}
			}
			
			if ($option == 'relationFrom') {
				foreach($value as $relationName => $graphID) {
					$aliasName = "grf_{$relationName}";
					$query->innerJoin("graphsRelations {$aliasName} ON (
						{$aliasName}.name = '{$relationName}' AND 
						{$aliasName}.toGraphID = g.id
					)");
					$query->filter("{$aliasName}.fromGraphID", $graphID);
				}
			}
			
			if ($option == 'id') {
				$query->filter($option, $value);
			}
			
			if ($option == 'offset') {
				$query->offset($value);
			}
			
			if ($option == 'limit') {
				$query->limit($value);
			}
		}
		$query->groupBy('g.id');
		$rs =  $query->select();
		
		$ids = [];
		foreach($rs as $row) {
			$ids[] = $row['id'];
		}
		$data = Mysql::Table('graph_data')
			->graphID($ids)
			->select();
		$indexedData = [];
		foreach($data as $rowData) {
			$graphID = $rowData['graphID'];
			$name = $rowData['name'];
			$value = $rowData['value'];
			if (!isset($indexedData[$graphID])) {
				$indexedData[$graphID] = [];
			}
			$indexedData[$graphID][$name] = $value;
		}
		foreach($rs as &$row) {
			$graphID = $row['id'];
			$row = array_merge($indexedData[$graphID], $row);
		}
		
		return $rs;
	}
	
	static public function Create(string $type, int $ownerID = 0, int $containerID = 0, array $data = []): int {
		$graphID = Mysql::Table('graphs')
				->type($type)
				->ownerID($ownerID)
				->containerID($containerID)
				->insert();
		
		foreach($data as $name => $value) {
			Mysql::Table('graphsData')
					->graphID($graphID)
					->name($name)
					->value($value)
					->insert();
		}
		return $graphID/1;
	}
	
	static public function Query(): GraphSelect {
		return new GraphSelect;
	}
	
	static public function Test() {
		GDB::Select()->data([])->relationTo()->get();
		
	}
}

