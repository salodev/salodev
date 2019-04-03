<?php

namespace salodev\Pli\Tokens;
use salodev\Pli\ComputingEngine;
use salodev\Pli\Tokens\Token;
use Exception;

abstract class LiteralChars extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		$value = null;
		while(($char = $this->_eatChars($evaluate))!=='') {
			$value = "{$value}{$char}";
		}
		$this->_value = $value;
		
		return strlen($value)>0;
	}
	
	private function _eatChars(bool $evaluate = false): string {
		$chars = $this->getChars();
		
		foreach($chars as $char) {
			if ($this->eatString($char)) {
				return $char;
			}
		}
		return '';
	}
	
	abstract public function getChars(): array;
}