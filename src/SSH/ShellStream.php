<?php
namespace salodev\SSH;

use salodev\ClientStream;
use Exception;

class ShellStream extends ClientStream {
	
	static public function Create($resource, array $options = []): self {
		$options['connection'] = $resource;
		return new self($options);
	}
	
	public function __construct(array $options = []) {
		$connection = $options['connection'] ?? null;
		$options = array_merge(array(
			'terminalType' => 'vanilla',
			'env' => array(),
			'width' => 80,
			'height' => 25,
			'mesaureType' => SSH2_TERM_UNIT_CHARS,
		), $options);
		if (!$this->_resource = ssh2_shell($connection, 
			$options['terminalType'],
			$options['env'],
			$options['width'],
			$options['height'],
			$options['mesaureType']
		)) {
			throw new Exception('Interactive shell failed');
		}
		$this->setNonBlocking();
	}
}