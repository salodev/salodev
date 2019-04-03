<?php

namespace salodev\Pli\Tokens\Math;
use salodev\Pli\ComputingEngine;
use Exception;

class Integer extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();
		$value = null;
		while(($number = $this->_eatNumber($evaluate))!=='') {
			$value = "{$value}{$number}";
		}
		$this->_value = $value/1;
		
		return is_numeric($value);
	}
	
	public function getValue() {
		return $this->_value/1;
	}
	
	private function _eatNumber($evaluate): string {
		$numbers = ['0','1','2','3','4','5','6','7','8','9'];
		
		foreach($numbers as $number) {
			if ($this->eatString($number)) {
				return $number;
			}
		}
		return '';
	}
}