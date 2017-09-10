<?php
namespace salodev;
/**
 * Para darle una interfaz a los flujos de datos.
 * @todo No estoy seguro si dividirlo en una abstracción para el servidor del flujo
 *       y otra para el consumidor. Por el momento se usa en ambas.
 */
abstract class Stream {
	protected $_resource = null;
	public function __construct(array $options = []) {
		$resource = $options['resource'] ?? null;
		if (is_resource($resource)) {
			$this->_resource = $resource;
		}
		// if (is_string($resource)) {
			$this->open($options);
			if (($options['nonBlocking']??false) || !($options['blocking']??true)) {
				$this->setNonBlocking(); // by default.
			}
		// }
	}
	abstract public function open(array $options = []);
	abstract public function read(int $bytes = 256, int $type = 0);
	abstract public function write(string $content, int $length = 0);
	abstract public function close();
	abstract public function setBlocking();
	abstract public function setNonBlocking();
}