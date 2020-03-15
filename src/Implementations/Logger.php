<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace salodev\Implementations;

/**
 * Description of Logger
 *
 * @author salomon
 */
trait Logger {	
	
	static protected $_logHandler = null;
	static protected $_logStatus = true;
	
	static public function SetLogStatus(bool $value): void {
		static::$_logStatus = $value;
	}
	
	static public function SetLogHandler(callable $logHandler): void {
		static::$_logHandler = $logHandler;
	}
	
	static public function Log(string $message): void {
		if (static::$_logStatus === true) {
			if (static::$_logHandler === null) {
				echo $message;
			} else {
				$fn = static::$_logHandler;
				$fn($message);
			}
		}
	}
}
