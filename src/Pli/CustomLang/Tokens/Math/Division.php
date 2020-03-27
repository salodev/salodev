<?php

namespace salodev\Pli\CustomLang\Tokens\Math;

class Division extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		if ($this->eatString('/')) {
			$t = $this->token(Expression::class);
			$t->eatExpected($evaluate);
			if ($evaluate) {
				$this->_value /= $t->getValue();
			}
			return true;
		}
		return false;
	}
}