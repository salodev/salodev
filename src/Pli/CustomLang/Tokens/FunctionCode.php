<?php

namespace salodev\Pli\CustomLang\Tokens;

class FunctionCode extends Token {
	
	public function parse(bool $evaluate = false): bool {
		while($this->eatExpressions($evaluate)) {}
		
		$this->eatSpaces();
		$this->eatExpectedString('return');
		$this->eatSpaces();
		$t = $this->token(Math\Expression::class);
		$t->eatExpected($evaluate);
		
		$this->_value = $t->getValue();
		
		return true;
	}
	
	public function eatExpressions(bool $evaluate): bool {		
		foreach([
			VariableDefinition::class,
			CommentBlock::class,
			CommentLine::class,
			FunctionCall::class,
			Output::class,
			IfElse::class,
		] as $tokenClass) {
			$t = $this->token($tokenClass);
			$t->setValue($this->_value);
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