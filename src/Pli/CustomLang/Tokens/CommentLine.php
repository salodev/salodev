<?php

namespace salodev\Pli\CustomLang\Tokens;

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