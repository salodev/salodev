<?php
namespace salodev;

use mysqli_result;
use salodev\Mysql\QueryTable;
use salodev\Mysql\Connection;
use Exception;

class Mysql {
	
	static private $_connections = array();
	static private $_defaultConnection = null;
	
	static public function AddConnection($name, Connection $connection) {
		self::$_connections[$name] = $connection;
		self::SetDefaultConnection($name);
	}
	
	static public function SetDefaultConnection(string $name) {
		self::$_defaultConnection = $name;
	}
	
	static public function GetConnection(): Connection {
		$name = self::$_defaultConnection;
		if (!isset(self::$_connections[$name])) {
			throw new Exception("Connection not found: '{$name}'");
		}
		return self::$_connections[$name];
	}
	
	static public function Query($sql): mysqli_result {
		return self::GetConnection()->query($sql);
	}
	
	static public function MultiQuery($sql): bool {
		return self::GetConnection()->multiQuery($sql);
	}
	
	static public function FetchRow($sql = null): array {
		return self::GetConnection()->fetchRow($sql);
	}
	
	static public function GetResultSet(): array {
		return self::GetConnection()->getResultSet();
	}
	
	static public function GetData($sql): array {
		return self::GetConnection()->getData($sql);
	}
	
	static public function GetInsertID(): int {
		return self::GetConnection()->getInsertID();
	}
	
	static public function GetAffectedRows(): int {
		return self::GetConnection()->getAffectedRows();
	}
	
	static public function Insert($tableName, array $fields, array $fieldsUpdate = []): int {
		return self::GetConnection()->insert($tableName, $fields, $fieldsUpdate);
	}
	
	static public function Update($tableName, array $fields, $fieldsFilter): bool {
		return self::GetConnection()->update($tableName, $fields, $fieldsFilter);
	}
	
	static public function Delete($tableName, array $fieldsFilter): bool {
		return self::GetConnection()->delete($tableName, $fieldsFilter);
	}
	
	static public function GetLastQuery(): string {
		return self::GetConnection()->getLastQuery();
	}
	
	static public function Select($tableName, array $wheres = array(), array $fieldList = array()): array {
		return self::GetConnection()->select($tableName, $wheres, $fieldList);
	}

	static public function Table($name): QueryTable {
		return self::GetConnection()->table($name);
	}
	
	static public function Transaction(): bool {
		return self::GetConnection()->transaction();
	}
	
	static public function Commit(): bool {
		return self::GetConnection()->commit();
	}
	
	static public function Rollback() {
		return self::GetConnection()->rollback();
	}
	
	static public function FixDate($date): string {
		return self::GetConnection()->fixDate($date);
	}
	
	static public function AddDate($date, $count, $period): string {
		return self::GetConnection()->addDate($date, $count, $period);
	}
	
	static public function AtLeastOne(string $errorMessage) {
		return self::GetConnection()->atLeastOne($errorMessage);
	}
}
