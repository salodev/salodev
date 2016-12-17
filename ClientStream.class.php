<?php
namespace salodev;

/**
 * Una abstracción para los consumidores de flujo.
 */
abstract class ClientStream extends Stream {
	public function open($spec, $mode = 'r') {
		$this->_resource = fopen($spec, $mode);
		return $this;
	}
	public function read($bytes = 256, $type = null) {
		return fread($this->_resource, $bytes);
	}
	public function readLine($length = 255) {
		return fgets($this->_resource, $length);
	}
	public function write($content, $length = null) {
		$length = $length===null ? strlen($content) : $length;
		fwrite($this->_resource, $content, $length);
		return $this;
	}
	public function close() {
		fclose($this->_resource);
		return $this;
	}
	public function setBlocking() {
		stream_set_blocking($this->_resource, true);
		return $this;
	}
	public function setNonBlocking() {
		stream_set_blocking($this->_resource, false);
		return $this;
	}
}