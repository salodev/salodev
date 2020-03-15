<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace salodev\IO;

use salodev\IO\Exceptions\Cli\Exception;

/**
 * Description of Cli
 *
 * @author salomon
 */
class Cli {
	
	//put your code here
	
	/**
	 * 
	 * @param string $name
	 * @param string $defaultValue
	 * @return string
	 */
	static public function getParam(string $name, string $defaultValue = null): string {
		$ret = getopt('',["{$name}:"]);
		return $ret[$name] ?? $defaultValue;
	}
	
	static public function getRawCommand(): string {
		return implode(' ', $argv);
	}
	
	static public function getRawParams(): string {
		global $argv;
		array_shift($argv);
		return implode(' ', $argv);
	}
	
	static public function getRawParamsArray(int $index = null): array {
		global $argv;
		$array = array_shift($argv);
		if ($index !== null) {
			if (!isset($array[$index])) {
				throw new Exception('Param offset not found');
			}
			return $array[$index];
		}
		return $array;
	}
}
