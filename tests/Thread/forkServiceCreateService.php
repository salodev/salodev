#!/usr/bin/php
<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/autoload.php');
use salodev\Thread;
use salodev\Child;
use salodev\Implementations\SimpleServer;
use salodev\ClientSocket;
use salodev\Socket;

SimpleServer::Listen('127.0.0.1', 4000, function(string $msg, Socket $connection) {
	$words   = explode(' ', $msg);
	$command = $words[0];
	$param   = $words[1] ?? null;
	if ($command == 'service' && $param) {
		if ($param == 4000) {
			return 'Impossible, I am using this port. Please try another, e.g. 4001';
		}
		Thread::Fork(function() use ($param) {
			SimpleServer::Listen('127.0.0.1', $param, function($msg) {
				return "Hi! You wrote: {$msg}";
			});
		});
		sleep(1);
		$client = new ClientSocket("127.0.0.1:{$param}" ,'rw');
		$ret = $client->writeAndRead('Tamo listo??');
		
		return 'Service created!. Its returned: '. $ret;
	}
	if ($command == 'stop') {
		die();
	}
});