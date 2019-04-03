<?php

namespace salodev\OpenSSL;
use salodev\FileSystem\File;

class PrivateKey {
	
	private $_file = null;
	private $_passphrase;
	
	public function __construct(File $file, string $passphrase = null) {
		$this->_file = $file;
		$this->_passphrase = $passphrase;
	}
	
	public function getFile(): File {
		return $this->_file;
	}
	
	public function getFilePath(): string {
		$this->_file->getFullPath();
	}
	
	public function getPassPhrase(): string {
		$this->_passphrase;
	}
}