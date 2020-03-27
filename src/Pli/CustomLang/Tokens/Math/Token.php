<?php

namespace salodev\Pli\CustomLang\Tokens\Math;

use salodev\Pli\Token as AbstractToken;

abstract class Token extends AbstractToken {
	
	protected $_value = 0;
	
	public function getValue() {
		return $this->_value;
	}
}