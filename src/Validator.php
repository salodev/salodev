<?php
namespace salodev;

use Exception;

class Validator {
	private $_data;
	private $_fieldName = null;
	private $_value = null;
	private $_lastErrorMessage = null;
	
	static public function Create(array $data) {
		return new self($data);
	}
	
	static public function Value($value) {
		$v = new self([], 'Value', $value);
		return $v;
	}
	
	public function __construct(array $data, $fieldName = null, $value = null) {
		$this->_data = $data;
		$this->_fieldName = $fieldName;
		$this->_value = $value;
	}
	
	public function with($fieldName) {
		if (!is_string($fieldName) || !strlen($fieldName)) {
			throw new Exception('Field name must be string');
		}
		$this->_fieldName = $fieldName;
		$this->_value = &$this->_data[$this->_fieldName];
		return $this;
	}
	
	public function required($errorMessage = null) {
		if (count($this->_data)) {
			if (!array_key_exists($this->_fieldName, $this->_data)) {
				throw new Exception($errorMessage ?? "'{$this->_fieldName}' does not exist.");
			}
		}
		if ($this->_value === null || $this->_value === '') {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' value is empty.");
		}
		return $this;
	}
	
	public function filled($errorMessage = null) {
		if ($this->_value === null || $this->_value === '') {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' value is empty.");
		}
		return $this;
	}
	
	public function numeric($errorMessage = null) {
		if (!is_numeric($this->_value)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be a valid number.");
		}
		return $this;
	}
	
	public function positive($errorMessage = null) {
		$this->numeric($errorMessage);
		if ($this->_value<0) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be positive number.");
		}
		return $this;
	}
	
	public function integer($errorMessage = null) {
		$this->numeric($errorMessage);
		if (!is_integer($this->_value)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be integer number.");
		}
		return $this;
	}
	
	public function nonZero($errorMessage = null) {
		$this->numeric($errorMessage);
		if ($this->_value===0) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must not be zero.");
		}
		return $this;
	}
	
	public function boolean($errorMessage = null) {
		if (!is_bool($this->_value)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be a boolean value");
		}
		return $this;
	}
	
	public function booleanTrue($errorMessage = null) {
		$this->boolean($errorMessage);
		if ($this->_value!==true) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be TRUE boolean value");
		}
		return $this;
	}
	
	public function booleanFalse($errorMessage = null) {
		$this->boolean($errorMessage);
		if ($this->_value!==true) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be FALSE boolean value");
		}
		return $this;
	}
	
	public function date($errorMessage = null) {
		@list($m, $d, $y) = explode('-', date('m-d-Y', strtotime($this->_value)));
		if (!checkdate($m/1, $d/1, $y/1)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be a valid date");
		}
		return $this;
	}
	
	public function dateDMY($errorMessage = null) {
		@list($d, $m, $y) = explode('-', str_replace('/', '-', $this->_value));
		if (!checkdate($m/1, $d/1, $y/1)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be a valid date");
		}
		return $this;
	}
	
	public function dateYMD($errorMessage = null) {
		@list($y, $m, $d) = explode('-', str_replace('/', '-', $this->_value));
		if (!checkdate($m/1, $d/1, $y/1)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be a valid date");
		}
		return $this;
	}
	
	public function lt($value, $errorMessage = null) {
		if (!($this->_value<$value)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be less than {$value}");
		}
		return $this;
	}
	
	public function lteq($value, $errorMessage = null) {
		if (!($this->_value<=$value)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be less than or equal to {$value}");
		}
		return $this;
	}
	
	public function gt($value, $errorMessage = null) {
		if (!($this->_value>$value)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be greather than {$value}");
		}
		return $this;
	}
	
	public function gteq($value, $errorMessage = null) {
		if (!($this->_value>=$value)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be greather than or equal to {$value}");
		}
		return $this;
	}
	
	public function allFilled($errorMessage = null) {
		foreach($this->_data as $name => $value) {
			if ($value === null || $value === '') {
				throw new Exception($errorMessage ?? "'{$name}' is empty. Must be filled");
			}
		}
		return $this;
	}
	
	public function checkPresent(array $nameList, $errorMessage) {
		foreach($nameList as $fieldName) {
			if (!array_key_exists($fieldName, $this->_data)) {
				throw new Exception($errorMessage ?? "'{$fieldName}' is not present.");
			}
			$value = $this->_data[$fieldName];
			if ($value === null || $value === '') {
				throw new Exception($errorMessage ?? "'{$fieldName}' is empty. Must be filled.");
			}
		}
		return $this;
	}
	
	public function in(array $values, $errorMessage = null) {
		if (!in_array($this->_value, $values)) {
			$strValues = implode(', ', $values);
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be in {$strValues}.");
		}
		return $this;
	}
	
	public function notIn(array $values, $errorMessage = null) {
		if (in_array($this->_value, $values)) {
			$strValues = implode(', ', $values);
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' can not be one of {$strValues}.");
		}
		return $this;
	}
	
	public function minLength(int $length, $errorMessage = null) {
		if (!(strlen($this->_value)>=$length)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must have at least {$length} characters.");
		}
		return $this;
	}
	
	public function numberAndLetteres($errorMessage = null) {
		if (!preg_match('/^[\pL0-9\ ]*$/iu', $this->_value)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be only numbers or letteres");
		}
		return $this;
	}
	
	public function min($value, $errorMessage = null) {
		$this->numeric($errorMessage);
		if ($this->_value < $value) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be greather than or equals to {$value}");
		}
	}
	
	public function max($value, $errorMessage = null) {
		$this->numeric($errorMessage);
		if ($this->_value > $value) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be less than or equals to {$value}");
		}
	}
	
	public function range($min, $max, $errorMessage = null) {
		$this->min($min, $errorMessage);
		$this->max($max, $errorMessage);
		return $this;
	}
}