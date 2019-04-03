<?php

namespace salodev\Pli\Tokens\Math;
use salodev\Pli\Tokens\Token as AbstractToken;

abstract class Token extends AbstractToken {
	
	protected $_value = 0;
	
	public function getValue() {
		return $this->_value;
	}
}