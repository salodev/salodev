<?php

namespace salodev\Pli;

abstract class UserFunction {
	
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
	
	abstract public function getCodeTokenClassName(): string;
	
	public function call(array $parameters = [], Scope $globalScope = null) {
		
		$start   = $this->offsetEvaluateStart;
		$end     = $this->offsetEvaluateEnd;
		$length  = $end-$start;
		
		$input   = substr($this->_compute->getInput(), $start, $length);
		$compute = new ComputingEngine($this->getCodeTokenClassName(), $globalScope);
		
		$this->checkParameters($parameters);
		
		$namedValues = [];
		foreach($this->parametersList as $i => $name) {
			if (!array_key_exists($i, $parameters)) {
				$ipos = $i+1;
				$compute->raiseError("Missing parameter #{$ipos} for {$this->name} function call.");
			}
			$namedValues[$name] = $parameters[$i];
		}
		
		$value   = $compute->evaluate($input, $parameters);
		
		return $value;
	}
	
	public function checkParameters(array $parameters = []): bool {
		$parametersRequiredCount = count($this->parametersList);
		$parametersPassedCount   = count($parameters);
		if ($parametersPassedCount<$parametersRequiredCount) {
			$this->_compute->raiseError("'{$this->name}' funciton requires {$parametersRequiredCount}, but {$parametersPassedCount} passed.");
		}
		return true;
	}
}