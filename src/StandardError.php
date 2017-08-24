<?php
namespace salodev;

class StandardError extends ClientStream {
	/**
	 *
	 * @var Stream
	 */
	protected static $_stream = null;
	
	public function __construct(array $options = []) {
		$spec = 'php://stderr';
		$mode = 'w';
		if (self::$_stream instanceof StandardError) {
			throw new \Exception('singleton violation');
		}
		self::$_stream = $this;
		parent::__construct($spec, $mode);
	}
	
	public function writeLine($content) {
		return $this->write($content . "\n");
	}
}