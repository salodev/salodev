<?php

namespace salodev\Pli\Tokens;
use salodev\Pli\ComputingEngine;
use salodev\Pli\Tokens\Token;
use Exception;

class DefinitionName extends LiteralChars {
	
	public function getChars(): array {
		return str_split('abcdefghijklmnopqrstuvwxyz_');
	}
}