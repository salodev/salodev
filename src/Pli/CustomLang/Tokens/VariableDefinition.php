<?php

namespace salodev\Pli\CustomLang\Tokens;

class VariableDefinition extends Token {
	
	public $name = null;
	public $storeInScope = true;
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		
		// $co = $this->getCurrentOffset();
		
		$t = $this->token(DefinitionName::class);
		
		if (!$t->eat($evaluate)) {
			// $this->setCurrentOffset($co);
			return false;
		}
		$name = $t->getValue();
		$this->name = $name;
		
		$this->eatSpaces();
		
		if (!$this->eatString('=')) {
			// $this->setCurrentOffset($co);
			return false;
		}
		
		$this->eatSpaces();
		
		$t2 = $this->token(Math\Expression::class);
		$t2->eatExpected($evaluate);
		
		if ($evaluate) {
			$value = $t2->getValue();
			$this->setValue($value);
			if ($this->storeInScope) {
				$this->storeVariable($name, $value);
			}
		}
		return true;
	}

}