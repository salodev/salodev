<?php

class StandardOutput extends ClientStream {
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
			self::$_stream = new StandardOutput();
			self::$_stream->setNonBlocking();
		}
		return self::$_stream;
	}
	
	public function __construct($spec = 'php://stdout', $mode = 'w') {
		parent::__construct($spec, $mode);
	}
	
	public function WriteLine($content) {
		return self::Write($content . "\n");
	}
	
	public function Write($content) {
		return self::_GetCreateStream()->write($content);
	}
}