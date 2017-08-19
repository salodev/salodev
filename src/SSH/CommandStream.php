<?php
namespace salodev\SSH;
class CommandStream extends \salodev\ClientStream {
	public function __construct($connection, $command, array $options = array()) {
		$options = array_merge(array(
			'pty' => true,
			'env' => array(),
			'width' => 80,
			'height' => 25,
			'mesaureType' => SSH2_TERM_UNIT_CHARS,
		), $options);
		if (!$this->_resource = ssh2_exec($connection, $command,
			$options['pty'],
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