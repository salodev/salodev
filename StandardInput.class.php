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
	 * @var string
	 */
	protected static $_readBuffer = null;
	
	public function __construct($spec = 'php://stdin', $mode = 'r') {
		if (self::$_stream instanceof StandardInput) {
			throw new Exception('singleton violation');
		}
		self::$_stream = $this;
		parent::__construct($spec, $mode);
	}
	public function readLine(callable $fn, $readOneTime = true) {
		Worker::AddTask(function($taskIndex) use ($fn, $readOneTime){
			$ret = $this->read();
			if (strlen($ret)) {
				$this->_readBuffer .= $ret;
				if (strpos($ret, "\n")!==false || strpos($ret, "\r")!==false) {
					$tmp = $this->_readBuffer;
					$this->_readBuffer = null;
					if ($readOneTime) {
						Worker::RemoveTask($taskIndex);
					}
					$fn($tmp);
				}
			}
		});
	}
}