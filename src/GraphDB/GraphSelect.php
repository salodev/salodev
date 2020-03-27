<?php

namespace salodev\GraphDB;

class GraphSelect {
	private $_options = [
		'id'           => null,
		'type'         => null,
		'offset'       => 0,
		'limit'        => 100,
		'data'         => [],
		'relationFrom' => [],
		'relationTo'   => [],
	];
	
	public function id(int $value): self {
		$this->_options['id'] = $value;
		return $this;
	}
	
	public function type(string $value): self {
		$this->_options['type'] = $value;
		return $this;
	}
	
	public function offset(int $value): self {
		$this->_options['offset'] = $value;
		return $this;
	}
	
	public function limit(int $value): self {
		$this->_options['limit'] = $value;
		return $this;
	}
	
	public function property(string $name, $value): self {
		$this->_options['data'][$name] = $value;
		return $this;
	}
	
	public function relationTo(string $name, int $graphId): self {
		$this->_options['relationTo'][$name] = $graphId;
		return $this;
	}
	
	public function relationFrom(string $name, int $graphId): self {
		$this->_options['relationFrom'][$name] = $graphId;
		return $this;
	}
	
	public function get() {
		return Graphs::GetList($this->_options);
	}
	
	static public function Instance(): self {
		return new self;
	}
	
}