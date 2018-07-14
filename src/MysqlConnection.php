<?php
namespace salodev;

use Exception;

class MysqlConnection {
	private $_link     = null;
	private $_host     = null;
	private $_user     = null;
	private $_password = null;
	private $_dbname   = null;
			
	private $_connectionId = null;
	public function __construct($host, $user, $password, $dbname) {
		$this->_host     = $host;
		$this->_user     = $user;
		$this->_password = $password;
		$this->_dbname   = $dbname;
		// $this->connect();
	}
	
	public function connect() {
		$this->_link = mysqli_connect($this->_host, $this->_user, $this->_password, $this->_dbname);
		if (!$this->_link) {
			mysqli_close($this->_link);
			throw new Exception(mysqli_connect_error(), mysqli_connect_errno());
		}
		$this->selectDB($this->_dbname);
		return $this->query("SELECT CONNECTION_ID() AS CID", function($rs) {
			$this->_connectionID = $rs[0]['CID'];
		});
	}
	
	public function close() {
		mysqli_close($this->_link);
		$this->_link = null;
	}
	
	/**
	 * @return \salodev\Deferred
	 */
	public function duplicate() {
		$connection = new MysqlConnection($this->_host, $this->_user, $this->_password, $this->_dbname);
		return $connection;
		return $connection->connect();
	}
	
	public function selectDB($dbName) {
		$this->_dbname = $dbName;
		$ret = mysqli_select_db($this->_link, $dbName);
		if (!$ret) {
			throw new Exception(mysqli_error(), mysqli_errno());
		}
		return true;
	}
	
	/**
	 * 
	 * @param string $query sql query code
	 * @param callable $callback function to add to done deferred
	 * @return \salodev\Deferred
	 * @throws \Exception
	 */
	public function query (string $query, callable $callback = null): Promise {
		$deferred = new Deferred();
		if (is_callable($callback)) {
			$deferred->done($callback);
		}
		mysqli_store_result($this->_link);
		$ret = mysqli_query($this->_link, $query, MYSQLI_ASYNC);
		if (!$ret) {
			$error = mysqli_error($this->_link);
			$errno = mysqli_errno($this->_link);
			if ($errno == 2006) {
				$this->connect();
			}
			$deferred->reject($error);
			throw new Exception($error, $errno);
		}
		Worker::AddTask(function($taskIndex) use($deferred) {
			if ($this->poll()) {
				Worker::RemoveTask($taskIndex);
				$result = $this->reapAsyncQuery();
				$class = get_class($result);
				if ($class != 'mysqli_result') {
					$deferred->reject();
					throw new Exception(mysqli_error($this->_link), mysqli_errno($this->_link));
				}
				$rs = array();
				while($row = $result->fetch_assoc()) {
					$rs[] = $row;
				}
				$result->free();
				$deferred->resolve($rs, $this);
			};
		}, true , 'MYSQL QUERY RESULT LISTENER');
		return $deferred->getPromise();
	}
	
	public function killQuery() {
		$ret = mysqli_kill($this->_link, $this->_connectionId);
		if (!$ret) {
			throw new Exception(mysqli_error($this->_link), mysqli_errno($this->_link));
		}
	}
	
	public function poll() {
		$links = array($this->_link);
		return mysqli_poll($links , $links , $links , 0, 1);
	}
	
	public function reapAsyncQuery() {
		return mysqli_reap_async_query($this->_link);
	}
}