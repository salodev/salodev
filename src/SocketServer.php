<?php
namespace salodev;

class SocketServer {
	
	static public function LogConsole($texto) {
		echo date('Y-m-d H:i:s') . " {$texto}\n";
	}
	
	static public function Listen($address, $port, $fnOnReady) {
		$socket = new Socket();
		$socket->create(AF_INET, SOCK_STREAM, 0);
		$socket->setOption(SOL_SOCKET, SO_REUSEADDR, 1);
		$socket->setNonBlocking();
        
		$tries = 0;
		Timer::Interval(function($taskIndex) use($socket, $tries, $address, $port, $fnOnReady) {
			$tries++;
			if (!$socket->bind($address, $port)) {
				if ($tries>10) {
					throw new \Exception("Socket bind failed after {$tries} tries: " . $socket->getErrorText());
				}
				return;
			}
			Worker::RemoveTask($taskIndex); // To cancel interval.
			if(($ret = $socket->listen(0)) < 0){
				throw new \Exception("Socket Listen failed: " . $socket->getErrorText());
			}
			$socket->setNonBlocking();
			$fnOnReady($socket);
		}, 3000);
	}
    
	static public function AddListener($address, $port, $fn){
		self::Listen($address, $port, function(Socket $socket) use($fn) {
			Worker::AddTask(function($taskIndex) use($socket, $fn) {
				$connection = $socket->accept();
				if (!$connection) {
					return;
				}
				$connection->setNonBlocking();
				// \salodev\IO::WriteLine('Luego de acceptar...');
				try {
					$fn($connection);
				} catch(Exception $e) {
					Worker::RemoveTask($taskIndex);
					echo $e->getMessage() . "\n";
				}
			}, true, 'WAITING FOR INCOMMING REQUESTS');
		});
	}
    
	static public function Start($useconds = 1) {
		set_time_limit(0);
		ob_implicit_flush();
		self::LogConsole("SERVER READY. WAITING FOR CONNECTIONS");
		Worker::Start($useconds);
	}
}
