<?php

namespace salodev\Pli\Tokens\Math;
use salodev\Pli\ComputingEngine;
use Exception;

class Multiplication extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		if ($this->eatString('*')) {
			$t = $this->token(Expression::class);
			$t->eatExpected($evaluate);
			if ($evaluate) {
				$this->_value *= $t->getValue();
			}
			return true;
		}
		return false;
	}
}