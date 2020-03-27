<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace salodev\GraphDB;

/**
 * Description of Graph
 *
 * @author salomon
 */
abstract class Relation {
	
	public $id;
	public $type;
	public $fromGraphId;
	public $toGraphId;
	public $creationDate;
	
	abstract static public function GetLargeDataFields(): array;
	
	abstract static public function GetType(): string;
	
	static public function GetList(int $offset = 0, int $limit = 0, array $options = []): array {
		$options['type'] = static::GetType();
		$rs = Relations::GetList($offset, $limit, $options);
		$colection = [];
		foreach($rs as $row) {
			$graph = new static($row);
		}
		return $collection;
	}
	
	static public function GetInstance(int $graphId, array $options = []): self {
		return new static(Relations::GetData($graphId, $options));
	}
	
	final public function __construct(array $data = []) {
		foreach($this as $name => $value) {
			$this->$name = $data[$name]??null;
		}
	}
	
	private function getData(): array {
		$data      = [];
		$largeData = [];
		foreach($this as $name => $value) {
			if (in_array($name, ['id', 'type', 'creationDate', 'fromGraphId', 'toGraphId'])) { continue; }
			$data[$name] = $value;
		}
		return $data;
	}
	
	public function add(Graph $fromGraph, Graph $toGraph): int {
		$data = $this->getData();
		$graphId  = Relations::Add($this->fromGraphId, static::GetType(), $this->toGraphIid, $this->getData());
		$this->id = $graphId;
		return $graphId;
	}
	
	public function update(): bool {
		return Relations::Update($this->id, $this->getData($data));
	}
}