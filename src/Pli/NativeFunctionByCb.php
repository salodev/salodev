<?php

namespace salodev\Pli;

use ReflectionFunction, Exception;

abstract class NativeFunctionByCb extends NativeFunction {
	
	private $_callback = null;
	
	public function setCallback(callable $callback) {
		$this->_callback = $callback;
	}
	
	public function call(array $parameters = [], Scope $globalScope = null) {
		
		if (empty($this->_callback)) {
			throw new Exception('No callback for this function!');
		}
		
		$rf = new ReflectionFunction($this->_callback);
		
		$params = $rf->getParameters();
		$startedOptionals = false;
		$requiredParamsCount = 0;
		foreach($params as $param) {
			
			if ($startedOptionals && !$param->isOptional()) {
				throw new Exception('After first optional parameter, followings must be optional');
			}
			
			if ($param->isOptional()) {
				$startedOptionals = true;
			} else {
				$requiredParamsCount++;
			}
		}
		
		$passedParamsCount = count($parameters);
		if ($passedParamsCount < $requiredParamsCount) {
			$this->_compute->raiseError("Too few parameters: {$this->name} expects {$requiredParamsCount}, but {$passedParamsCount} passed");
		}
		
		return call_user_func_array($this->_callback, $parameters);
	}
}