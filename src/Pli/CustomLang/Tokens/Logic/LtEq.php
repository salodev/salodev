<?php

namespace salodev\Pli\CustomLang\Tokens\Logic;

use salodev\Pli\Token;
use salodev\Pli\CustomLang\Tokens\Math\Expression;

class LtEq extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		if (!$this->eatString('<=')) {
			return false;
		}
		
		if ($this->_value === null && $evaluate) {
			$this->raiseError('Missing for compare to.');
		}
		
		$t = $this->token(Expression::class);
		$t->eatExpected($evaluate);
		
		$value = $t->getValue();
		
		$this->_value = $this->_value <= $value ? true : false;
		
		return true;
	}

}