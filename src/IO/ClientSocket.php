<?php

namespace salodev\IO;

use salodev\IO\Exceptions\Socket\ConnectionRefused;
use salodev\IO\Exceptions\Socket\ConnectionTimedOut;
use salodev\IO\Exceptions\Socket as SocketException;
use salodev\IO\Stream;

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
	
	public function open(array $options = []): Stream {
		$host = $options['host'] ?? null;
		$port = $options['port'] ?? null;
		$timeout = $options['timeout'] ?? 5;
		$this->_resource = @fsockopen($host, $port, $errNo, $errString, $timeout);
		if ($errNo) {
			if ($errNo == SOCKET_ECONNREFUSED) {
				throw new ConnectionRefused("Connection to {$host}:{$port} was refused");
			}
			if ($errNo == SOCKET_ETIMEDOUT) {
				throw new ConnectionTimedOut("Connection to {$host}:{$port} timed out");
			}
			throw new SocketException($errString . " code: {$errNo} " , $errNo);
		}
		return $this;
	}
	
	public function read(int $bytes = 256, int $type = 0): string {
		return fread($this->_resource, $bytes);
	}
	
	public function readAll($length, $type = PHP_BINARY_READ): string {
		$read = '';
		while($buffer = $this->read($length, $type)) {
			$read .= $buffer;
			if (strpos($buffer, "\n")!==false) {
				break;
			}
		}
		return $read;
	}
	
	public function readLine(int $length = 255): string {
		return fgets($this->_resource, $length);
	}
	
	public function writeAndRead(string $content): string {
		$this->setBlocking();
		$this->write($content . "\n");
		$buffer = '';
		while($read = $this->readLine()) {
			$buffer .= $read;
		}
		return $buffer;
	}
}