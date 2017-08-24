<?php
namespace salodev;

class StandardOutput extends ClientStream {
	/**
	 *
	 * @var Stream
	 */
	protected static $_stream = null;
	
	public function __construct(array $options = []) {
		$spec = 'php://stdout';
		$mode = 'w';
		if (self::$_stream instanceof StandardOutput) {
			throw new \Exception('singleton violation');
		}
		self::$_stream = $this;
		parent::__construct($spec, $mode);
	}
	
	public function writeLine($content) {
		return $this->write($content . "\n");
	}
}