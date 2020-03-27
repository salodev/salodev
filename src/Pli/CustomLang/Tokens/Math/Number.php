<?php

namespace salodev\Pli\CustomLang\Tokens\Math;

class Number extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		
		$t = $this->token(Integer::class);
		if (!$t->eat($evaluate)) {
			return false;
		}
		
		$value = $t->getValue();
		
		if ($this->eatString('.')) {
			$t = $this->token(Integer::class);
			$t->eatExpected($evaluate);
			$decimal = $t->getValue();
			$value = "{$value}.{$decimal}";
		}
		
		$this->_value = $value/1;
		
		return is_numeric($value);
	}
}