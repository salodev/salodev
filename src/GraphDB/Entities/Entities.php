<?php

namespace salodev\GraphDB\Entities;
use salodev\GraphDB\Graphs;

abstract class Entities {
	
	abstract static public function GetEntityName(): string;
	
	static public function Create(int $ownerId, int $containerId, array $data = []): int {
		$type = static::GetEntityName();
		return Graphs::Create($type, $ownerId, $containerId, $data);
	}
	
	static public function GetList(array $options = []) : array {
		$options['type'] = static::GetEntityName();
		return Graphs::GetList($options);
	}
	
	static public function GetComments($graphId) {
		return Comments::GetList([
			'relationTo' => [
				'containedOn' => $graphId,
			],
		]);
	}
}