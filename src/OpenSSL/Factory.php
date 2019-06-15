<?php

namespace salodev\OpenSSL;

use salodev\FileSystem\File;

class Factory {
	
	/**
	 * This methods returns an array
	 * @param array Contains the privatekey, csr and certificate
	 * 
	 */
	static public function GenerateSelfSignedCertificate(
			string $countryName = null, 
			string $stateOrProvinceName = null, 
			string $localityName = null, 
			string $organizationName = null, 
			string $organizationalUnitName = null, 
			string $commonName = null, 
			string $emailAddress = null,
			string $privateKeyContent = null
	): array {
		if ($privateKeyContent !== null) {
			$privateKey = PrivateKey::FromString($privateKeyContent);
		} else {
			$privateKey = new PrivateKey(new File('')); 
		}
		$csr = new CSR([
			'commonName'             => $commonName,
			'countryName'            => $countryName, 
			'stateOrProvinceName'    => $stateOrProvinceName,
			'localityName'           => $localityName,
			'organizationName'       => $organizationName,
			'organizationalUnitName' => $organizationalUnitName,
			'emailAddress'           => $emailAddress

		], $privateKey);
		$x509 = $csr->sign();
		
		return [
			'privateKey'  => $privateKey->export(),
			'csr'         => $csr->export(),
			'certificate' => $x509->export(),
		];
	}
}