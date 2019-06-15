<?php

namespace salodev\OpenSSL;
use salodev\FileSystem\File;

class PrivateKey {
	
	private $_file = null;
	private $_passphrase = null;
	private $_resource = null;
	
	public function __construct(File $file = null, string $passphrase = null, string $privateKeyContent = null) {
		$this->_file = $file;
		$this->_passphrase = $passphrase;
		if ($privateKeyContent === null) {
			$this->generate();
		} else {
			$test = openssl_pkey_get_private($privateKeyContent, $passphrase);
			if ($test===false) {
				throw new Error('Invalid private key format');
			}
			$this->_resource = $test;
		}
	}
	
	static public function FromString(string $privateKeyContent, string $passphrase = null): self {
		return new self(null, $passphrase, $privateKeyContent);
	}
	
	public function getFile(): File {
		return $this->_file;
	}
	
	public function getFilePath(): string {
		return $this->_file->getFullPath();
	}
	
	public function getPassPhrase(): string {
		return $this->_passphrase;
	}
	
	public function getResource() {
		return $this->_resource;
	}
	
	public function generate(): self {
		$this->_resource = openssl_pkey_new();
		return $this;
	}
	
	public function export(): string {
		$out = '';
		openssl_pkey_export($this->_resource, $out, $this->_passphrase);
		return $out;
	}
}