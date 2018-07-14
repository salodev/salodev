<?php
namespace salodev\IO;

class IO {
	/**
	 *
	 * @var StandardInput; 
	 */
	static private $_stdin = null;
	/**
	 *
	 * @var StandardOutput;
	 */
	static private $_stdout = null;
	/**
	 *
	 * @var StandardError;
	 */
	static private $_stderr = null;
	static private function _GetCreateStreams() {
		if (!self::$_stdin){
			self::$_stdin  = new StandardInput();
			self::$_stdout = new StandardOutput();
			self::$_stderr = new StandardError();
		}
	}
	static public function Read(callable $onRead, $length = 8, $readOneTime = true) {
		self::_GetCreateStreams();
		Worker::AddTask(function() use($onRead, $length){
			$read = self::$_stdin->read($length);
			if (strlen($read)) {
				$onRead($read);
			}
		}, !$readOneTime, 'STANDARD INPUT LISTENER');
		
	}
	static public function ReadLine(callable $onRead, $readOneTime = true) {
		self::_GetCreateStreams();
		self::$_stdin->readLineAsync($onRead, $readOneTime);
	}
	static public function Write($content) {
		self::_GetCreateStreams();
		self::$_stdout->write($content);
	}
	static public function WriteLine($content = '') {
		self::_GetCreateStreams();
		self::$_stdout->writeLine($content);
	}
	static public function WriteError($content) {
		self::_GetCreateStreams();
		self::$_stderr->write($content);
	}
	static public function WriteLineError($content = '') {
		self::_GetCreateStreams();
		self::$_stderr->writeLine($content);
	}
}