<?php

namespace salodev\Pli\Tokens\Math;
use salodev\Pli\ComputingEngine;
use salodev\Pli\Tokens\ValueExpression;
use Exception;

class Addition extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		if ($this->eatString('+')) {
			$t = $this->token(ValueExpression::class);
			$t->eatExpected($evaluate);
			if ($evaluate) {
				$this->_value += $t->getValue();
			}
			return true;
		}
		return false;
	}
}