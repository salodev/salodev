<?php
namespace salodev;

/**
 * Una abstracción para los consumidores de flujo.
 */
abstract class ClientStream extends Stream {
	
	public function open(array $options = []) {
		$spec = $options['spec'] ?? null;
		$mode = $options['mode'] ?? null;
		$this->_resource = fopen($spec, $mode);
		return $this;
	}
	
	public function read(int $bytes = 256, $type = null): string {
		return fread($this->_resource, $bytes);
	}
	
	public function readLine(int $length = 255): string {
		return fgets($this->_resource, $length);
	}
	
	public function write(string $content, int $length = null): self {
		$length = $length===null ? strlen($content) : $length;
		fwrite($this->_resource, $content, $length);
		return $this;
	}
	
	public function writeAndRead(string $content): string {
		$this->write($content);
		return $this->read();
	}
	
	public function close(): self {
		fclose($this->_resource);
		return $this;
	}
	
	public function setBlocking(): self {
		stream_set_blocking($this->_resource, true);
		return $this;
	}
	
	public function setNonBlocking(): self {
		stream_set_blocking($this->_resource, false);
		return $this;
	}
}