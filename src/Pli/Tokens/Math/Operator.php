<?php

namespace salodev\Pli\Tokens\Math;
use salodev\Pli\Tokens\ValueExpression;
use salodev\Pli\Tokens\Logic\Gt;
use salodev\Pli\Tokens\Logic\Lt;
use salodev\Pli\Tokens\Logic\GtEq;
use salodev\Pli\Tokens\Logic\LtEq;
use salodev\Pli\Tokens\Logic\Eq;

class Operator extends Token {
	
	public function parse(bool $evaluate = false): bool {		
		foreach([
			Addition::class,
			Division::class,
			Multiplication::class,
			Subtraction::class,
			Gt::class,
			GtEq::class,
			Lt::class,
			LtEq::class,
			Eq::class,
		] as $tokenClass) {
			$t = $this->token($tokenClass);
			$t->setValue($this->_value);
			if ($t->eat($evaluate)) {
				$this->_value = $t->getValue();
				return true;
			}
		}
		
		return false;
	}

}

