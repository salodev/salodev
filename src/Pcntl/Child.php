<?php

namespace salodev\Pcntl;
use salodev\Deferred;
use salodev\Promise;

class Child {
	
	private $_pid    = null;
	private $_status = null;
	
	public function __construct(int $pid) {
		$this->_pid = $pid;
	}
	
	public function wait(int $options = 0): int {
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
	
	public function getExitCode(): int {
		return pcntl_wexitstatus($this->_status);
	}
	
	public function getStopSignal(): int {
		return pcntl_wstopsig($this->_status);
	}
	
	public function getSignal(): int {
		return pcntl_wtermsig($this->_status);
	}
	
	public function sendSignal($sig): self {
		$return = posix_kill($this->_pid, $sig);
		if ($return === false) {
			$errorCode = posix_get_last_error();
			$errorMessage = posix_strerror($errorCode);
			throw new \Excetion($errorMessage, $errorCode);
		}
		return $this;
	}
	
	public function kill(): self {
		return $this->sendSignal(SIGKILL);
	}
	
	public function stop(): self {
		return $this->sendSignal(SIGINT);
	}
	
	public function isRunning(): bool {
		$pid = $this->wait(WNOHANG);
		return !($pid>0 && $this->exited());
	}
	
	public function waitForFinish(): Promise {
		$deferred = new Deferred;
		Worker::AddTask(function($taskIndex) use ($deferred) {
			if (!$this->isRunning()) {
				Worker::RemoveTask($taskIndex);
				if ($this->getExitCode() === 0) {
					$deferred->resolve($this);
				} else {
					$deferred->reject($this);
				}
			}
		}, true, "Waiting for PID: {$this->getPid()}");
		
		return $deferred->getPromise();
	}
}