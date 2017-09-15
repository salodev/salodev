<?php
namespace salodev;
use mysqli;
use salodev\Mysql\QueryTable;
use Exception;

class Mysql {
	
	static private $_host = null;
	
	static private $_user = null;
	
	static private $_pass = null;
	
	static private $_name = null;
	
	/**
	 * @var \mysqli
	 */
	static private $_resource = null;
	/**
	 * @var \mysqli_result
	 */
	static private $_lastResult = null;
	
	/**
	 *
	 * @var string 
	 */
	static private $_lastQuery = null;
	
	/**
	 *
	 * @var integer 
	 */
	static private $_nestedTransactions = 0;
	
	/**
	 *
	 * @var bool 
	 */
	static private $_atLeastOne = false;
	
	/**
	 *
	 * @var string 
	 */
	static private $_atLeastOneErrorMessage = null;
	
	/**
	 * 
	 * @var string
	 */
	static public $logFile = null;
	
	static public function SetDBConnection($host, $user, $pass, $name) {
		self::$_host = $host;
		self::$_user = $user;
		self::$_pass = $pass;
		self::$_name = $name;
		
		if (is_resource(self::$_resource)) {
			self::$_resource->close();
			self::$_resource = null;
		}
	}
	
	/**
	 * 
	 * @return \mysqli;
	 */
	static public function Connect() {
		if (!self::$_resource) {
			@self::$_resource = new mysqli(self::$_host, self::$_user, self::$_pass, self::$_name);
			if (self::$_resource->connect_error) {
				throw new Exception(self::$_resource->connect_error, self::$_resource->connect_errno);
			}
		}
		self::$_resource->query("
			SET 
			character_set_results    = 'utf8', 
			character_set_client     = 'utf8', 
			character_set_connection = 'utf8', 
			character_set_database   = 'utf8', 
			character_set_server     = 'utf8'
		");
		return self::$_resource;
	}
	
	/**
	 * 
	 * @param string $sql
	 * @return \mysqli_result;
	 */
	static public function Query($sql) {
		$resource = self::Connect();
		if (self::$logFile) {
			$sql = trim($sql);
			@file_put_contents(self::$logFile, /*date('Y-m-d H:i:s') .*/ "\n{$sql};\n", FILE_APPEND);
		}
		$result = $resource->query($sql);
		if ($insertID = self::GetInsertID()) {
			@file_put_contents(self::$logFile, "INSERT_ID={$insertID};\n", FILE_APPEND);
		}
		@file_put_contents(self::$logFile, "\n******************\n", FILE_APPEND);
		self::$_lastQuery = $sql;
		if (!$result) {
			throw new Exception($resource->error . ' (using ' . self::$_name . ')', $resource->errno);
		}
		self::$_lastResult = $result;
		
		if (self::$_atLeastOne) {
			if (!self::GetAffectedRows()) {
				throw new Exception(self::$_atLeastOneErrorMessage);
			}
			self::$_atLeastOne = false;
			self::$_atLeastOneErrorMessage = null;
		}
		
		return $result;
	}
	
	static public function MultiQuery($sql) {
		$resource = self::Connect();
		$result = $resource->multi_query($sql);
		if (!$result) {
			throw new Exception($resource->error, $resource->errno);
		}
	}
	
	static public function FetchRow($sql = null) {
		if ($sql !== null) {
			self::Query($sql);
		}
		
		if (!self::$_lastResult) {
			throw new Exception('No result found');
		}
		
		$row = self::$_lastResult->fetch_assoc();
		return $row;
	}
	
	static public function GetResultSet() {
		if (!self::$_lastResult) {
			throw new Exception('No result found');
		}
		$rs = array();
		while ($row = self::$_lastResult->fetch_assoc()) {
			$rs[] = $row;
		}
		return $rs;
	}
	
	static public function GetData($sql) {
		self::Query($sql);
		return self::GetResultSet();
	}
	
	static public function GetInsertID() {
		if (!self::$_resource) {
			throw new Exception('No DB connection');
		}
		return self::$_resource->insert_id;
	}
	
	static public function GetAffectedRows() {
		if (!self::$_resource) {
			throw new Exception('No DB connection');
		}
		return self::$_resource->affected_rows;
		
	}
	
	static public function Insert($tableName, array $fields, array $fieldsUpdate = []) {
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
		Mysql::Query($sql);
		return Mysql::GetInsertID();
	}
	
	static public function Update($tableName, array $fields, $fieldsFilter) {
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
		Mysql::Query($sql);
		return true;
	}
	
	static public function Delete($tableName, array $fieldsFilter) {
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
		Mysql::Query($sql);
		return true;
	}
	
	static public function GetLastQuery() {
		return self::$_lastQuery;
	}
	
	static public function Select($tableName, array $wheres = array(), array $fieldList = array()) {
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
		return Mysql::GetData("SELECT {$sqlFieldList} FROM {$tableName} {$sqlWhere}");
	}
	
	/**
	 * 
	 * @param type $name
	 * @return \SG\Mysql\QueryTable
	 */
	static public function Table($name): QueryTable {
		return new QueryTable($name);
	}
	
	static public function Transaction() {
		if (self::$_nestedTransactions==0) {
			Mysql::Query("SET autocommit=0");
			Mysql::Query("START TRANSACTION");
		}
		self::$_nestedTransactions++;
		return true;
	}
	
	static public function Commit() {
		self::$_nestedTransactions--;
		if (self::$_nestedTransactions<0){
			self::$_nestedTransactions = 0;
		}
		if (self::$_nestedTransactions==0) {
			Mysql::Query("COMMIT");
			Mysql::Query("SET autocommit=1");
		}
		return true;
	}
	
	static public function Rollback() {
		Mysql::Query("ROLLBACK");
			Mysql::Query("SET autocommit=1");
	}
	
	static public function FixDate($date) {
		$date = str_replace('/', '-', $date);
		if (preg_match('/^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/', $date)) {
			return $date;
		}
		if (preg_match('/^([0-9]{2})\-([0-9]{2})\-([0-9]{4})$/', $date)) {
			return implode('-', array_reverse(explode('-', $date)));
		}
		throw new Exception('Malformed date');
	}
	
	static public function AddDate($date, $count, $period) {
		$row = self::FetchRow("SELECT ADDDATE('{$date}', INTERVAL {$count} {$period}) AS newDate");
		return $row['newDate'];
	}
	
	static public function AtLeastOne(string $errorMessage) {
		self::$_atLeastOne = true;
		self::$_atLeastOneErrorMessage = $errorMessage;
	}
}
