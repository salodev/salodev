<?php
namespace salodev;

/**
 * Una abstracción para los consumidores de flujo.
 */
class ClientSocket extends ClientStream {
	
	static public function Create(string $host, int $port, float $timeout = 5): self {
		return new self([
			'host' => $host,
			'port' => $port,
			'timeout' => $timeout,
		]);
	}
	
	public function open(array $options = []) {
		$host = $options['host'] ?? null;
		$port = $options['port'] ?? null;
		$timeout = $options['timeout'] ?? 5;
		$this->_resource = @fsockopen($host, $port, $errNo, $errString, $timeout);
		if ($errNo) {
			throw new \Exception($errString, $errNo);
		}
		return $this;
	}
	public function read($bytes = 256, $type = null) {
		return fread($this->_resource, $bytes);
	}
	
	public function readAll($length, $type = PHP_BINARY_READ) {
		$read = null;
		while($buffer = $this->read($length, $type)) {
			$read .= $buffer;
			if (strpos($buffer, "\n")!==false) {
				break;
			}
		}
		return $read;
	}
	
	public function readLine($length = 255) {
		return fgets($this->_resource, $length);
	}
	public function write($content, $length = null) {
		$length = $length===null ? strlen($content) : $length;
		// echo "escribiendo '$content' ({$length})\n";
		fwrite($this->_resource, $content, $length);
		return $this;
	}
	public function writeAndRead($content) {
		$this->setBlocking();
		$this->write($content . "\n");
		$buffer = '';
		while($read = $this->readLine()) {
			$buffer .= $read;
		}
		return $buffer;
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