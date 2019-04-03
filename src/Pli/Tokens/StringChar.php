<?php

namespace salodev\Pli\Tokens;

class StringChar extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		if (!$this->eatString('"')) {			
			return false;
		}
		$closed = false;
		while($char = $this->_eatTokens($evaluate)) {			
			if ($char === '"') {
				$closed = true;
				break;
			}
			if ($char==='\"') {
				$char = '"';
			}
			if ($char==='\n') {
				$char = "\n";
			}
			$this->_value .= $char;
		}
		if (!$closed) {
			$this->raiseError('Missing close string.');
		}
		return true;
	}
	
	private function _eatTokens(bool $evaluate = false): string {
		if ($this->eatString('\"')) {
			return '\"';
		}
		if ($this->eatString('\n')) {
			return '\n';
		}
		return $this->eatAny();
	}

}