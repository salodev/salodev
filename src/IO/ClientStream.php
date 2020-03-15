<?php
namespace salodev\IO;

/**
 * Una abstracción para los consumidores de flujo.
 */
abstract class ClientStream extends Stream {
	
	public function open(array $options = []): Stream {
		$spec = $options['spec'] ?? null;
		$mode = $options['mode'] ?? null;
		$this->_resource = fopen($spec, $mode);
		return $this;
	}
	
	public function read(int $bytes = 256, int $type = 0): string {
		return fread($this->_resource, $bytes);
	}
	
	public function readLine(int $length = 255): string {
		return fgets($this->_resource, $length);
	}
	
	public function write(string $content, int $length = 0): Stream {
		$length = $length == 0 ? strlen($content) : $length;
		fwrite($this->_resource, $content, $length);
		return $this;
	}
	
	public function writeAndRead(string $content): string {
		$this->write($content);
		return $this->read();
	}
	
	public function close(): Stream {
		fclose($this->_resource);
		return $this;
	}
	
	public function setBlocking(): Stream {
		stream_set_blocking($this->_resource, true);
		return $this;
	}
	
	public function setNonBlocking(): Stream {
		stream_set_blocking($this->_resource, false);
		return $this;
	}
}