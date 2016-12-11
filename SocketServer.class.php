<?php
namespace salodev;

class SocketServer extends Thread {
    private $_status;
    private $_pidFile = '/tmp/SocketServer.pid';
    
    public function setPidFile($pidFile) {
        $this->_pidFile = $pidFile;
    }
    
    public function openPidFile($file) {
        if(file_exists($file)){
            $fp = fopen($file, "r");
            $pid = fgets($fp, 1024);
            fclose($fp);
            if(posix_kill($pid, 0)){
                $this->logConsole("Server already running with PID: $pid");
                exit(1);
            }
            $this->logConsole("Removing PID file for defunct server process $pid");
            if(!unlink($file)){
                $this->logConsole("Cannot unlink PID file $file");
                exit(1);
            }
        }

		$fp = fopen($file, 'w');
        if(!$fp){
			throw new Exception("Unable to open PID file $file for writing...");
        }
        return $fp;
    }
	
    public function logConsole($texto) {
        echo date('Y-m-d H:i:s') . " {$texto}\n";
    }
	
	public function listen($address, $port, $fnOnReady) {
        $socket = new Socket();
        $socket->create(AF_INET, SOCK_STREAM, 0);
        $socket->setOption(SOL_SOCKET, SO_REUSEADDR, 1);
        
        $tries = 0;
		Timer::Interval(function($taskIndex) use($socket, $tries, $address, $port, $fnOnReady) {
			$tries++;
			if (!$socket->bind($address, $port)) {
				if ($tries>10) {
					throw new Exception("Socket bind failed after {$tries} tries: " . $socket->getErrorText());
				}
				return;
			}
			Worker::RemoveTask($taskIndex); // To cancel interval.
			if(($ret = $socket->listen(0)) < 0){
				throw new Exception("Socket Listen failed: " . $socket->getErrorText());
			}
			$fnOnReady($socket);
		}, 3000);
	}
    
    public function addListener($address, $port, $fn, $forkable = true){
		$this->listen($address, $port, function(Socket $socket) use($fn, $forkable) {
			Worker::AddTask(function() use($socket, $fn, $forkable) {
				$connection = $socket->accept();
				if (!$connection) {
					return;
				}
				if ($forkable) {
					Thread::Fork(function() use ($fn, $connection) {
						Worker::Clear();
						Worket::Stop();
						Worker::AddTask(function() use($fn, $connection) {
							$fn($connection);
						});
						Worker::Start();
					});
				} else {
					Worker::AddTask(function() use ($fn, $connection) {
						$fn($connection);
					});
				}
				$connection->close();
				return;
			}, true, 'WAITING FOR INCOMMING REQUESTS');
		});
    }
    
    public function Start() {
        set_time_limit(0);
        ob_implicit_flush();
		self::SetSignalHandler([SIGTERM, SIGINT], function() {
			if (count(self::$_childPIDs)) { // si tiene hijos..
				foreach(self::$_childPIDs as $pidChild) {
					$out = "Sending SIGTERM to child #{$pidChild}...";
					$killed = posix_kill($pidChild, SIGTERM);
					$out .= $killed ? "OK" : "ERROR";
					$this->logConsole($out);
				}
			}
			exit(0);
		});
		self::SetSignalHandler(SIGCHILD, function() {
			if (count(self::$_childPIDs)) {
				$pid = pcntl_waitpid(-1, $this->_status, WUNTRACED);
				foreach(self::$_childPIDs as $k => $pidChild) {
					if ($pidChild==$pid) {
						unset(self::$_childPIDs[$k]);
						break;
					}
				}
			}
		});
		
        $fh = $this->openPidFile($this->_pidFile);
		$pid = self::GetPid();
        fputs($fh, $pid);
        fclose($fh);
        declare(ticks = 1);

        $this->logConsole("SERVER READY. WAITING FOR CONNECTIONS");
        Worker::Start();
        if(posix_getpid() == $pid){
            unlink($this->_pidFile);
        }
    }
}