<?php

namespace salodev\Pli\Definitions;
use salodev\Pli\ComputingEngine;
use salodev\Pli\Tokens\FunctionCode;
use salodev\Pli\Scope;

class UserFunction {
	
	/* @var string */ public $name                = null;	
	/* @var int    */ public $offsetStart         = 0;
	/* @var int    */ public $offsetEnd           = 0;
	/* @var int    */ public $offsetEvaluateStart = 0;
	/* @var int    */ public $offsetEvaluateEnd   = 0;	
	/* @var string */ public $fileName            = null;
	/* @var array  */ public $parametersList      = [];
	
	/**
	 *
	 * @var \salodev\Pli\ComputingEngine 
	 */
	protected $_compute = null;
	
	public function __construct(ComputingEngine $ce) {
		$this->_compute = $ce;
	}
	
	public function call(array $parameters = [], Scope $globalScope = null) {
		
		$start   = $this->offsetEvaluateStart;
		$end     = $this->offsetEvaluateEnd;
		$length  = $end-$start;
		
		$input   = substr($this->_compute->getInput(), $start, $length);
		$compute = new ComputingEngine(FunctionCode::class, $globalScope);
		
		$this->checkParameters($parameters);
		
		$value   = $compute->evaluate($input, $parameters);
		
		return $value;
	}
	
	public function checkParameters(array $parameters = []): bool {
		foreach($this->parametersList as $name) {
			if (!array_key_exists($name, $parameters)) {
				$this->_compute->raiseError("Missing reiqured parameter '{$name}' for function '{$this->name}()'");
			}
		}
		return true;
	}
}