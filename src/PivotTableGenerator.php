<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace salodev;

/**
 * Description of PivotTableGenerator
 *
 * @author salomon
 */
class PivotTableGenerator {
	//put your code here
	
	private $_data        = [];
	private $_groupBy     = [];
	private $_columnsBy   = null;
	private $_valuesBy    = null;
	private $_showColumns = [];
	private $_fnResult    = null;
	
	public function setData(array $data): self {
		$this->_data = $data;
		return $this;
	}
	
	public function getData(): array {
		return $this->_data;
	}
	
	public function groupBy(...$names): self {
		if (empty($names)) {
			throw new \Exception('No columns names! Specify a name or a name list of columns by you want to group results');
		}
		$this->_groupBy = $names;
		return $this;
	}
	
	public function columnsBy(string $name): self {
		$this->_columnsBy = $name;
		return $this;
	}
	
	public function valuesBy(string $name): self {
		$this->_valuesBy = $name;
		return $this;
	}
	
	public function showColumns(string ...$names): self {
		$this->_showColumns = $names;
		return $this;
	}
	
	public function setFnResult(callable $fn):self {
		$this->_fnResult = $fn;
		return $this;
	}
	
	public function resultAvg(): self {
		$this->setFnResult(function(array $values) {
			if (!count($values)) {
				return 0;
			}
			return array_sum($values) / count($values);
		});
		return $this;
	}
	
	public function resultMax(): self {
		$this->setFnResult(function(array $values) {
			if (!count($values)) {
				return 0;
			}
			return max($values);
		});
		return $this;
	}
	
	public function resultMin(): self {
		$this->setFnResult(function(array $values) {
			if (!count($values)) {
				return 0;
			}
			return min($values);
		});
		return $this;
	}
	
	public function resultFirst(): self {
		$this->setFnResult(function(array $values) {
			if (!count($values)) {
				return null;
			}
			return $values[0];
		});
		return $this;
	}
	
	public function resultLast(): self {
		$this->setFnResult(function(array $values) {
			if (!count($values)) {
				return null;
			}			
			return array_pop($values);
		});
		return $this;
	}
	
	public function transform(): array {
		
		if (empty($this->_data)) {
			throw new \Exception('Please provide data for transformation! Use setData() method passing a ResultSet array format.');
		}
		
		if (empty($this->_groupBy)) {
			throw new \Exception('No columns names! Please call groupBy() passing a name or a name list of columns by you want to group results');
		}
		
		if (empty($this->_columnsBy)) {
			throw new \Exception('No field for dynamic columns! Please call columnsBy() passing a column name by you want to generate dynamic columns. The row value for this field will produce a column on your result');
		}
		
		if (empty($this->_valuesBy)) {
			throw new \Exception('No field for dynamic column values! Please call valuesBy() passing a column name that contains data you want to fill dynamic columns!');
		}
		
		$pivot = [];
		$dymaicColumnNames = [];
		
		// Get all possible column names;
		foreach($this->_data as $row) {
			$columnName  = $row[$this->_columnsBy];
			$dymaicColumnNames[$columnName] = $columnName;
		}
		
		// Preparate entire pivot table
		foreach($this->_data as $row) {
			$id = $this->_getRowIdentifier($row);
			
			$showColumns = $this->_showColumns;
			if (!count($showColumns)) {
				$showColumns = array_keys($row);
			}
			
			foreach($showColumns as $name) {
				if ($name != $this->_columnsBy && $name != $this->_valuesBy) {
					$pivot[$id][$name] = $row[$name];
				}
			}
			
			foreach($dymaicColumnNames as $columnName) {
				$pivot[$id][$columnName] = [];
			}
		}
		
		// Fill it with data.
		foreach($this->_data as $row) {
			$id = $this->_getRowIdentifier($row);
			$columnName  = $row[$this->_columnsBy];
			$columnValue = $row[$this->_valuesBy ];			
			$pivot[$id][$columnName][] = $columnValue;
		}
		
		// Now time to process cell results.		
		foreach($pivot as $id => &$row) {
			foreach($dymaicColumnNames as $columnName) {
				$row[$columnName] = $this->_processValues($row[$columnName]??[]);
			}			
		}
		return array_values($pivot);
	}
	
	private function _getRowIdentifier(array $row): string {
		if (!count($row)) {
			throw new \Exception('Row is empty');
		}
		if (!count($this->_groupBy)) {
			throw new \Exception('No columns defined for grouping. Please use ->groupBy() method to specify at least one.');
		}
		
		$values = [];
		foreach($this->_groupBy as $name) {
			if (!array_key_exists($name, $row)) {
				throw new \Exception("Column '{$name}' for current row does not exist! Try remove it from groupBy or ensure data has this column.");
			}
			$values[] = $row[$name];
		}
		return implode('_', $values);
	}
	
	private function _processValues(array $values) {
		if ($this->_fnResult == null) {
			$this->resultLast();
		}
		$fn = $this->_fnResult;
		return $fn($values);
	}
}
