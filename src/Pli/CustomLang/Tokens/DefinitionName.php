<?php

namespace salodev\Pli\CustomLang\Tokens;

class DefinitionName extends LiteralChars {
	
	public function getChars(): array {
		return str_split('abcdefghijklmnopqrstuvwxyz_');
	}
}