<?php

namespace salodev\Pli\Tokens;

class ParametersList extends Token {
	
	public function parse(bool $evaluate = false): bool {
		
		$this->_value = [];
		
		$this->eatSpaces();
		$t = $this->token(DefinitionName::class);
		if (!$t->eat($evaluate)) {
			return false;
		}
		$this->_value[] = $t->getValue();
		
		while($this->eatList($evaluate)){}
		
		return true;
	}
	
	public function eatList(bool $evaluate = false): bool {
		$this->eatSpaces();
		if (!$this->eatString(',')) {
			return false;
		}
		$t = $this->token(DefinitionName::class);
		$t->eatExpected($evaluate);
		
		$this->_value[] = $t->getValue();
		
		return true;
	}

}