<?php

namespace salodev\Pli\Tokens;
use salodev\Pli\ComputingEngine;
use salodev\Pli\Tokens\Token;
use Exception;

class UserInput extends Token{
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		if (!$this->eatString('$(')) {
			return false;
		}
		
		$message = '';
		$t = $this->token(ValueExpression::class);
		if ($t->eat($evaluate)) {
			$message = $t->getValue();
		}
		$this->eatExpectedString(')');		
		if ($evaluate) {
			$this->_value = readline($message);
		}
		return true;
	}
}