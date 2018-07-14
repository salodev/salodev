<?php

namespace salodev\GraphDB\Entities;
use salodev\GraphDB\Graphs;

abstract class Entities {
	
	abstract static public function GetEntityName(): string;
	
	static public function Create(int $ownerID, int $containerID, array $data = []): int {
		$type = static::GetEntityName();
		return Graphs::Create($type, $ownerID, $containerID, $data);
	}
	
	static public function GetList(array $options = []) : array {
		$options['type'] = static::GetEntityName();
		return Graphs::GetList($options);
	}
	
	static public function GetComments($graphID) {
		return Comments::GetList([
			'relationTo' => [
				'containedOn' => $graphID,
			],
		]);
	}
}