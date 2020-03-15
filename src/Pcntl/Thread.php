<?php
namespace salodev\Pcntl;

use salodev\IO\Stream;
use Exception;

/**
 * Abstraction clas for current thread information and operations
 */
abstract class Thread {
	
	static protected $_childPIDs = [];
	static protected $_childs    = [];
	
	public function start(array $params = []): Child {
		return self::Fork(function() use ($params) {
			$this->run($params);
		});
	}
	
	abstract public function run(array $params = []);
	
	/**
	 * Easy way to fork 
	 * @param callback $onForked Function be called on forked child proces
	 * @return Child Instance of child forked process
	 */
	static public function Fork(callable $onForked): Child {
		$pid = pcntl_fork();
		$child = new Child($pid);
		if ($pid) {
			self::$_childPIDs[] = $pid;
			self::$_childs[] = $child;
		} else {
			self::$_childPIDs = [];
			self::$_childs = [];
			$childPid = posix_getpid();
			try {
				$onForked($childPid);
			} catch (\Error $e) {
				throw new \Exception("Error during fork!: \n{$e->getMessage()} \n{$e->getFile()} ({$e->getLine()})", 0, $e);
			}
			/**
			 * avoid continue on origal proccess code.
			 */
			die();
		}
		return $child;
	}
	
	static public function SetSignalHandler($signos, callable $fn) {
		declare(ticks = 1);
		if (!is_array($signos)) {
			$signos = array($signos);
		}
		foreach($signos as $signo) {
			pcntl_signal($signo, $fn);
		}
	}
	
	static public function GetPid() {
		return posix_getpid();
	}
    
    static public function ChangeIidentity($uid, $gid){
        if(!posix_setgid($gid)){
            throw new Exception('Unable to change GID');
        }
        if(!posix_setuid($uid)){
            throw new Exception('Unable to change UID');
        }
    }
	
	static public function SpawnChildProcess(string $command, string $wd, array $env = []): Process {
		return Process::Spawn($command, $wd, $env);
	}
	
	static public function Nice(int $increment): void {
		proc_nice($increment);
	}
	
	static public function Kill(): void {
		posix_kill(static::GetPid(), SIGKILL);
	}
	
	static public function CloseAllStreams(): void {
		Stream::CloseAll();
	}
	
	static public function HasChild(Child $child) {
		foreach(static::$_childs as $test) {
			$test->getPid() == $child->getPid();
			return true;
		}
		return false;
	}
}