<?php

namespace salodev\Pli\Definitions;
use salodev\Pli\Scope;

abstract class NativeFunction extends UserFunction {
	
	abstract public function call(array $parameters = [], Scope $globalScope = null);
}