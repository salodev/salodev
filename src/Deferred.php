<?php
namespace salodev;
class Deferred {
	
	const NO_RESULT = 0;
	const DONE = 1;
	const FAIL = 2;
	
	private $_doneFns = array();
	private $_failFns = array();
	private $_alwaysFns = array();
	private $_state = Deferred::NO_RESULT;
	private $_result = null;
	
	public function resolve($result = null): self {
		$this->_state = Deferred::DONE;
		$this->_result = null;
		
		foreach($this->_doneFns as $fn) {
			$fn($result);
		}
		
		foreach($this->_alwaysFns as $fn) {
			$fn($result);
		}
		
		return $this;
	}
	
	public function reject($result = null): self {
		$this->_state = Deferred::FAIL;
		$this->_result = null;
		
		foreach($this->_failFns as $fn) {
			$fn($result);
		}
		
		foreach($this->_alwaysFns as $fn) {
			$fn($result);
		}
		
		return $this;
	}
	
	public function done(callable $callback): self {
		if ($this->_state==Deferred::DONE) {
			$callback($this->_result);
		} else {
			$this->_doneFns[] = $callback;
		}
		return $this;
	}
	
	public function fail(callable $callback): self {
		if ($this->_state==Deferred::FAIL) {
			$callback($this->_result);
		} else {
			$this->_failFns[] = $callback;
		}
		return $this;		
	}
	
	public function always(callable $callback): self {
		if ($this->_state==Deferred::DONE || $this->_state==Deferred::FAIL) {
			$callback($this->_result);
		} else {
			$this->_alwaysFns[] = $callback;
		}
		return $this;
	}
	
	public function getPromise(): Promise {
		return new Promise($this);
	}
}