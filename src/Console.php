<?php

namespace salodev;

/**
 * @deprecated Use IO\Cli instead
 */
class Console {
	
	static public function GetParam($name, $defaultValue = null) {
		$ret = getopt('',["{$name}:"]);
		return $ret[$name] ?? $defaultValue;
	}
}