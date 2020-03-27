<?php

namespace salodev\Pli\CustomLang\Tokens;

class Code extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$matched = false;
		while($this->eatExpressions($evaluate)) {
			$matched = true;
		}
		return $matched;
	}
	
	public function eatExpressions(bool $evaluate): bool {
		foreach([
			Output::class,
			IfElse::class,
			FunctionDefinition::class,
			VariableDefinition::class,
			CommentBlock::class,
			CommentLine::class,
			FunctionCall::class,
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