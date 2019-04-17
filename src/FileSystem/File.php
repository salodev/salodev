<?php

namespace salodev\FileSystem;
use salodev\Worker;
use salodev\IO\FileStream;
use salodev\Deferred;
use salodev\Promise;

class File {
	
	private $_path     = null;
	private $_hidePath = false;
	/**
	 *
	 * @var salodev\FileSystem\FileInfo 
	 */
	private $_fileInfo = null;
	
	static public function GetInstance(string $path): self {
		return new self($path);
	}
	
	public function __construct(string $path) {
		$this->_path = $path;
	}
	
	public function hidePath(bool $value): self {
		$this->_hidePath = $value;
		return $this;
	}
	
	public function getFileName(): string {
		return basename($this->_path);
	}
	
	public function getSecureFileName(): string {
		if ($this->_hidePath) {
			return $this->getFileName();
		}
		return $this->_path;
	}
	
	public function getFullPath(): string {
		return $this->_path;
	}
	
	public function exists(): bool {
		return file_exists($this->_path);
	}
	
	public function checkPath(): bool {
		return is_file($this->_path);
	}
	
	public function canRead(): bool {
		return is_readable($this->_path);
	}
	
	public function canWrite(): bool {
		return is_writable($this->_path);
	}
	
	public function canExecute(): bool {
		return is_executable($this->_path);
	}
	
	public function isFile(): bool {
		return is_file($this->_path);
	}
	
	public function validateRead() {
		if (!$this->exists()) {
			throw new ReadError("File {$this->getSecureFileName()} does not exist.");
		}
		
		if (!$this->canRead()) {
			throw new ReadError("Access denied to read file {$this->getSecureFileName()}");
		}
		
		if (!$this->isFile()) {
			throw new ReadError("{$this->getSecureFileName()} is not a file!");
		}
	}
	
	public function validateWrite() {
		if (!$this->exists()) {
			return;
		}
		
		if (!$this->canWrite()) {
			throw new WriteError("Access denied to write file {$this->getSecureFileName()}");
		}
	}
	
	public function validateRemove() {
		if (!$this->exists()) {
			return;
		}
		
		if (!$this->canRemove()) {
			throw new WriteError("Access denied to remove file {$this->getSecureFileName()}");
		}
	}
	
	public function putContents(string $data, bool $append = true): self {
		$flag = $append ? FILE_APPEND : FILE_IGNORE_NEW_LINES;
		file_put_contents($this->_path, $data, $flag);
	}
	
	public function getAllContent(int $offset = 0, int $maxLen = 0): string {
		$this->validateRead();
		
		if ($maxLen > 0) {
			$ret = file_get_contents($this->_path, false, null, $offset, $maxLen);
		} else {
			$ret = file_get_contents($this->_path, false, null, $offset);
		}
		
		if ($ret === false) {
			throw new ReadError("Error reading {$this->getSecureFileName()}");
		}
		return $ret;
	}
	
	public function streamAllContent(): void {
		$this->validateRead();
		
		readfile($this->_path);		
	}
	
	public function readAsync(int $bytes = 256): Promise {
		$deferred = new Deferred();
		$fs = FileStream::Create($this->_path, 'r');
		$readBuffer = '';
		Worker::AddTask(function($taskIndex) use ($deferred, $fs, $bytes, &$readBuffer) {
			$partialRead = $fs->read($bytes);
			$readBuffer .= $partialRead;
			if (!$partialRead) {
				Worker::RemoveTask($taskIndex);
				$fs->close();
				$deferred->resolve($readBuffer);
			}
		}, true);
		return $deferred->getPromise();
	}
	
	public function remove(): self {
		$this->validateRemove();
		unlink($this->_path);
		return $this;
	}
	
	public function moveTo(string $targetFullPath, bool $createDirs = false): self {
		if ($createDirs) {
			$dirName = dirname($targetFullPath);
			if (!is_dir($dirName)) {
				mkdir($dirName, 0775, true);
			}
		}
		$return = rename($this->_path, $targetFullPath);
		if (!$return) {
			throw new Error('Unable move to destination');
		}
		$this->_path = $targetFullPath;
		return $this;
	}
	
	public function canRemove(): bool {
		return $this->canWrite();
	}
	
	public function exec() {
		throw new Error('Excecute not implemented yet.');
	}
	
	public function getMimeType() {
		if (!$this->_fileInfo) {
			$this->_fileInfo = new FileInfo($this->_path);
		}
		return $this->_fileInfo->getMimeType();
	}
}