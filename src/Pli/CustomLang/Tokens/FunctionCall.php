<?php

namespace salodev\Pli\CustomLang\Tokens;

class FunctionCall extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		$co = $this->getCurrentOffset();
		$t = $this->token(DefinitionName::class);
				
		if (!$t->eat($evaluate)) {
			
			$this->setCurrentOffset($co);
			return false;
		}
		
		$functionName = $t->getValue();
		
		if (!$this->eatString('(')) {
			$this->setCurrentOffset($co);
			return false;
		}
		
		$t = $this->token(ParameterAssignmentsList::class);
		$t->eat($evaluate);
		$parameterAssignments = $t->getValue();
		
		
		$this->eatExpectedString(')');
				
		if ($evaluate) {
			$this->_value = $this->callFunction($functionName, $parameterAssignments);
		}
		
		return true;
	}
}