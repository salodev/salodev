<?php
namespace salodev;

class StandardInput extends ClientStream{
	/**
	 *
	 * @var Stream
	 */
	protected static $_stream = null;
	/**
	 * 
	 * @return Stream;
	 */
	protected static function _GetCreateStream() {
		if (self::$_stream == null) {
			self::$_stream = new StandardInput();
			self::$_stream->setNonBlocking();
		}
		return self::$_stream;
	}
	
	public function __construct($spec = 'php://stdin', $mode = 'r') {
		parent::__construct($spec, $mode);
	}
	
	static public function SetNonBlocking() {
		$stream = self::_GetCreateStream();
		$stream->setNonBlocking();
	}
	static public function SetBlocking() {
		$stream = self::_GetCreateStream();
		$stream->setBlocking();
	}
	static public function Read() {
		$stream = self::_GetCreateStream();
		return $stream->read();
	}
	static public function ReadLineAsync($fn, $readOneTime = true) {
		$read = null;
		Worker::AddTask(function($taskIndex) use (&$read, $fn, $readOneTime){
			$stream = self::_GetCreateStream();
			$buffer = $stream->read();
			$read .= $buffer;
			if (strpos($buffer, "\n")!==false || strpos($buffer, "\r")!==false) {
				$tmp = $read;
				$read = null;
				$fn($tmp);
				if ($readOneTime) {
					Worker::RemoveTask($taskIndex);
				}
			}
		});
	}
}