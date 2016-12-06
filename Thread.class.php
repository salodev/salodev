<?php

class Thread {
	static protected $_childPIDs = array();
	static public function Fork($fnChild) {
		$pid = pcntl_fork();
		if ($pid) {
			self::$_childPIDs[] = $pid;
		} else {
			self::$_childPIDs = array();
			$fnChild();
		}
		return $pid;
	}
	
	static public function GetPid() {
		return posix_getpid();
	}
    
    static public function ChangeIidentity($uid, $gid){
        global $pidFile;
        if(!posix_setgid($gid)){
            $this->logConsola("Unable to setgid to $gid!");
            unlink($pidFile);
            exit;
        }
        if(!posix_setuid($uid)){
            $this->logConsola("Unable to setuid to $uid!");
            unlink($pidFile);
            exit;
        }
    }
}