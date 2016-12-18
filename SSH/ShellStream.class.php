<?php
namespace salodev\SSH;
class ShellStream extends \salodev\ClientStream {
	public function __construct($connection, array $options = array()) {
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
			throw new \Exception('Interactive shell failed');
		}
		$this->setNonBlocking();
	}
}