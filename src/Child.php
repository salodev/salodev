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
	
	public function exited(): bool {
		return pcntl_wifexited($this->_status);
	}
	
	public function stopped(): bool {
		return pcntl_wifstopped($this->_status);
	}
	
	public function signaled(): bool {
		return pcntl_wifsignaled($this->_status);
	}
	
	public function getExitStatus(): int {
		return pcntl_wexitstatus($this->_status);
	}
	
	public function getStopSignal(): int {
		return pcntl_wstopsig($this->_status);
	}
	
	public function getSignal(): int {
		return pcntl_wtermsig($this->_status);
	}
	
	public function sendSignal($sig): bool {
		return posix_kill($this->_pid, $sig);
	}
	
	public function kill(): bool {
		return $this->sendSignal(SIGKILL);
	}
}