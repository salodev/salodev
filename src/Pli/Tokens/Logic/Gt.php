<?php

namespace salodev\Pli\Tokens\Logic;
use salodev\Pli\Tokens\Token;
use salodev\Pli\Tokens\ValueExpression;
use salodev\Pli\Tokens\Math\Expression as MathExpression;
class Gt extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		if (!$this->eatString('>')) {
			return false;
		}
		
		if ($this->_value === null && $evaluate) {
			$this->raiseError('Missing for compare to.');
		}
		
		$t = $this->token(MathExpression::class);
		$t->eatExpected($evaluate);
		
		$value = $t->getValue();
		
		$this->_value = $this->_value > $value ? true : false;
		
		return true;
	}

}