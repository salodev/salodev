<?php

namespace salodev\OpenSSL;
use salodev\FileSystem\File;

class Pkcs7 {
	
	private $_file = null;
	
	public function __construct(File $file) {
		$this->_file = $file;
	}
	
	public function sign(File $output, Certificate $cert, PrivateKey $key, array $headers = []): File {
		$this->_file->validateRead();
		$cert->getFile()->validateRead();
		$key->getFile()->validateRead();
		
		$ret = openssl_pkcs7_sign(
				$this->_file->getFullPath(), 
				$output->getFullPath(), 
				[$key->getFullPath(), $key->getPassPhrase()], 
				$cert->getFullPath(), $headers);
		
		if ($ret === false) {
			throw new Error("Error signing file {$this->_file->getSecureFileName()}");
		}
		
		return $output;
	}
	
	static public function decrypt() {
		
	}
}