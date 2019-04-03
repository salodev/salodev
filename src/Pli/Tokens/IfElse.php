<?php

namespace salodev\Pli\Tokens;

class IfElse extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		if (!$this->eatString('if')) {
			return false;
		}

		$t = $this->token(Condition::class);
		if (!$t->eat($evaluate)) {
			return false;
		}
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