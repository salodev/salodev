<?php

namespace salodev\OpenSSL;

class X509 {
	
	private $_resource = null;
	
	public function __construct() {
		
	}
	
	public function setResource($resource) {
		if (get_resource_type($resource) != 'OpenSSL X.509') {
			throw new Error('Invalid resource type');
		}
		$this->_resource = $resource;
	}
	
	public function getResource() {
		return $this->_resource;
	}
	
	public function export(): string {
		$output = '';
		openssl_x509_export($this->_resource, $output);
		return $output;
	}
}