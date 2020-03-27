<?php

namespace salodev\Pli\CustomLang\Tokens;

use Exception;

class ParenthesisExpression extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();		
		if ($this->eatString('(')) {
			$t = $this->token(Math\Expression::class);
			$t->eatExpected($evaluate);
			$this->_value = $t->getValue();
			if (!$this->eatString(')')) {
				throw new Exception('Expected CLOSED_PARENTHESIS');
			}
			
			return true;
		}
		return false;
	}
}