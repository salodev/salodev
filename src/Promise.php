<?php

namespace salodev;

class Promise {
	
	private $_deferred = null;
	
	public function __construct(Deferred $deferred) {
		$this->_deferred = $deferred;
	}
	
	public function done(callable $callback): self {
		$this->_deferred->done($callback);
		return self;
	}
	
	public function fail(callable $callback): self {
		$this->_deferred->fail($callback);
		return self;
	}
	
	public function always(callable $callback): self {
		$this->_deferred->always($callback);
		return self;
	}
}
