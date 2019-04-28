<?php

namespace salodev\Entities;

abstract class Entities {
	
	static protected $_identifierName = 'id';
	static protected $_singularName   = 'entity';
	static protected $_pluralName     = 'entity';
	static protected $_foreignIdentifierName = 'entityID';
	
	static public function Instance(): self {
		return new static;
	}
	
	abstract public function getData(array $filters = [], array $options = []): array;
	
	final public function getList(array $filters = [], array $options = []): Collection {
		$data = $this->getData($filters, $options);
		$entityName = get_class($this);
		return new Collection($entityName, $data);
	}
	
	static public function GetIdentifierName() {
		return static::$_identifierName;
	}
	
	static public function GetForeignEntityName() {
		$fullClass = static::class;
		$shortClass = explode('\\', $fullClass);
		return lcfirst(array_pop($shortClass));
	}
	
	static public function GetForeignIdentifierName() {
		return lcfirst(static::GetForeignEntityName()) . 'ID';
	}
	
	public function getOne(array $filters = [], array $options = []): array {
		$list = $this->getList($filters, $options);
		if (!count($list)) {
			throw new Exception('No records found');
		}
		
		if (count($list)>1) {
			throw new Exception('More than one found');
		}
		
		return $list[0];
	}
	
	public function get(int $identifier): array {
		return $this->getOne([$this->_identifierName=>$identifier]);
	}
}


