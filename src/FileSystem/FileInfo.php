<?php

namespace salodev\FileSystem;

class FileInfo {
	private $_resource = null;
	private $_fileName = null;
	
	public function __construct(string $fileName) {
		$this->_resource = finfo_open();
		$this->_fileName = $fileName;
	}
	
	private function getInfo(int $type) {
		return finfo_file($this->_resource, $this->_fileName, $type);
	}
	
	public function getMimeType() {
		return $this->getInfo(FILEINFO_MIME_TYPE);
	}
	
	public function getMimeEncoding() {
		return $this->getInfo(FILEINFO_MIME_ENCODING);
	}
}