<?php

namespace salodev\Pli\Tokens;
use salodev\Pli\ComputingEngine;
use salodev\Pli\Tokens\Token;
use salodev\Pli\Definitions\UserFunction as UserFunctionDefinition;
use Exception;

class FunctionDefinition extends Token {
	
	public function parse(bool $evaluate = false): bool {
		
		$this->eatSpaces();
		if (!$this->eatString('function')) {
			return false;
		}
		
		if (!$this->eatSpaces()) {
			return false;
		}
		$t = $this->token(DefinitionName::class);
		if (!$t->eat($evaluate)) {
			return false;
		}
		$functionName = $t->getValue();
		
		$this->eatSpaces();
		
		$this->eatExpectedString('(');
		
		$tpl = $this->token(ParametersList::class);
		$tpl->eat();
		$parametersList = $tpl->getValue();
				
		$this->eatExpectedString(')');
		
		$this->eatSpaces();
		
		$this->eatExpectedString('{');
		
		$fnDefinition = new UserFunctionDefinition($this->_computingEngine);
		$fnDefinition->name = $functionName;
		$fnDefinition->offsetEvaluateStart = $this->getCurrentOffset();
		
		$t2 = $this->token(FunctionCode::class);
		$t2->eat(false);
		
		$this->_value = $t2->getValue();
				
		$fnDefinition->offsetEvaluateEnd = $this->getCurrentOffset();
		$fnDefinition->parametersList = $parametersList;
		
		$this->eatSpaces();
		
		$this->eatExpectedString('}');
		$this->eatSpaces();
		
		if ($evaluate) {
			$this->_computingEngine->defineFunction($fnDefinition);
		}
		
		return true;
	}
}