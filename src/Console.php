<?php

namespace salodev;

class Console {
	
	static public function GetParam($name, $defaultValue = null) {
		$ret = getopt('',["{$name}:"]);
		return $ret[$name] ?? $defaultValue;
	}
}