<?php

namespace salodev\Pli\CustomLang\Tokens;

class Condition extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		$t = $this->token(Boolean\Expression::class);
		$t->eatExpected($evaluate);
		$this->_value = $t->getValue();
		
		return true;
	}
	
}