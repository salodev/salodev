<?php
namespace salodev\SSH;

use Exception;

class SSH {
	
	private $_resource;
	
	public function __construct(string $host, int $port, array $methods = [], array $callbacks = []) {
		if (!$this->_resource = ssh2_connect($host, $port, $methods, $callbacks)) {
			throw new Exception('Error trying connect to ssh host');
		}
		return $this;
	}
	
	public function getFingerPring($flags = SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX): string {
		return ssh2_fingerprint($this->_resource, $flags);
	}
	
	public function checkFingerPrint(string $knownHostString, $flags = SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX): bool {
		return $knownHostString === $this->getFingerPring($flags);
	}
	
	public function authUserPassword(string $user, string $password): self {
		if (!ssh2_auth_password($this->_resource, $user, $password)) {
			throw new Exception('Authentication by userpass failed');
		}
		return $this;
	}
	
	/**
	 * @return SSH\ShellStream Interactive shell stream
	 */
	public function interact(array $options = array()): ShellStream {
		return ShellStream::Create($this->_resource, $options);
	}
	
	/**
	 * @return SSH\ShellStream Command interactive stream
	 */
	public function exec($command, array $options = array()): CommandStream {
		return CommandStream::Create($this->_resource, $command, $options);
	}
}