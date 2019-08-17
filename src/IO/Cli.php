<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace salodev\IO;

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
}
