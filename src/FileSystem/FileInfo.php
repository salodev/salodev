<?php

namespace salodev\FileSystem;

class FileInfo {
	private $_resource = null;
	private $_fileName = null;
	
	public function __construct(string $fileName) {
		$this->_resource = finfo_open();
		$this->_fileName = $fileName;
	}
	
	public function getResource() {
		if (!is_resource($this->_resource)) {
			$this->_resource = finfo_open();
		}
		return $this->_resource;
	}
	
	private function getInfo(int $type) {
		return finfo_file($this->getResource(), $this->_fileName, $type);
	}
	
	public function getMimeType() {
		return $this->getInfo(FILEINFO_MIME_TYPE);
	}
	
	public function getMimeEncoding() {
		return $this->getInfo(FILEINFO_MIME_ENCODING);
	}
}