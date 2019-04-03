<?php

namespace salodev\Pli\Tokens;

class VariableRead extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		$t = $this->token(DefinitionName::class);
		
		if (!$t->eat($evaluate)) {
			return false;
		}
		
		if ($evaluate) {
			$name = $t->getValue();
			$this->setValue($this->readVariable($name));
		}
		
		return true;
	}
}