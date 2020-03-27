<?php

namespace salodev\Pli\CustomLang\Definitions\NativeFunctions;

use salodev\Pli\CustomLang\Tokens\FunctionCode;
use salodev\Pli\NativeFunction;
use salodev\Pli\Scope;

class FunctionIF extends NativeFunction {
	
	public function call(array $parameters = [], Scope $globalScope = null) {
		if (count($parameters)<3) {
			throw new \Exception('Function expects 3 parameters');
		}
		return $parameters[0] ? $parameters[1] : $parameters[2];
	}

	public function getCodeTokenClassName(): string {
		return FunctionCode::class;
	}

}