<?php

class Deferred {
	const NO_RESULT = 0;
	const DONE = 1;
	const FAIL = 2;
	private $_doneFns = array();
	private $_failFns = array();
	private $_alwaysFns = array();
	private $_state = Deferred::NO_RESULT;
	private $_result = null;
	public function resolve($result = null) {
		$this->_state = Deferred::DONE;
		$this->_result = null;
		foreach($this->_doneFns as $fn) {
			$fn($result);
		}
		foreach($this->_alwaysFns as $fn) {
			$fn($result);
		}
	}
	
	public function reject($result) {
		$this->_state = Deferred::FAIL;
		$this->_result = null;
		foreach($this->_failFns as $fn) {
			$fn($result);
		}
		foreach($this->_alwaysFns as $fn) {
			$fn($result);
		}
	}
	
	public function done($callback) {
		if ($this->_state==Deferred::DONE) {
			$callback($this->_result);
		} else {
			$this->_doneFns[] = $callback;
		}
		return $this;
	}
	
	public function fail($callback) {
		if ($this->_state==Deferred::FAIL) {
			$callback($this->_result);
		} else {
			$this->_failFns[] = $callback;
		}
		return $this;		
	}
	
	public function always($callback) {
		if ($this->_state==Deferred::DONE || $this->_state==Deferred::FAIL) {
			$callback($this->_result);
		} else {
			$this->_alwaysFns[] = $callback;
		}
		return $this;
	}
}