<?php
namespace salodev\IO;

use salodev\Worker;

class StandardInput extends ClientStream {
	/**
	 *
	 * @var Stream
	 */
	protected static $_stream = null;
	
	/**
	 *
	 * @var string
	 */
	protected $_readBuffer = null;
	
	static public function Create(array $options = []): self {
		$spec = 'php://stdin';
		$mode = 'r';
		
		$instance = new self(array_merge([
			'spec' => $spec,
			'mode' => $mode,
		], $options));
		
		return $instance;
	}
	
	static public function CreateFromResource($resource): self {
		return new self([
			'resource' => $resource,
		]);
	}
	
	public function readLineAsync(callable $fn, bool $readOneTime = true): void {
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
					$tmp = str_replace("\n", '', $tmp);
					$tmp = str_replace("\r", '', $tmp);
					$fn($tmp);
				}
			}
		}, true, 'READ LINE FROM STANDARD INPUT');
	}
	
	public function close(): Stream {
		static::$_stream = null;
		parent::close();
	}
}