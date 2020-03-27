<?php

namespace salodev\Pli\CustomLang\Tokens\Math;

use salodev\Pli\CustomLang\Tokens\ValueExpression;

class Expression extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		$t = $this->token(ValueExpression::class);
		
		if (!$t->eat($evaluate)) {
			return false;
		}
		$t2 = $this->token(Operator::class);
		$t2->setValue($t->getValue());
		
		while($t2->eat($evaluate)) {
			/*$t2 = $this->token(Operator::class);
			$t2->setValue($t2->getValue());*/
		}
		$this->setValue($t2->getValue());
		return true;
	}
}