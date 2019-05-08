<?php

namespace salodev\Mysql;
use mysqli;
use mysqli_result;
use Exception;

class Connection {
	
	private $_host = null;
	
	private $_user = null;
	
	private $_pass = null;
	
	private $_name = null;
	
	/**
	 * @var \mysqli
	 */
	private $_resource = null;
	/**
	 * @var \mysqli_result
	 */
	private $_lastResult = null;
	
	/**
	 *
	 * @var string 
	 */
	private $_lastQuery = null;
	
	/**
	 *
	 * @var integer 
	 */
	private $_nestedTransactions = 0;
	
	/**
	 *
	 * @var bool 
	 */
	private $_atLeastOne = false;
	
	/**
	 *
	 * @var string 
	 */
	private $_atLeastOneErrorMessage = null;
	
	/**
	 *
	 * @var string 
	 */
	private $_exceptionClass = Exception::class;
	
	/**
	 * 
	 * @var string
	 */
	public $logFile = null;
	
	
	public function __construct(string $host, string $user, string $pass, string $name) {
		$this->setDBConnection($host, $user, $pass, $name);
	}
	
	public function setDBConnection(string $host, string $user, string $pass, string $name) {
		$this->_host = $host;
		$this->_user = $user;
		$this->_pass = $pass;
		$this->_name = $name;
		
		if (is_resource($this->_resource)) {
			$this->_resource->close();
			$this->_resource = null;
		}
	}
	
	/**
	 * 
	 * @return \mysqli;
	 */
	public function connect():mysqli {
		if (!$this->_resource) {
			@$this->_resource = new mysqli($this->_host, $this->_user, $this->_pass, $this->_name);
			if ($this->_resource->connect_error) {
				throw new Exception($this->_resource->connect_error, $this->_resource->connect_errno);
			}
		}
		$this->_resource->query("
			SET 
			character_set_results    = 'utf8', 
			character_set_client     = 'utf8', 
			character_set_connection = 'utf8', 
			character_set_database   = 'utf8', 
			character_set_server     = 'utf8'
		");
		return $this->_resource;
	}
	
	/**
	 * 
	 * @param string $sql
	 */
	public function query(string $sql) {
		$resource = $this->connect();
		if ($this->logFile) {
			$sql = trim($sql);
			@file_put_contents($this->logFile, /*date('Y-m-d H:i:s') .*/ "\n($this->_name) {$sql};\n", FILE_APPEND);
		}
		$this->_autoAddLock($sql);
		$result = $resource->query($sql);
		
		if ($insertID = $this->getInsertID()) {
			@file_put_contents($this->logFile, "INSERT_ID={$insertID};\n", FILE_APPEND);
		}
		@file_put_contents($this->logFile, "\n******************\n", FILE_APPEND);
		$this->_lastQuery = $sql;
		if (!$result) {
			throw new Exception($resource->error . ' (using ' . $this->_name . ')', $resource->errno);
		}
		$this->_lastResult = $result;
		
		if ($this->_atLeastOne) {
			$this->_atLeastOne = false;
			$this->_atLeastOneErrorMessage = null;
			if (!$this->getAffectedRows()) {
				$class = $this->_exceptionClass;
				throw new $class($this->_atLeastOneErrorMessage);
			}
		}
		
		return $result;
	}
	
	public function multiQuery(string $sql): bool {
		$resource = $this->connect();
		$result = $resource->multi_query($sql);
		if (!$result) {
			throw new Exception($resource->error, $resource->errno);
		}
		return true;
	}
	
	public function fetchRow(string $sql = null): array {
		if ($sql !== null) {
			$this->query($sql);
		}
		
		if (!$this->_lastResult) {
			throw new Exception('No result found');
		}
		
		$row = $this->_lastResult->fetch_assoc();
		if (!is_array($row)) {
			$row = [];
		}
		return $row;
	}
	
	public function getResultSet(string $className = null, array $classParams = null): array {
		if (!$this->_lastResult) {
			throw new Exception('No result found');
		}
		$rs = array();
		while (
			$row = $className !== null
			? $this->_lastResult->fetch_object($className, $classParams)
			: $this->_lastResult->fetch_assoc()
		) { $rs[] = $row; }
		return $rs;
	}
	
	public function getData(string $sql, string $className = null, $classParams = null): array {
		$this->query($sql);
		return $this->getResultSet($className, $classParams);
	}
	
	public function getInsertID(): int {
		if (!$this->_resource) {
			throw new Exception('No DB connection');
		}
		return $this->_resource->insert_id;
	}
	
	public function getAffectedRows(): int {
		if (!$this->_resource) {
			throw new Exception('No DB connection');
		}
		return $this->_resource->affected_rows;
		
	}
	
	public function insert(string $tableName, array $fields, array $fieldsUpdate = []): int {
		$sqlFields = [];
		if (!count($fields)) {
			throw new Exception('No fields');
		}
		foreach($fields as $name => $value) {
			$sqlValue = "'{$value}'";
			if ($value === null || $value === '') { 
				$sqlValue = 'NULL'; 
			}
			$sqlFields[] = "\t{$name} = {$sqlValue}";
		}
		$sql  = "INSERT INTO {$tableName} SET \n";
		$sql .= implode(",\n\t", $sqlFields);
		
		$sqlFields = [];
		if (!empty($fieldsUpdate)) {
			foreach($fieldsUpdate as $fieldName) {
				if (!array_key_exists($fieldName, $fields)) {
					throw new Exception("Not provided field '{$fieldName}'.");
				}
				$value = $fields[$fieldName];
				$sqlValue = "'{$value}'";
				if ($value === null || $value === '') { 
					$sqlValue = 'NULL'; 
				}
				$sqlFields[] = "\t{$name} = {$sqlValue}";
			}
			
			$sql .= " \nON DUPLICATE KEY UPDATE \n";
			$sql .= implode(",\n\t", $sqlFields);
		}
		// die($sql);
		$this->query($sql);
		return $this->getInsertID();
	}
	
	public function update(string $tableName, array $fields, $fieldsFilter): bool {
		if (!is_array($fieldsFilter)){
			$fieldsFilter = explode(',', $fieldsFilter);
		}
		foreach($fieldsFilter as &$fieldFilter) {
			$fieldFilter = trim($fieldFilter);
		}
		unset($fieldFilter); // quitamos el puntero.
		$sqlFields = [];
		$sqlFieldsFilter = [];
		if (!count($fields)) {
			throw new Exception('No fields');
		}
		if (!count($fieldsFilter)) {
			throw new Exception('No fields for filter');
		}
		foreach($fields as $name => $value) {
			$sqlValue = "'{$value}'";
			if ($value === null || $value === '') { 
				$sqlValue = 'NULL'; 
			}
			if (in_array($name, $fieldsFilter)) {
				$sqlFieldsFilter[] = "\t{$name} = {$sqlValue}";
			} else {
				$sqlFields[] = "\t{$name} = {$sqlValue}";
			}
		}
		$sql  = "UPDATE {$tableName} SET \n";
		$sql .= implode(",\n", $sqlFields);
		$sql .= "\nWHERE ";
		$sql .= implode(" AND\n", $sqlFieldsFilter);
		
		// die($sql);
		$this->query($sql);
		return true;
	}
	
	public function delete(string $tableName, array $fieldsFilter): bool {
		if (!count($fieldsFilter)) {
			throw new Exception('No fields');
		}
		foreach($fieldsFilter as &$fieldFilter) {
			$fieldFilter = trim($fieldFilter);
		}
		unset($fieldFilter); // quitamos el puntero.
		$sqlFieldsFilter = [];
		foreach($fieldsFilter as $name => $value) {
			$sqlValue = "'{$value}'";
			if ($value === null || $value === '') { 
				$sqlValue = 'NULL'; 
			}
			$sqlFieldsFilter[] = "{$name} = {$sqlValue}";
		}
		$sql  = "DELETE FROM {$tableName} \n";
		$sql .= "WHERE ";
		$sql .= implode("\nAND ", $sqlFieldsFilter);
		
		// die($sql);
		$this->query($sql);
		return true;
	}
	
	public function getLastQuery(): string {
		return $this->_lastQuery;
	}
	
	public function select(string $tableName, array $wheres = array(), array $fieldList = array()): array {
		$sqlFieldList = '*';
		$sqlWhere = '';
		if (!empty($fieldList)) {
			$sqlFieldList = implode(',', $fieldList);
		}
		if (!empty($wheres)) {
			$arrWheres = [];
			foreach($wheres as $fName => $fValue) {
				$arrWheres[] = "`{$fName}` = '{$fValue}'";
			}
			$sqlWhere = "WHERE " . implode(" AND ", $arrWheres);
		}
		return $this->getData("SELECT {$sqlFieldList} FROM {$tableName} {$sqlWhere}");
	}
	
	/**
	 * 
	 * @param type $name
	 * @return \SG\Mysql\QueryTable
	 */
	public function table(string $name): QueryTable {
		return new QueryTable($this, $name);
	}
	
	public function transaction(): bool {
		if ($this->_nestedTransactions==0) {
			$this->query("SET autocommit=0");
			$this->query("START TRANSACTION");
		}
		$this->_nestedTransactions++;
		return true;
	}
	
	public function commit(): bool {
		$this->_nestedTransactions--;
		if ($this->_nestedTransactions<0){
			$this->_nestedTransactions = 0;
		}
		if ($this->_nestedTransactions==0) {
			$this->query("COMMIT");
			$this->query("SET autocommit=1");
		}
		return true;
	}
	
	public function rollback() {
		$this->query("ROLLBACK");
		$this->query("SET autocommit=1");
	}
	
	public function fixDate(string $date): string {
		$date = str_replace('/', '-', $date);
		if (preg_match('/^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/', $date)) {
			return $date;
		}
		if (preg_match('/^([0-9]{2})\-([0-9]{2})\-([0-9]{4})$/', $date)) {
			return implode('-', array_reverse(explode('-', $date)));
		}
		throw new Exception('Malformed date');
	}
	
	public function addDate(string $date, int $count, string $period): string {
		$row = $this->fetchRow("SELECT ADDDATE('{$date}', INTERVAL {$count} {$period}) AS newDate");
		return $row['newDate'];
	}
	
	public function atLeastOne(string $errorMessage, string $exceptionClass = Exception::class) {
		$this->_atLeastOne = true;
		$this->_atLeastOneErrorMessage = $errorMessage;
		$this->_exceptionClass = $exceptionClass;
	}
	
	private function _autoAddLock(string $sql): string {
		if ($this->_nestedTransactions <= 0) {
			return $sql;
		}
		$t1 = strpos(strtolower($sql), 'select'    ) !== false;
		$t2 = strpos(strtolower($sql), 'from'      ) !== false;
		$t3 = strpos(strtolower($sql), 'for update') === false;
		$t4 = strpos(strtolower($sql), 'lock'      ) === false;
		if ($t1 && $t2 && ($t3 || $t4)){
			$sql .= "\nFOR UPDATE";
		}
		return $sql;
	}
}