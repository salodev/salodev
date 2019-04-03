<?php

namespace salodev\Pli\Tokens;

class Output extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		if (!$this->eatString('¡(')) {
			return false;
		}
		
		$t = $this->token(ValueExpression::class);
		$t->eatExpected($evaluate);
		
		$this->eatExpectedString(')');
		if ($evaluate) {
			$this->setValue($t->getValue());
			echo $t->getValue();
		}
		return true;
	}

}