<?php
namespace salodev;

class SocketServer {
    private $_status;
	
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
    
    public function addListener($address, $port, $fn){
	$this->listen($address, $port, function(Socket $socket) use($fn, $forkable) {
		Worker::AddTask(function() use($socket, $fn, $forkable) {
			$connection = $socket->accept();
			if (!$connection) {
				return;
			}
			Worker::AddTask(function() use ($fn, $connection) {
				$fn($connection);
			});
			$connection->close();
		}, true, 'WAITING FOR INCOMMING REQUESTS');
	});
    }
    
    public function start() {
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

        $this->logConsole("SERVER READY. WAITING FOR CONNECTIONS");
        Worker::Start();
    }
}
