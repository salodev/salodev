#!/usr/bin/php
<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/autoload.php');
use salodev\Thread;
use salodev\Implementations\SimpleServer;
declare(ticks = 1);
$child = Thread::Fork(function() {
	declare(ticks = 1);
	Thread::SetSignalHandler([SIGINT], function() {
		echo "KILLED....";
		sleep(1);
		echo "KILLED!!";
		die();
	});
	SimpleServer::Listen('127.0.0.1', '8080', function($msg) {
		return "hola {$msg}";
	});
});
sleep(3);
$child->sendSignal(SIGINT);
// sleep(2);
// $child->kill();
echo "killed...";
// $child->wait();
echo "*{$child->exited()}*";
echo "and terminated...";
