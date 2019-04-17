<?php

namespace salodev\OpenSSL;

class CSR {
	
	private $_resource = null;
	private $_privateKey = null;
	
	public function __construct(array $dn, PrivateKey $pk) {
		foreach($dn as $key => $value) {
			if ($value === null) {
				throw new Error("Value for {$key} can not be null");
			}
		}
		$this->_privateKey = $pk;
		$pkResource = $pk->getResource();
		$ret = openssl_csr_new($dn, $pkResource);
		if ($ret === false) {
			throw new Error(openssl_error_string());
		}
		$this->_resource = $ret;
	}
	
	public function sign(int $days = 365, array $configargs = [], $serial = 0): X509 {
		$resource = openssl_csr_sign($this->_resource, null, $this->_privateKey->getResource(), $days, $configargs, $serial);
		if ($resource === false) {
			throw new Error(openssl_error_string());
		}
		
		$x509 = new X509;
		$x509->setResource($resource);
		return $x509;
	}
	
	public function export(): string {
		$out = '';
		openssl_csr_export($this->_resource, $out);
		return $out;
	}
}