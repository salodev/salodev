<?php

namespace salodev\Debug\CodeTracer;


class Expectation {
	
	private $class    = null;
	private $method   = null;
	private $type     = null;
	private $callback = null;
	private $times    = 1;
	private $count    = 0;
	private $reached  = false;
	private $closure  = null;
	private $since    = null;
	
	static private $instances = [];
	
	static public function Create(string $className, string $methodName): self {
		return (new self)
			->setClass($className)
			->setMethod($methodName)
			->start();
	}
	
	static public function GetLast(): self {
		if (!count(self::$instances)) {
			throw new \Exception('No Expectatiosn found');
		}
		return self::$instances[count(self::$instances)-1];
	}
	
	static public function GetAll(): array {
		return self::$instances;
	}
	
	public function __construct() {
		self::$instances[] = $this;
	}
	
	public function start() {
		$this->closure = function() {			
			$wr = $this->wasReached();			
			if ($this->wasReached()) {
				return;
			}
			
			$rs = debug_backtrace();
			
			
			if (empty($rs[1])) { return; }
			$call = $rs[1];
			
			$reached = true;
			
			if ($this->class) {
				if (isset($call['class'])) {
					$reached = $call['class'   ] == $this->class;
				} else {
					$reached = false;
				}
			}
			
			if ($this->method && $reached) {
				if (isset($call['function'])) {
					$reached = $call['function'] == $this->method;
				} else {
					$reached = false;
				}
			}
			
			if ($this->type && $reached) {
				if (isset($call['type'])) {
					$reached = $call['type'] == $this->type;
				} else {
					$reached = false;
				}
			}
			
			if ($this->callback && $reached) {
				$reached = ($this->callback)(new Call($call));
			}
			
			if ($reached) {
				$this->_reached();
				return;
			}
		};
		register_tick_function($this->closure);
		return $this;
	}
	
	public function stop(): self {
		unregister_tick_function($this->closure);
		return $this;
	}
	
	public function setAnalyzerCallback(callable $fn): self {
		$this->callback = $fn;
		return $this;
	}
	
	public function setClass(string $value): self {
		$this->class = $value;
		return $this;
	}
	
	public function getClass(): string {
		return $this->class;
	}
	
	public function setMethod(string $value): self {
		$this->method = $value;
		return $this;
	}
	
	public function getMethod(): string {
		return $this->method;
	}
	
	public function setInstanceType(string $value): self {
		$this->type = '->';
		return $this;
	}
	
	public function setStaticType(string $value): self {
		$this->type = '::';
		return $this;
	}
	
	public function removeCallType(string $value): self {
		$this->type = '';
		return $this;
	}
	
	public function getType(): string {
		return $this->type;
	}
	
	public function setTimes(int $value): self {
		$this->times = $value;
		return $this;
	}
	
	public function getTimes(): int {
		return $this->times;
	}
	
	private function _reached(): self {
		$this->count++;
		$this->reached = $this->count>=$this->times;
		if ($this->since) {
			// echo "entra...\n";
			//$this->since->start();
		}
		return $this;
	}
	
	public function since(): self {
		$this->since = new self;
		return $this->since;
	}
	
	public function getCount(): int {
		return $this->count;
	}
	
	public function wasReached(): bool {
		// unregister_tick_function($this->closure);
		return $this->reached;
	}
	
	public function __destruct() {
		$this->stop();
	}
}