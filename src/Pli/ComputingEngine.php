<?php

namespace salodev\Pli;
use salodev\Pli\Definitions\UserFunction;
use salodev\Pli\Tokens\Token;
use salodev\Pli\Tokens\Code;
use Exception;

class ComputingEngine {
	
	private $_input       = null;
	private $_offset      = 0;
	private $_tokenClass  = null;
	private $_globalScope = null;
	private $_localScope  = null;
	
	public $value = null;
	
	public function __construct(string $tokenClass = Code::class, Scope $globalScope = null) {
		$this->_tokenClass = $tokenClass;
		if ($globalScope === null) {
			$this->_globalScope = new Scope;
		} else {
			$this->_globalScope = $globalScope;
		}
		$this->_localScope = new Scope;
		
	}
	
	public function readChars(int $count = 1): string {
		return substr($this->_input, $this->_offset, $count);
	}
	
	public function eatString(string $string): bool {
		$len = strlen($string);
		if ($this->_offset + $len > strlen($this->_input)) {
			return false;
		}
		$found = $this->readChars($len);
		if ($found === $string) {
			return $this->_increase(strlen($string));
		}
		return false;
	}
	
	public function eatExpectedString(string $string): bool {
		if (!$this->eatString($string)) {
			$this->raiseError("Expected: '{$string}'");
			return false;
		}
		return true;
	}
	
	public function eatSpaces():bool {
		$chars = "\n\r\t ";
		$matched = false;
		foreach(str_split($chars) as $char) {
			while($this->eatString($char)){
				$matched = true;
			}
		}
		return $matched;
	}
	
	public function eatUntil(string $string): bool {
		while(!$this->eatString($string)) {
			$this->_increase(1);
			if ($this->isOver()) {
				return false;
			}
		}
		return true;
	}
	
	public function eatAny(): string {
		$found = $this->readChars(1);
		$this->_increase(1);
		return $found;
	}
	
	private function _increase(int $offset): bool {
		$this->_offset += $offset;
		return true;
	}
	
	public function isOver(): bool {
		return $this->_offset >= strlen($this->_input);
	}
	
	public function getOffset(): int {
		return $this->_offset;
	}
	
	public function getLine(): int {
		return substr_count($this->_input, "\n", 0, $this->_offset)+1;
	}
	
	public function getInput(): string {
		return $this->_input;
	}
	
	public function raiseError(string $text) {
		throw new Exception("{$text} at line {$this->getLine()}, offset {$this->getOffset()}.");
	}
	
	public function evaluate(string $input, array $variables = []) {
		$this->_input = $input;
		$this->_localScope->storeVariables($variables);
		$this->run(false);
		$this->_offset = 0;
		return $this->run(true);
	}
	
	public function run(bool $evaluate = true) {
		while($this->_offset < strlen($this->_input) && $this->runTokens($evaluate)) {}
		
		$this->eatSpaces();
		
		if (!$this->isOver()) {
			$this->raiseError("Inesperado: '{$this->readChars(1)}'");
		}
		
		return $this->value;
	}
	
	public function runTokens(bool $evaluate = true): bool {
		$tokenClass = $this->_tokenClass;
		if (empty($tokenClass)) {
			throw new Exception('Missing define default token ckass');
		}
		
		$t = new $tokenClass($this);		
		
		if (!$t->eat($evaluate)) {
			return false;
		}
		$this->value = $t->getValue();
		return true;
	}
	
	public function callFunction(string $name, array $parameters = []) {
		try {
			$fnDef = $this->_globalScope->getFunctionDefinition($name);
		} catch (Exception $e) {
			$this->raiseError($e->getMessage());
		}
		$value = $fnDef->call($parameters, $this->_globalScope);
		return $value;
	}
	
	public function setOffset(int $offset) {
		$this->_offset = $offset;
	}
	
	public function storeVariable(string $name, $value) {
		return $this->_localScope->storeVariable($name, $value);
	}
	
	public function readVariable(string $name) {
		try {
			return $this->_localScope->readVariable($name);
		} catch (Exception $e) {
			$this->raiseError($e->getMessage());
		}
	}
	
	public function defineFunction(UserFunction $functionDefinition) {
		try {
			$this->_globalScope->defineFunction($functionDefinition);
		} catch (Exception $e) {
			$this->raiseError($e->getMessage());
		}
	}
	
	public function showCurrentParsing($windowSize = 100) {
		$string = $this->getInput();
		$start      = max([0, $this->getOffset()-$windowSize]);
		$leftWindow = $this->getOffset() - $start;
		
		echo substr($string, $start, $leftWindow);
		echo "<>";
		echo substr($string, $this->getOffset(), $windowSize);
		echo "\n\n";
	}
}