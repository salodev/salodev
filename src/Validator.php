<?php
namespace salodev;

use Exception;

class Validator {
	private $_data;
	private $_fieldName = null;
	private $_value = null;
	private $_lastErrorMessage = null;
	
	static public function Create(array $data): self {
		return new self($data);
	}
	
	static public function Value($value): self {
		$v = new self([], 'Value', $value);
		return $v;
	}
	
	public function __construct(array $data, $fieldName = null, $value = null) {
		$this->_data = $data;
		$this->_fieldName = $fieldName;
		$this->_value = $value;
	}
	
	public function setValue($value): self {
		$this->_value = $value;
		return $this;
	}
	
	public function with($fieldName): self {
		if (!is_string($fieldName) || !strlen($fieldName)) {
			throw new Exception('Field name must be string');
		}
		$this->_fieldName = $fieldName;
		$this->_value = &$this->_data[$this->_fieldName];
		return $this;
	}
	
	public function required(string $errorMessage = null): self {
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
	
	public function filled(string $errorMessage = null): self {
		if ($this->_value === null || $this->_value === '') {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' value is empty.");
		}
		return $this;
	}
	
	public function numeric(string $errorMessage = null): self {
		if (!is_numeric($this->_value)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be a valid number.");
		}
		return $this;
	}
	
	public function positive(string $errorMessage = null): self {
		$this->numeric($errorMessage);
		if ($this->_value<0) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be positive number.");
		}
		return $this;
	}
	
	public function integer(string $errorMessage = null): self {
		$this->numeric($errorMessage);
		if (!is_integer($this->_value)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be integer number.");
		}
		return $this;
	}
	
	public function nonZero(string $errorMessage = null): self {
		$this->numeric($errorMessage);
		if ($this->_value===0) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must not be zero.");
		}
		return $this;
	}
	
	public function boolean(string $errorMessage = null): self {
		if (!is_bool($this->_value)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be a boolean value");
		}
		return $this;
	}
	
	public function booleanTrue(string $errorMessage = null): self {
		$this->boolean($errorMessage);
		if ($this->_value!==true) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be TRUE boolean value");
		}
		return $this;
	}
	
	public function booleanFalse(string $errorMessage = null): self {
		$this->boolean($errorMessage);
		if ($this->_value!==true) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be FALSE boolean value");
		}
		return $this;
	}
	
	public function date(string $errorMessage = null): self {
		@list($m, $d, $y) = explode('-', date('m-d-Y', strtotime($this->_value)));
		if (!checkdate($m/1, $d/1, $y/1)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be a valid date");
		}
		return $this;
	}
	
	public function dateDMY(string $errorMessage = null): self {
		@list($d, $m, $y) = explode('-', str_replace('/', '-', $this->_value));
		if (!checkdate($m/1, $d/1, $y/1)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be a valid date");
		}
		return $this;
	}
	
	public function dateYMD(string $errorMessage = null): self {
		@list($y, $m, $d) = explode('-', str_replace('/', '-', $this->_value));
		if (!checkdate($m/1, $d/1, $y/1)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be a valid date");
		}
		return $this;
	}
	
	public function lt($value, string $errorMessage = null): self {
		if (!($this->_value<$value)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be less than {$value}");
		}
		return $this;
	}
	
	public function lteq($value, string $errorMessage = null): self {
		if (!($this->_value<=$value)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be less than or equal to {$value}");
		}
		return $this;
	}
	
	public function gt($value, string $errorMessage = null): self {
		if (!($this->_value>$value)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be greather than {$value}");
		}
		return $this;
	}
	
	public function gteq($value, string $errorMessage = null): self {
		if (!($this->_value>=$value)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be greather than or equal to {$value}");
		}
		return $this;
	}
	
	public function allFilled(string $errorMessage = null): self {
		foreach($this->_data as $name => $value) {
			if ($value === null || $value === '') {
				throw new Exception($errorMessage ?? "'{$name}' is empty. Must be filled");
			}
		}
		return $this;
	}
	
	public function checkPresent(array $nameList, string $errorMessage): self {
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
	
	public function in(array $values, string $errorMessage = null): self {
		if (!in_array($this->_value, $values)) {
			$strValues = implode(', ', $values);
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be in {$strValues}.");
		}
		return $this;
	}
	
	public function notIn(array $values, string $errorMessage = null): self {
		if (in_array($this->_value, $values)) {
			$strValues = implode(', ', $values);
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' can not be one of {$strValues}.");
		}
		return $this;
	}
	
	public function minLength(int $length, string $errorMessage = null): self {
		if (!(strlen($this->_value)>=$length)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must have at least {$length} characters.");
		}
		return $this;
	}
	
	public function numberAndLetteres(string $errorMessage = null): self {
		if (!preg_match('/^[\pL0-9\ ]*$/iu', $this->_value)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be only numbers or letteres");
		}
		return $this;
	}
	
	public function min($value, string $errorMessage = null): self {
		$this->numeric($errorMessage);
		if ($this->_value < $value) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be greather than or equals to {$value}");
		}
		return $this;
	}
	
	public function max($value, string $errorMessage = null): self {
		$this->numeric($errorMessage);
		if ($this->_value > $value) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be less than or equals to {$value}");
		}
		return $this;
	}
	
	public function range($min, $max, string $errorMessage = null): self {
		$this->min($min, $errorMessage);
		$this->max($max, $errorMessage);
		return $this;
	}
	
	public function email(string $errorMessage = null): self {
		if (!filter_var($this->_value, FILTER_VALIDATE_EMAIL)) {
			throw new Exception($errorMessage ?? "'{$this->_fieldName}' must be a valid email address");
		}
		return $this;
	}
}