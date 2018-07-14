<?php
namespace salodev\Pcntl;

use Exception;

class Process {
    private $_stream;
    private $_command;
    private $_pipes;
    public function __construct(string $command, string $wd, array $env = []) {
        $this->_command = $command;
        $this->_stream = proc_open($this->_command, array(
           0 => array('pipe', 'r'),
           1 => array('pipe', 'w'),
           2 => array('pipe', 'w'),
        ), $this->_pipes, $wd, $env);
        if (!is_resource($this->_stream)) {
            throw new Exception('Proccess can not be started.');
        }
        stream_set_blocking($this->_pipes[0], 0);
        stream_set_blocking($this->_pipes[1], 0);
        stream_set_blocking($this->_pipes[2], 0);
    }
	
	static public function Spawn(string $command, string $wd, array $env = []): self {
		return new self($command, $wd, $env);
	}
	
    public function write(int $string): self {
        fwrite($this->_pipes[0], $string);
		return $this;
    }
	
    public function read(int $length = 256): string {
        $buffer = '';
        while($read = fread($this->_pipes[1], $length)) {
            $buffer .= $read;
        }
        return $buffer;
    }
	
    public function readError(int $length = 256): string {
        $buffer = '';
        while($read = fread($this->_pipes[2], $length)) {
            $buffer .= $read;
        }
        return $buffer;
    }
	
    public function terminate(int $signal = 15): void {
        proc_terminate($this->_stream, $signal);
    }
	
    public function getStatus(): array {
        return proc_get_status($this->_stream);
    }
	
    public function close(): void {
        proc_close($this->_stream);
    }
	
    public function isRunning(): bool {
        $status = $this->getStatus();
        return $status['running'];
    }
	
	public function getPID(): int {
		$info = $this->getStatus();
		return $info['pid'];
	}
    
}