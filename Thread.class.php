<?php
/**
 * El proposito general de esta clase es encapsular ciertos metodos de un thread
 * a fin de facilitar algunas tareas y escribir un código más semántico y elegante.
 */
class Thread {
	static protected $_childPIDs = array();
	static public function Fork(callable $onForked) {
		$pid = pcntl_fork();
		if ($pid) {
			self::$_childPIDs[] = $pid;
		} else {
			self::$_childPIDs = array();
			$onForked();
		}
		return $pid;
	}
	
	static public function SetSignalHandler($signos, callable $fn) {
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
}