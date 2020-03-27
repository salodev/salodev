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
abstract class Graph {
	
	public $id;
	public $creationDate;
	private $largeDataLoaded = false;
	
	abstract static public function GetLargeDataFields(): array;
	
	abstract static public function GetType(): string;
	
	static public function GetList(int $offset = 0, int $limit = 0, array $options = []): array {
		$options['type'] = static::GetType();
		$rs = Graphs::GetList($offset, $limit, $options);
		$colection = [];
		foreach($rs as $row) {
			$graph = new static($row, $options['largeData']??false);
		}
		return $collection;
	}
	
	static public function GetInstance(int $graphId, array $options = []): self {
		return new static(Graphs::GetData($graphId, $options), $options['largeData']??false);
	}
	
	static public function Create(array $data, array $largeData = []): int {
		return Graphs::Create(static::GetType(), $data, $largeData);
	}
	
	final public function __construct(array $data = [], bool $largeDataLoaded = false) {
		foreach($this as $name => $value) {
			$this->$name = $data[$name]??null;
		}
		$this->largeDataLoaded = $largeDataLoaded;
	}
	
	private function getDataAndLargeData(array &$data, array &$largeData): void {
		$data      = [];
		$largeData = [];
		foreach($this as $name => $value) {
			if (in_array($name, ['id', 'type', 'creationDate'])) { continue; }
			if (in_array($name, static::GetLargeDataFields())) {
				if ($this->largeDataLoaded) {
					$largeData[$name] = $value;
				}
			} else {
				$data[$name] = $value;
			}
		}

	}
	
	public function add(): int {
		$this->getDataAndLargeData($data, $largeData);
		$graphId  = Graphs::Create(static::GetType(), $data, $largeData);
		$this->id = $graphId;
		return $graphId;
	}
	
	public function update(): bool {
		$this->getDataAndLargeData($data, $largeData);
		return Graphs::Update($this->id, $data, $largeData);
	}
	
	public function bindTo(Graph $graph, $relationClassName, array $relationData): Relation {
		$type = $relationClassName::GetType();
		$relationId = Relations::Add($this->id, $type, $graph->id, $relationData);
		return $relationClassName::GetData($relationId);
	}
	
	public function bindFrom(Graph $graph, $relationClassName, array $relationData): Relation {
		$type = $relationClassName::GetType();
		$relationId = Relations::Add($graph->id, $type, $this->id, $relationData);
		return $relationClassName::GetData($relationId);
	}
	
}