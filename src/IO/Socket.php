<?php

namespace salodev\IO;

use salodev\Worker;
use Exception;

class Socket extends Stream {
    
	private $readBuffer = null;
	private $writtenBytes = 0;
	
	public function open(array $options = []): Stream {
		return $this;
	}
    
    public function create($domain = AF_INET, $type = SOCK_STREAM, $protocol = 0): self {
        $resource = socket_create($domain, $type , $protocol);
        if (!is_resource($resource)) {
            throw new Exception($this->getErrorText(), $this->getLastError());
        }
        $this->_resource = $resource;
		$this->setNonBlocking();
		return $this;
    }
    
    public function getLastError(): int {
        return socket_last_error($this->_resource);
    }
    
    public function getErrorText() {
        return socket_strerror($this->getLastError());
    }
    
    public function setNonBlocking(): Stream {
		$this->validateResource();
        socket_set_nonblock($this->_resource);
		return $this;
    }
    
    public function setBlocking(): Stream {
		$this->validateResource();
        socket_set_block($this->_resource);
		return $this;
    }
    
    public function setOption($name, $val): self {
		$this->validateResource();
        $ret = socket_set_option($this->_resource, SOL_SOCKET, $name, $val);
		if ($ret === false) {
			throw new Exception($this->getErrorText(), $this->getLastError());
		}
		return $this;
    }
	
	public function setOptionReuseAddressOnBind(bool $value = true): self {
		return $this->setOption(SO_REUSEADDR, $value ? 1: 0);
	}
    
    public function bind($address, $port): self {
		$this->validateResource();
		if (@!socket_bind($this->_resource, $address, $port)) {
			throw new \Exception($this->getErrorText(), $this->getLastError());
		}
		return $this;
    }
    
    public function listen($backlog = 0): self {
		$this->validateResource();
		if (!socket_listen($this->_resource, $backlog)) {
			throw new \Exception($this->getErrorText(), $this->getLastError());
		}
		return $this;
    }
    
	/**
	 * 
	 * @return \salodev\IO\Socket | bool
	 */
    public function accept() {
		$this->validateResource();
        $newResource = socket_accept($this->_resource);
        if (!$newResource) {
            return false;
        }
        $newSocket = new Socket(['resource' => $newResource]);
		$newSocket->setNonBlocking();
        return $newSocket;
    }
    
    public function close(): Stream {
		$this->validateResource();
        socket_close($this->_resource);
		return $this;
    }
    
    public function read(int $length = 256, int $type = PHP_BINARY_READ): string {
		$this->validateResource();
        return @socket_read($this->_resource, $length, $type);
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
    
    public function write(string $buffer, int $length = 0): Stream {
		$this->validateResource();
        if ($length== 0) {
            $length = strlen($buffer);
        }
        $this->writtenBytes = socket_write($this->_resource, $buffer, $length);
		return $this;
    }
}