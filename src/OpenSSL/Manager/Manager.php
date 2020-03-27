<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace salodev\OpenSSL\Manager;

/**
 * Description of Manager
 *
 * @author salomon
 */
class Manager {
	//put your code here
	
	private $_data = [];
	private $_table = [];
	
	public function test() {
		$manager = new Manager;
		$csr = $manager->getCSR();
		$csr->storeCertificate($certificate);
		$manager->storeAll();
	}
	
	public function load(array $data) {
		$this->_data = $data;
		$this->updateTable();
	}
	
	public function updateTable() {
		foreach($this->_data['pkeys'] as $pkeyID => $pkeyData) {
			foreach($pkeyData['requests'] as $csrID => $csrData) {
				foreach($csrData['responses'] as $crtID => $crtData) {
					$this->_table[] = [
						'pkeyID'      => $pkeyID,
						'pkeyContent' => $pkeyData['content'],
						'csrID'       => $csrID,
						'csrContent'  => $csrData['content'],
						'crtID'       => $crtID,
						'crtContent'  => $crtData['content'],
					];
				}
			}
		}
	}
	
	public function addPrivateKey(string $content): string {
		$pkeyID = md5($privkey);
		$this->_data['pkeys'][$pkeyID] = [
			'content' => $content,
			'certificates' => [],
		];
		$this->updateTable();
		return $pkeyID;
	}
	
	public function addCsr(string $pkeyID, string $content): string {
		if (!isset($this->_data[$pkeyID])) {
			throw new \Exception('Private Key ID does not exist');
		}
		$csrID = md5($content);
		$this->_data[$pkeyID]['requests'][$csrID] = [
			'csr' => $content,
			'certificate' => null,
		];
		$this->updateTable();
		return $csrID;
	}
	
	public function addCertificate(string $keyID, string $csrID, string $content): string {
		if (!isset($this->_data[$pkeyID])) {
			throw new \Exception('Private Key ID does not exist');
		}
		if (!isset($this->_data[$pkeyID]['requests'][$csrID])) {
			throw new \Exception('Request ID does not exist');
		}
		$crtID = md5($content);
		$this->_data[$pkeyID]['certificates'][$csrID]['responses'][$crtID] = $content;
		$this->updateTable();
		return $crtID;
	}
}
