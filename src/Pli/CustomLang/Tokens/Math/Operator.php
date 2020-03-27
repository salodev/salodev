<?php

namespace salodev\Pli\CustomLang\Tokens\Math;

use salodev\Pli\CustomLang\Tokens\Logic\Gt;
use salodev\Pli\CustomLang\Tokens\Logic\Lt;
use salodev\Pli\CustomLang\Tokens\Logic\GtEq;
use salodev\Pli\CustomLang\Tokens\Logic\LtEq;
use salodev\Pli\CustomLang\Tokens\Logic\Eq;

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

