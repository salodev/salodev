<?php

namespace salodev\Pli\Tokens;
use salodev\Pli\ComputingEngine;
use Exception;

class CommentLine extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();		
		if ($this->eatString('#')) {
			$this->eatUntil("\n");
			return true;
		}
		return false;
	}
}