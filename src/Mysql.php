<?php
namespace salodev;

use salodev\Mysql\QueryTable;
use salodev\Mysql\Connection;
use Exception;

class Mysql {
	
	static protected $_connections = array();
	static protected $_defaultConnection = null;
	
	static public function Instance(string $name): Connection {
		static::SetDefaultConnection($name);
		return self::GetConnection();
	}
	
	static public function AddConnection($name, Connection $connection) {
		static::$_connections[$name] = $connection;
		static::SetDefaultConnection($name);
	}
	
	static public function SetDefaultConnection(string $name) {
		static::$_defaultConnection = $name;
	}
	
	static public function GetConnection(string $name = null): Connection {
		if ($name == null) {
			$name = static::$_defaultConnection;
		}
		if (!isset(static::$_connections[$name])) {
			throw new Exception("Connection not found: '{$name}'");
		}
		return static::$_connections[$name];
	}
	
	static public function Query($sql) {
		return static::GetConnection()->query($sql);
	}
	
	static public function MultiQuery($sql): bool {
		return static::GetConnection()->multiQuery($sql);
	}
	
	static public function FetchRow($sql = null): array {
		return static::GetConnection()->fetchRow($sql);
	}
	
	static public function GetResultSet(): array {
		return static::GetConnection()->getResultSet();
	}
	
	static public function GetData($sql): array {
		return static::GetConnection()->getData($sql);
	}
	
	static public function GetInsertID(): int {
		return static::GetConnection()->getInsertID();
	}
	
	static public function GetAffectedRows(): int {
		return static::GetConnection()->getAffectedRows();
	}
	
	static public function Insert($tableName, array $fields, array $fieldsUpdate = []): int {
		return static::GetConnection()->insert($tableName, $fields, $fieldsUpdate);
	}
	
	static public function Update($tableName, array $fields, $fieldsFilter): bool {
		return static::GetConnection()->update($tableName, $fields, $fieldsFilter);
	}
	
	static public function Delete($tableName, array $fieldsFilter): bool {
		return static::GetConnection()->delete($tableName, $fieldsFilter);
	}
	
	static public function GetLastQuery(): string {
		return static::GetConnection()->getLastQuery();
	}
	
	static public function Select($tableName, array $wheres = array(), array $fieldList = array()): array {
		return static::GetConnection()->select($tableName, $wheres, $fieldList);
	}

	static public function Table($name): QueryTable {
		return static::GetConnection()->table($name);
	}
	
	static public function Transaction(): bool {
		return static::GetConnection()->transaction();
	}
	
	static public function Commit(): bool {
		return static::GetConnection()->commit();
	}
	
	static public function Rollback() {
		return static::GetConnection()->rollback();
	}
	
	static public function FixDate($date): string {
		return static::GetConnection()->fixDate($date);
	}
	
	static public function AddDate($date, $count, $period): string {
		return static::GetConnection()->addDate($date, $count, $period);
	}
	
	static public function AtLeastOne(string $errorMessage, string $exceptionClass = \Exception::class) {
		return static::GetConnection()->atLeastOne($errorMessage, $exceptionClass);
	}
}
