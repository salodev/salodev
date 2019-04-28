<?php

namespace salodev\Entities;
use ArrayIterator;

class Collection extends ArrayIterator{
	private $_entityName;
	
	public function __construct(string $entityName, array $data = []) {
		parent::__construct($data);
		$this->_entityName = $entityName;
	}
	
	public function joinWithForeign(string $entitiesHandlerName): self {
		$foreignIdentifierName = $entitiesHandlerName::GetForeignIdentifierName();
		$foreignEntityName = $entitiesHandlerName::GetForeignEntityName();
		$coll = $entitiesHandlerName::Instance()->getList([
			'id' => $this->getFieldValues($foreignIdentifierName),
		]);
		foreach($this as &$entity) {
			$foreignEntityID = $entity[$foreignIdentifierName];
			$foreignEntity = $coll->getByID($foreignEntityID);
			$entity[$foreignEntityName] = $foreignEntity;
			unset($entity[$foreignIdentifierName]);
		}
		return $this;
	}
	
	public function joinWithRelated(string $entitiesHandlerName): self {
		return $this;
	}
	
	public function getFieldValues(string $fieldName): array {
		$list = [];
		foreach($this as $entity) {
			if (!array_key_exists($fieldName, $entity)) {
				throw new Exception("Key '{$fieldName}' for {$this->_entityName} does not exist.");
			}
			$list[] = $entity[$fieldName];
		}
		return array_unique($list);
	}
	
	public function getByID(int $id): array {
		$entityName = $this->_entityName;
		$identifierName = $entityName::GetIdentifierName();
		foreach($this as $item) {
			if ($item[$identifierName]==$id) {
				return $item;
			}
		}
		return [];
	}
}