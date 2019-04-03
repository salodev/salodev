<?php

namespace salodev\Pli;
use salodev\Pli\Definitions\UserFunction;
use Exception;

class Scope {
	
	private $_functions = [];
	private $_classes   = [];
	private $_variables = [];
	private $_constants = [];
	
	public function storeVariables(array $variables) {
		$this->_variables = $variables;
	}
	
	public function storeVariable(string $name, $value) {
		$this->_variables[$name] = $value;
	}
	
	public function readVariable(string $name) {
		if (!isset($this->_variables[$name])) {
			throw new Exception("Call to undefined variable '{$name}'");
		}
		return $this->_variables[$name];
	}
	
	public function checkFunctionDefinition(string $name): bool {
		return isset($this->_functions[$name]);
	}
	
	public function defineFunction(UserFunction $functionDefinition) {
		$name = $functionDefinition->name;
		if (isset($this->_functions[$name])) {
			throw new Exception("Can not redefine funcion {$name}");
		}
		$this->_functions[$name] = $functionDefinition;
	}
	
	public function getFunctionDefinition(string $name): UserFunction {
		if (!isset($this->_functions[$name])) {
			throw new Exception("Call to undefined funtion {$name}");
		}
		return $this->_functions[$name];
	}
}