<?php

namespace salodev\Pli\CustomLang\Tokens\Boolean;

use salodev\Pli\Token;

class Expression extends Token {
	
	public function parse(bool $evaluate = false): bool {
		foreach([
			Equal::class,
			Distinct::class,
		] as $tokenClass) {
			$t = $this->token($tokenClass);
			if ($t->eat($evaluate)) {
				$this->_value = $t->getValue();
				return true;
			}
			if ($this->isOver()) {
				break;
			}
		}
		
		return false;
	}

}
