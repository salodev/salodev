<?php
namespace salodev;

class FileStream extends ClientStream {
	
	static public function Create(string $path, string $mode): self {
		return new self([
			'path' => $path,
			'mode' => $mode,
		]);
	}
	
	public function open(array $options = []) {
		$this->_resource = fopen($options['path'] ?? null, $options['mode'] ?? null);
	}
}