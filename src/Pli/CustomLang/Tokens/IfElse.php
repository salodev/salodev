<?php

namespace salodev\Pli\CustomLang\Tokens;

class IfElse extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		if (!$this->eatString('if')) {
			return false;
		}
		$this->eatSpaces();
		if (!$this->eatString('(')) {
			return false;
		}

		$this->eatSpaces();
		$t = $this->token(Condition::class);
		$t->eatExpected($evaluate);		
		
		
		$this->eatSpaces();
		$this->eatExpectedString(')');
		$this->eatSpaces();
		
		$value = $t->getValue();
		$t2 = $this->token(CodeBlock::class);
		$t2->eatExpected($evaluate && $value);
		
		$this->eatSpaces();
		
		if (!$this->eatString('else')) {
			return true;
		}
		
		$this->eatSpaces();
		
		$t2 = $this->token(CodeBlock::class);
		
		$this->eatSpaces();
		$t2->eatExpected($evaluate && !$value);
		
		return true;
	}

}