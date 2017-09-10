<?php
namespace salodev;

use Exception;

class Socket extends Stream {
    private $resource = null;
	private $readBuffer = null;
    
    public function __construct($resource = null) {
        if ($resource !== null && !is_resource($resource)) {
            throw new Exception('Parameter given must be null or valid resource');
        }
        $this->resource = $resource;
    }
	
	public function isValidResource() {
		return ($this->resource !== null && is_resource($this->resource));
	}
	
	public function open(array $options = []) {}
    
    public function create($domain = AF_INET, $type = SOCK_STREAM, $protocol = 0) {
        $resource = socket_create($domain, $type , $protocol);
        if (!is_resource($resource)) {
            throw new Exception($this->getErrorText(), $this->getLastError());
        }
        $this->resource = $resource;
		$this->setNonBlocking();
    }
    
    public function getLastError() {
        return socket_last_error($this->resource);
    }
    
    public function getErrorText() {
        return socket_strerror($this->resource);
    }
    
    public function setNonBlocking() {
        socket_set_nonblock($this->resource);
    }
    
    public function setBlocking() {
        socket_set_block($this->resource);
    }
    
    public function setOption($level, $name, $val) {
        socket_set_option($this->resource, $level, $name, $val);
    }
    
    public function bind($address, $port) {
        return socket_bind($this->resource, $address, $port);
    }
    
    public function listen($backlog = 0) {
        return socket_listen($this->resource, $backlog);
    }
    
	/**
	 * 
	 * @return \salodev\Socket
	 */
    public function accept()/*: Socket*/ {
        $newResource = socket_accept($this->resource);
        if (!$newResource) {
            return false;
        }
        $newSocket = new Socket($newResource);
		$newSocket->setNonBlocking();
        return $newSocket;
    }
    
    public function close() {
        socket_close($this->resource);
    }
    
    public function read(int $length = 256, int $type = PHP_BINARY_READ) {
		if (!$this->isValidResource()) {
			throw new Exception('Invalid socket resource. Connection may be expired.');
		}
        return @socket_read($this->resource, $length, $type);
    }
	
	public function readAll($length, $type = PHP_BINARY_READ) {
		$read = null;
		while($buffer = $this->read($length, $type)) {
			$read .= $buffer;
			if (strpos($buffer, "\n")!==false) {
				break;
			}
		}
		return $read;
	}
    
    public function readLine(callable $callback, $length = 8, $oneTime = false){
		Worker::AddTask(function($taskIndex) use ($callback, $length, $oneTime){
			if (!$this->isValidResource()) { // connection is closed.
				Worker::RemoveTask($taskIndex);
				return;
			}
			$ret = $this->read($length);
			if (strlen($ret)) {
				$this->readBuffer .=$ret;
				if (strpos($ret, "\n")!==false) {
					$line = $this->readBuffer;
					$this->readBuffer = null;
					if ($oneTime) {
						Worker::RemoveTask($taskIndex);
					}
					$callback(trim($line), $this);
				}
			}
		}, true, 'SOCKET - READ LINE TASK');
    }
    
    public function write(string $buffer, int $length = 0) {
		if (!$this->isValidResource()) {
			throw new Exception('Invalid socket resource. Connection may be expired.');
		}
        if ($length== 0) {
            $length = strlen($buffer);
        }
        return socket_write($this->resource, $buffer, $length);
    }
}