<?php

namespace salodev\Pli\Tokens;

class CodeBlock extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		if (!$this->eatString('{')) {
			return false;
		}
		$this->eatSpaces();
		$t = $this->token(Code::class);
		$t->eatExpected($evaluate);
		
		$this->eatSpaces();
		$this->eatExpectedString('}');
		
		return true;
	}
	
}