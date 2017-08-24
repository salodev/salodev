<?php
namespace salodev;
use SSH\ShellStream;
use SSH\CommandStream;
class SSH {
	private $_resource;
	public function __construct($host, $port, array $methods = array(), array $callbacks = array()) {
		if (!$this->_resource = ssh2_connect($host, $port, $methods, $callbacks)) {
			throw new \Exception('Error trying connect to ssh host');
		};
		return $this;
	}
	public function getFingerPring($flags = SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX) {
		return ssh2_fingerprint($this->_resource, $flags);
	}
	public function checkFingerPrint($knownHostString, $flags = SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX) {
		return $knownHostString === $this->getFingerPring($flags);
	}
	public function authUserPassword($user, $password) {
		if (!ssh2_auth_password($this->_resource, $user, $password)) {
			throw new \Exception('Authentication by userpass failed');
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