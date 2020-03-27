<?php

namespace salodev\GraphDB;

use salodev\Mysql;

class Relations {
	
	static public function Add(int $fromGraphId, string $type, int $toGraphId, array $data = []): int {
		$relationId = Mysql::Table('relations')
				->type($type)
				->fromGraphId($fromGraphId)
				->toGraphId($toGraphId)
				->insert();
		
		foreach($data as $name => $value) {
			Mysql::Table('relationsData')
					->relationId($relationId)
					->name($name)
					->value($value)
					->insert();
		}
		
		return $relationId;
	}
}
