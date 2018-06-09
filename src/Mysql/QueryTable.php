<?php

namespace salodev\Mysql;

use salodev\Mysql\QueryException;

class QueryTable {
	private $_connection;
	private $_name;
	private $_eqFilters     = [];
	private $_customFilters = [];
	private $_useLimits     = false;
	private $_columnList    = [];
	private $_orders        = [];
	private $_innerJoins    = [];
	private $_leftJoins     = [];
	private $_offset        = 0;
	private $_groupBy       = [];
	private $_limit         = 100;
	
	public function __construct(Connection $connection, string $name) {
		$this->_connection = $connection;
		$this->_name = $name;
	}
	
	public function __set($name, $value) {
		$this->filter($name, $value);
	}
	
	public function __call($name, $arguments): self {
		$this->filter($name, $arguments[0]);
		return $this;
	}
	
	public function innerJoin($portion): self {
		$this->_innerJoins[] = "INNER JOIN {$portion}";
		return $this;
	}
	
	public function leftJoin($portion): self {
		$this->_leftJoins[] = "LEFT JOIN {$portion}";
		return $this;
	}
	
	public function groupBy($field): self {
		$this->_groupBy[] = $field;
		return $this;
	}
	
	public function filter($fieldName, $fieldValue): self {
		$this->_eqFilters[$fieldName] = $fieldValue;
		return $this;
	}
	
	public function like($fieldName, $fieldValue): self {
		$fieldName = $this->_refactorColumnName($fieldName);
		$this->customFilter("{$fieldName} LIKE '%{$fieldValue}%'");
		return $this;
	}
	
	public function customFilter($sqlText): self {
		$this->_customFilters[] = $sqlText;
		return $this;
	}
	
	public function column($name): self {
		$this->_columnList[]= $name;
		return $this;
	}
	public function columns(array $names): self {
		$this->_columnList = array_merge($this->_columnList, $names);
		return $this;
	}
	
	public function limits($offset, $limit): self {
		$this->_useLimits = true;
		$this->_offset = $offset;
		$this->_limit = $limit;
		return $this;
	}
	
	public function limit($limit): self {
		$this->_useLimits = true;
		$this->_limit = $limit;
		return $this;
	}
	
	public function offset($offset): self {
		$this->_useLimits = true;
		$this->_offset = $offset;
		return $this;
	}
	
	/**
	 * 
	 * @param string $name
	 * @param bool $asc
	 * @return $this
	 */
	public function order($name, $asc = true): self {
		$this->_orders[$name] = $asc;
		return $this;
	}
	
	private function _refactorColumnName($name) {
		$parts = explode('.', $name);
		if (count($parts)>2){
			throw new QueryException('Malformed column name');
		}
		if (count($parts)==2) {
			$parts[1] = str_replace('`', '', $parts[1]);
			return "{$parts[0]}.`{$parts[1]}`";
		} else {
			return $parts[0];
		}		
	}
	
	private function _getSQLWhere() {
		$sqlWhere = '';
		$wheres = [];
		if (count($this->_eqFilters)) {
			foreach($this->_eqFilters as $column => $value) {
				if ($value===NULL) {
					$wheres[] = "{$column} IS NULL";
				} elseif (is_array($value)) {
					$wheres[] = "{$column} IN('" . implode("', '", $value) . "')";
				} else {
					$wheres[] = "{$column} = '{$value}'";
				}
			}
		}
		if (count($this->_customFilters)) {
			$wheres = array_merge($wheres, $this->_customFilters);
		}
		if (count($wheres)) {
			$sqlWhere = "WHERE " . implode(' AND ', $wheres);
		}
		return $sqlWhere;
	}
	
	private function _getSQLOrder() {
		if (!count($this->_orders)) {
			return '';
		}
		$orders = [];
		foreach($this->_orders as $column => $asc) {
			$orders[] = $column . ' ' . ($asc?'ASC':'DESC');
		}
		$sqlOrder = "ORDER BY " . implode(', ', $orders);
		return $sqlOrder;
	}
	
	private function _getSQLGroup() {
		if (!count($this->_groupBy)) {
			return '';
		}
		return "GROUP BY " . implode(', ', $this->_groupBy);
	}
	
	public function getSelectSQL() {
		$sqlColumnList = '*';
		if (count($this->_columnList)) {
			$sqlColumnList = implode(', ', $this->_columnList);
		}
		
		$sqlInnerJoin = implode("\n", $this->_innerJoins);
		$sqlLeftJoin = implode("\n", $this->_leftJoins);
		$sqlWhere = $this->_getSQLWhere();
		$sqlOrder = $this->_getSQLOrder();
		$sqlGroup = $this->_getSQLGroup();
		
		$sqlLimit = '';
		if ($this->_useLimits) {
			$sqlLimit = "LIMIT {$this->_offset}, {$this->_limit}";
		}
		$sql = "
			SELECT {$sqlColumnList}
			FROM {$this->_name}
			{$sqlInnerJoin}
			{$sqlLeftJoin}
			{$sqlWhere}
			{$sqlGroup}
			{$sqlOrder}
			{$sqlLimit}
		";
		//die($sql);
		return $sql;
	}
	
	public function getDeleteSQL() {
		$sqlWhere = $this->_getSQLWhere();
		$sqlOrder = $this->_getSQLOrder();
		$sqlLimit = '';
		if ($this->_useLimits) {
			$sqlLimit = "LIMIT {$this->_limit}";
		}
		$sql = "
			DELETE FROM {$this->_name}
			{$sqlWhere}
			{$sqlOrder}
			{$sqlLimit}
		";
		//die($sql);
		return $sql;
	}
	
	public function getInsertSQL() {
		if (!count($this->_eqFilters)) {
			throw new Exception('Missing insert fields');
		}
		$columns = [];
		foreach($this->_eqFilters as $column => $value) {
			$columns[] = $value===null?"{$column} = NULL":"{$column} = '{$value}'";
		}
		return "INSERT INTO {$this->_name} SET \n" . implode(",\n\t", $columns);
	}
	
	public function getUpdateSQL(array $set) {
		$sqlWhere = $this->_getSQLWhere();
		$sqlOrder = $this->_getSQLOrder();
		$sqlLimit = '';
		if ($this->_useLimits) {
			$sqlLimit = "LIMIT {$this->_limit}";
		}
		
		$columns = [];
		foreach($set as $column => $value) {
			$columns[] = $value===null?"{$column} IS NULL":"{$column} = '{$value}'";
		}
		$sqlSet = implode(",\n\t", $columns);
		
		return "
			UPDATE {$this->_name} SET
				{$sqlSet}
			{$sqlWhere}
			{$sqlOrder}
			{$sqlLimit}
		";
		
	}
	
	public function select(array $params = []) {
		$this->_parseSelectParameters($params);
		$sql = $this->getSelectSQL();
		return $this->_connection->getData($sql);
	}
	
	public function delete() {
		$sql = $this->getDeleteSQL();
		return $this->_connection->query($sql);
	}
	
	public function insert() {
		$sql = $this->getInsertSQL();
		$this->_connection->query($sql);
		return $this->_connection->getInsertID();
	}
	
	public function update(array $fields) {
		$sql = $this->getUpdateSQL($fields);
		return $this->_connection->query($sql);
	}
	
	public function fetchRow($column = null) {
		$sql = $this->getSelectSQL();
		$this->_connection->query($sql);
		$row = $this->_connection->fetchRow();
		if ($column) {
			if (!array_key_exists($column, $row)) {
				throw new QueryException("Column '{$column}' does not exist.");
			}
			return $row[$column];
		}
		return $row;
	}
	
	public function getCountRows() {
		$row = $this->column('IFNULL(COUNT(*), 0) AS count')->fetchRow();
		return $row['count']/1;
	}
	
	public function atLeastOne(string $errorMessage): self {
		$this->_connection->atLeastOne($errorMessage);
		return $this;
	}
	
	private function _parseSelectParameters(array $params = []) {
		foreach($params as $name => $value) {
			switch($name) {
				case 'limit':
					$this->limit($value);
					break;
				case 'offset':
					$this->offset($value);
					break;
				case 'limits':
					if (!(is_array($value) && isset($value[0]) && isset($value[1]))) {
						throw new Exception('Incorrect limits values');
					}
					$this->limits($value[0], $value[1]);
					break;
				case 'columns':
					if (!is_array($value)) {
						throw new Exception('Incorrect columns value');
					}
					$this->columns($value);
				default;
					if (is_array($value)) {
						if (isset($value['like'])) {
							$this->like($name, $value['like']);
							break;
						}
						throw new Exception('Incorrect \'like\' value filter');
					}
					$this->filter($name, $value);
					break;
			}
		}
	}
	
}