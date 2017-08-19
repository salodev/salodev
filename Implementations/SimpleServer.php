<?php

namespace salodev\Implementations;

use salodev\Socket;
use Exception;

class SimpleServer {
	static public $socket = null;
	static public function Listen(string $address, int $port, callable $onRequest) {
		$socket = new Socket();
		self::$socket = $socket;
		$socket->create(AF_INET, SOCK_STREAM, SOL_TCP);
		$socket->setBlocking();
		if ($socket->bind($address, $port)===false) {
			echo "error en bind()...\n";
		}
		$socket->listen();
		do {
			$connection = $socket->accept();
			if (!($connection instanceof Socket)) {
				echo "error al accept()...\n";
				break;
			}
			$connection->setBlocking();
			$message = trim($connection->readAll(256, PHP_NORMAL_READ));
			try {
				$return = $onRequest($message);
				$connection->write($return);
			} catch (Exception $e) {
				$connection->write('Uncatched service error.');
			}
			
			$connection->close();
			
		} while(true);
		
		$socket->close();
	}
}