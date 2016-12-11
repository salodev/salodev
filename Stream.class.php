<?php
namespace salodev;
/**
 * Para darle una interfaz a los flujos de datos.
 * @todo No estoy seguro si dividirlo en una abstracción para el servidor del flujo
 *       y otra para el consumidor. Por el momento se usa en ambas.
 */
abstract class Stream {
	private $_resource = null;
	public function __construct($spec, $mode = null) {
		if (is_resource($spec)) {
			$this->_resource = $spec;
		}
		if (is_string($spec)) {
			$this->open($spec, $mode);
			$this->setNonBlocking(); // by default.
		}
	}
	abstract public function open($spec, $mode = 'r');
	abstract public function read($bytes = 256);
	abstract public function readLine($length = 255);
	abstract public function write($content, $length = null);
	abstract public function close();
	abstract public function setBlocking();
	abstract public function setNonBlocking();
}