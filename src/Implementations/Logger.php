<?php

namespace salodev\Implementations;

use salodev\Debug\ExceptionDumper;
use salodev\Pcntl\Thread;

/**
 * Description of Logger
 *
 * @author salomon
 */
trait Logger {	
	
	static protected $_logHandler = null;
	
	static protected $_logLevelsEnabled = ['all'];
	
	static public function SetLogLevels(array $values): void {
		static::$_logLevelsEnabled = $values;
	}
	
	static public function SetLogHandler(callable $logHandler): void {
		static::$_logHandler = $logHandler;
	}
	
	static public function SetLogDisabled() {
		static::SetLogLevels([]);
	}
	
	static public function Log(string $message, string $level = 'debug'): void {
		if (in_array($level, static::$_logLevelsEnabled) || in_array('all', static::$_logLevelsEnabled)) {
			$dateTime = date('Y-m-d H:i:s');
			$pid = Thread::GetPid();
			if (static::$_logHandler === null) {
				echo sprintf("[%s][%d][%s] %s\n", $dateTime, $pid, $level, $message);
			} else {
				$fn = static::$_logHandler;
				$fn($message, $level, $pid, $dateTime);
			}
		}
	}
	
	static public function LogError(string $message): void {
		static::Log($message, 'error');
	}
	
	static public function LogDebug(string $message): void {
		static::Log($message, 'debug');
	}
	
	static public function LogInfo(string $message): void {
		static::Log($message, 'info');
	}
	
	
	static public function LogException($e) {
		static::LogError(ExceptionDumper::DumpFromThrowable($e));
	}
}
