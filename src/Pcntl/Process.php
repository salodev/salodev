<?php
namespace salodev\Pcntl;
use salodev\IO\StandardInput;
use salodev\IO\StandardOutput;
use salodev\IO\StandardError;
use salodev\Worker;
use salodev\Deferred;
use salodev\Promise;

use Exception;

class Process {
    private $_stream;
    private $_command;
    private $_pipes;
	private $_stdIn;
	private $_stdOut;
	private $_stdErr;
	private $_exitCode = null;
	
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
		
		$this->_stdIn  = StandardInput :: CreateFromResource($this->_pipes[0])->setNonBlocking();
		$this->_stdOut = StandardOutput:: CreateFromResource($this->_pipes[1])->setNonBlocking();
		$this->_stdErr = StandardError :: CreateFromResource($this->_pipes[2])->setNonBlocking();
    }
	
	static public function Spawn(string $command, string $wd, array $env = []): self {
		return new self($command, $wd, $env);
	}
	
	public function waitForFinish(): Promise {
		$deferred = new Deferred;
		Worker::AddTask(function($taskIndex) use ($deferred) {
			if (!$this->isRunning()) {
				Worker::RemoveTask($taskIndex);
				if ($this->getExitCode() === 0) {
					$deferred->resolve($this);
				} else {
					$deferred->reject($this);
				}
			}
		}, true, "exec: {$this->_command}");
		
		return $deferred->getPromise();
	}
	
	static public function SpawnAndWait(string $command, string $wd, array $env = []): Promise {
		$process = self::Spawn($command, $wd, $env);
		return $process->waitForFinish();
	}
	
	public function getStdin(): StandardInput {
		return $this->_stdIn;
	}
	
	public function getStdout(): StandardOutput {
		return $this->_stdOut;
	}
	
	public function getStderr(): StandardError {
		return $this->_stdErr;
	}
	
    public function write(string $content): self {
		$this->getStdin()->write($content);
		return $this;
    }
	
    public function read(int $length = 256): string {
		return $this->getStdout()->read($length);
    }
	
    public function readError(int $length = 256): string {
		return $this->getStderr()->read($length);
    }
	
    public function terminate(int $signal = 15): void {
        proc_terminate($this->_stream, $signal);
    }
	
    public function getStatus(): array {
        return proc_get_status($this->_stream);
    }
	
	public function getExitCode(): int {
		if ($this->_exitCode===null) {
			$arr = $this->getStatus();
			$this->_exitCode = $arr['exitcode'];
		}
		return $this->_exitCode;
	}
	
    public function close(): void {
        proc_close($this->_stream);
    }
	
    public function isRunning(): bool {
        $status = $this->getStatus();
        return $status['running'];
    }
	
    public function isSignaled(): bool {
        $status = $this->getStatus();
        return $status['signaled'];
    }
	
    public function getTermSignal(): int {
        $status = $this->getStatus();
        return $status['termsig'];
    }
	
    public function getStopSignal(): int {
        $status = $this->getStatus();
        return $status['stopsig'];
    }
	
	public function getPID(): int {
		$info = $this->getStatus();
		return $info['pid'];
	}
    
}