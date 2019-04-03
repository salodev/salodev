<?php
namespace salodev\IO;
use salodev\IO\Exceptions\StreamWaitError;

/**
 * Para darle una interfaz a los flujos de datos.
 * @todo No estoy seguro si dividirlo en una abstracción para el servidor del flujo
 *       y otra para el consumidor. Por el momento se usa en ambas.
 */
abstract class Stream {
	protected $_resource = null;
	static private $_readResources   = [];
	static private $_writeResources  = [];
	static private $_exceptResources = [];
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
	abstract public function open(array $options = []): self;
	abstract public function read(int $bytes = 256, int $type = 0): string;
	abstract public function write(string $content, int $length = 0): self;
	abstract public function close(): self;
	abstract public function setBlocking(): self;
	abstract public function setNonBlocking(): self;
	
	public function AddRead(): int {
		$this->_readResources[] = $this->_resource;
		return key($this->_readResources);
	}
	
	public function RemoveRead(int $key) {
		$this->_readResources[] = $this->_resource;
		return key($this->_readResources);
	}
	
	static public function WaitForActivity(int $sec, int $usec): int {
		$ret = stream_select($this->_readResources, $this->_writeResources, $this->_exceptResources, $sec, $usec);
		if ($ret === false) {
			throw new StreamWaitError('Error wating for stream activity');
		}
		
		return $ret;
	}
}