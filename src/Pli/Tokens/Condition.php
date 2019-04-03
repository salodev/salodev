<?php

namespace salodev\Pli\Tokens;

class Condition extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		if (!$this->eatString('(')) {
			return false;
		}
		$t = $this->token(Math\Expression::class);
		$t->eatExpected($evaluate);
		$this->_value = $t->getValue();
		
		$this->eatSpaces();
		$this->eatExpectedString(')');
		
		return true;
	}
	
}