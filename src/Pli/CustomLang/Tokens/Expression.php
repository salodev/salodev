<?php

namespace salodev\Pli\CustomLang\Tokens;

class Expression extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$matched = false;
		while($this->eatExpression($evaluate)) {
			$matched = true;
		}
		return $matched;
	}
	
	public function eatExpression(bool $evaluate): bool {
		$matched = false;
		foreach([
			Math\Integer::class,
			VariableRead::class,
			Math\Addition::class,
			Math\Subtraction::class,
			Math\Multiplication::class,
			Math\Division::class,
			ParenthesisExpression::class,
			UserInput::class,
		] as $tokenClass) {
			$t = $this->token($tokenClass);
			$t->setValue($this->_value);
			if ($t->eat($evaluate)) {
				$matched = true;
				$this->_value = $t->getValue();
			}
			if ($this->isOver()) {
				break;
			}
		}
		if (!$matched) {
			return false;
		}
		return true;
	}
}