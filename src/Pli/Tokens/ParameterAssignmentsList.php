<?php

namespace salodev\Pli\Tokens;

class ParameterAssignmentsList extends Token {
	
	public function parse(bool $evaluate = false): bool {
		
		$this->_value = [];
		
		$this->eatSpaces();
		$t = $this->token(VariableDefinition::class);
		$t->storeInScope = false;
		if (!$t->eat($evaluate)) {
			return false;
		}
		
		$this->_value[$t->name] = $t->getValue();
		
		while($this->eatList($evaluate)){}
		
		return true;
	}
	
	public function eatList(bool $evaluate = false): bool {
		$this->eatSpaces();
		if (!$this->eatString(',')) {
			return false;
		}
		$t = $this->token(VariableDefinition::class);
		$t->storeInScope = false;
		$t->eatExpected($evaluate);
		$this->_value[$t->name] = $t->getValue();
		
		return true;
	}

}