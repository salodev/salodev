<?php
namespace salodev\IO;

class FileStream extends ClientStream {
	
	static public function Create(string $path, string $mode): self {
		return new self([
			'spec' => $path,
			'mode' => $mode,
		]);
	}
}