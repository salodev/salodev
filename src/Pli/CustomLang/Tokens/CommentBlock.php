<?php

namespace salodev\Pli\CustomLang\Tokens;

class CommentBlock extends Token {
	
	public function parse(bool $evaluate = false): bool {
		$this->eatSpaces();		
		if ($this->eatString('/*')) {
			$this->eatUntil('*/');
			return true;
		}
		return false;
	}
}