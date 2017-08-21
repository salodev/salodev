<?php

namespace salodev;

class Child {
	
	private $_pid    = null;
	private $_status = null;
	
	public function __construct(int $pid) {
		$this->_pid = $pid;
	}
	
	public function wait(int $options = 0) {
		return pcntl_waitpid($this->_pid, $this->_status, $options);
	}
	
	public function getPid(): int {
		return $this->_pid;
	}
	
}