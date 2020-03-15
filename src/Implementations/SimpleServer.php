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
declare(ticks = 1); 

class SimpleServer {
	
	use Logger;
	
	static public $socket = null;
	static protected $incomingConnection = null;
	
	static public function Listen(string $address, int $port, callable $onRequest, int $usleep = 1000): void {
		
		/**
		 *  It makes cancellabe by CTRL+C signal
		 */
		Thread::SetSignalHandler(SIGINT, function($signo) {
			die();
		});
		
		/**
		 * For long time run
		 */
		set_time_limit(0);
		
		$socket = new Socket();
		// static::$socket = $socket;
		$socket->create(AF_INET, SOCK_STREAM, SOL_TCP);
		
		if ($socket->bind($address, $port)===false) {
			static::Log("error en bind()...\n");
			return;
		}
		register_shutdown_function(function() use ($socket){
			try {
				$socket->close();
			} catch (\Exception $e) {
				// do nothing
			}
		});
		$socket->listen();
		do {
			if (!$socket->isValidResource()) {
				break;
			}
			$socket->setNonBlocking();
			
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
						static::Log("Uncaught Exception: {$e->getMessage()} at file '{$e->getFile()}' ({$e->getLine()})\n\n");
						static::Log($e->getTraceAsString());
						$connection->write('Uncaught service exception.');
					}
				} catch (Error $e) {
					if ($connection->isValidResource()) {
						static::Log("Uncaught Error: {$e->getMessage()} at file '{$e->getFile()}' ({$e->getLine()})\n\n");
						static::Log($e->getTraceAsString());
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