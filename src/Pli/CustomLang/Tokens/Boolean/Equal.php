<?php

namespace salodev\Pli\CustomLang\Tokens\Boolean;

use salodev\Pli\Token;
use salodev\Pli\CustomLang\Tokens\ValueExpression;

class Equal extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		$t1 = $this->token(ValueExpression::class);
		
		if (!$t1->eat($evaluate)) {
			return false;
		}
	
		$this->eatSpaces();
		
		if (!$this->eatString('==')) {
			return false;
		}
		
		$this->eatSpaces();
		
		$t2 = $this->token(ValueExpression::class);
		
		if (!$t2->eatExpected($evaluate)) {
			return false;
		}		
		
		if ($evaluate) {
			$this->_value = $t1->getValue() === $t2->getValue();
		}
		
		return true;
	}

}
