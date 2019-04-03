<?php
namespace salodev\SSH;

use salodev\IO\ClientStream;
use Exception;

class CommandStream extends ClientStream {
	
	static public function Create($resource, array $options = []): self {
		$options['connection'] = $resource;
		return new self($options);
	}
	
	public function __construct(array $options = []) {
		$connection = $options['connection'] ?? null;
		$command    = $options['command'   ] ?? null;
		$options = array_merge(array(
			'pty' => true,
			'env' => array(),
			'width' => 80,
			'height' => 25,
			'measureType' => SSH2_TERM_UNIT_CHARS,
		), $options);
		if (!$this->_resource = ssh2_exec($connection, $command,
			$options['pty'],
			$options['env'],
			$options['width'],
			$options['height'],
			$options['measureType']
		)) {
			throw new Exception('Interactive shell failed');
		}
		$this->setNonBlocking();
	}
}