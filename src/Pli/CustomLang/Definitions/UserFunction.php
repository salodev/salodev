<?php


namespace salodev\Pli\CustomLang\Definitions;

use salodev\Pli\UserFunction as Base;
use salodev\Pli\CustomLang\Tokens\FunctionCode;

class UserFunction extends Base {
	
	public function getCodeTokenClassName(): string {
		return FunctionCode::class;
	}

}
