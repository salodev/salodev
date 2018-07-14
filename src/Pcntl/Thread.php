<?php
namespace salodev\Pcntl;

use Exception;

/**
 * El proposito general de esta clase es encapsular ciertos metodos de un thread
 * a fin de facilitar algunas tareas y escribir un código más semántico y elegante.
 */
class Thread {
	
	static protected $_childPIDs = [];
	static protected $_childs    = [];
	
	/**
	 * 
	 * @param callback $onForked
	 * @return Child
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
			$onForked($childPid);
			die(); // avoid continue on origal proccess code.
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
}