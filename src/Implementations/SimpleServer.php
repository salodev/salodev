<?php

/**
 * Implementation for a simple socket service.
 * No async support.
 * 
 * @TODO: Make support for secure connections.
 */
namespace salodev\Implementations;

use salodev\IO\Socket;
use salodev\Pcntl\Thread;
use Exception;

/**
 * It allows signal handlers run!
 */

class SimpleServer {
	
	use Logger;
	
	static public $socket = null;
	static protected $incomingConnection = null;
	
	static public function Listen(string $address, int $port, callable $onRequest, int $usleep = 1000): void {
		
		/**
		 * For long time run
		 */
		set_time_limit(0);
		
		$socket = new Socket();
		
		$socket->create(AF_INET, SOCK_STREAM, SOL_TCP);
		
		/**
		 * Reusing address prevent error on restart service.
		 */
		$socket->setOptionReuseAddressOnBind();
		
		if ($socket->bind($address, $port)===false) {
			static::LogError("error en bind()...");
			return;
		}
		
		/**
		 * Now wait for incomming connectios.
		 */
		$socket->listen();
		do {
			if (!$socket->isValidResource()) {
				break;
			}
			// $socket->setNonBlocking();
			
			/**
			 * Read if incoming connection
			 */
			if ($connection = $socket->accept()) {
				if (!($connection instanceof Socket)) {
					static::Log("error al accept()...\n");
					break;
				}
				static::$incomingConnection = $connection;
				/**
				 * Blocks the program making it awaiting (not async) for new data.
				 */
				$connection->setBlocking();
				$message = trim($connection->readAll(256, PHP_NORMAL_READ));
				try {
					
					/**
					 * Callback function passed handles incoming request
					 */
					$return = $onRequest($message, $connection);
					
					/**
					 * And Its response will send via open connection.
					 */
					$connection->write($return . "\n");
				} catch (Exception $e) {
					if ($connection->isValidResource()) {						
						static::LogException($e);
						$connection->write('Uncaught service exception.');
					}
				} catch (Error $e) {
					if ($connection->isValidResource()) {
						static::LogException($e);
						$connection->write('Uncaught service error.');
					}
				} finally {
					/**
					 * Because is a simple implementation, connection is closed
					 */
					if ($connection->isValidResource()) {
						$connection->close();
					}
				}
			}
			
			/**
			 * Because is non blocking CPU needs a relief
			 */
			usleep($usleep);
			
		} while(true);
		
		if ($socket->isValidResource()) {
			$socket->close();
		}
	}
}