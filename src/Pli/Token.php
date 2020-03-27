<?php

namespace salodev\Pli;

use Exception;

abstract class Token {
	
	protected $_value = null;
	
	/**
	 *
	 * @var \salodev\Pli\ComputingEngine;
	 */
	protected $_computingEngine;
	
	public function __construct(ComputingEngine $computingEngine) {
		$this->_computingEngine = $computingEngine;
	}
	
	public function eat(bool $evaluate = false): bool {
		$offset = $this->getCurrentOffset();
		$return = $this->parse($evaluate);
		if ($return !== true) {
			$this->setCurrentOffset($offset);
		}
		return $return;
	}
	
	public function eatExpected(bool $evaluate = false): bool {
		if (!$this->eat($evaluate)) {
			$this->raiseError('Expected: ' . get_class($this));
		}
		
		return true;
	}
	
	public function eatString(string $string, bool $matchCase = true): bool {
		return $this->_computingEngine->eatString($string, $matchCase);
	}
	
	public function eatExpectedString(string $string, bool $matchCase = true): bool {
		return $this->_computingEngine->eatExpectedString($string, $matchCase);
	}
	
	public function eatSpaces(): bool {
		return $this->_computingEngine->eatSpaces();
	}
	public function eatUntil(string $string, &$foundString): bool {
		return $this->_computingEngine->eatUntil($string, $foundString);
	}
	
	public function eatWord($delimiter = ' '): string {
		return $this->_computingEngine->eatWord($delimiter);
	}
	
	public function eatExpectedWord(string $word): bool {
		return $this->_computingEngine->eatExpectedWord($word);
	}
	
	public function eatChars(string $chars): string {
		return $this->_computingEngine->eatChars($chars);
	}
	
	public function eatAny(): string {
		return $this->_computingEngine->eatAny();
	}
	
	abstract public function parse(bool $evaluate = false): bool;
	
	public function getValue() {
		return $this->_value;
	}
	
	public function setValue($value) {
		$this->_value = $value;
	}
	
	public function raiseError(string $message) {
		$this->_computingEngine->raiseError(get_class($this) . ": " . $message);
	}
	
	public function token(string $tokenClass): Token {
		$tokenInsance = new $tokenClass($this->_computingEngine);
		if (!($tokenInsance instanceof Token)) {
			$class = get_class($tokenInsance);
			throw new Exception("{$class} instance must be an Token implementation");
		}
		return $tokenInsance;
	}
	
	public function isOver(): bool {
		return $this->_computingEngine->isOver();
	}
	
	public function getCurrentOffset(): int {
		return $this->_computingEngine->getOffset();
	}
	public function setCurrentOffset(int $offset) {
		return $this->_computingEngine->setOffset($offset);
	}
	
	public function callFunction(string $name, array $parameters = []) {
		return $this->_computingEngine->callFunction($name, $parameters);
	}
	
	public function storeVariable(string $name, $value) {
		return $this->_computingEngine->storeVariable($name, $value);
	}
	
	public function readVariable(string $name) {
		return $this->_computingEngine->readVariable($name);
	}
	
}