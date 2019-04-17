<?php

namespace salodev\Pli\Tokens;

class ValueExpression extends Token {
	
	public function parse(bool $evaluate = false): bool {
		
		foreach([
			UserInput::class,
			FunctionCall::class,
			ParenthesisExpression::class,
			VariableRead::class,
			Math\Number::class,
			StringChar::class,
			Output::class,
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