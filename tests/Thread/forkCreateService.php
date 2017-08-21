#!/usr/bin/php
<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/autoload.php');
use salodev\Thread;
use salodev\Child;
use salodev\Implementations\SimpleServer;
declare(ticks = 1);
$child = Thread::Fork(function($pid) {
	echo "I am the child, my pid is {$pid}\n";
	SimpleServer::Listen('127.0.0.1', 4000, function($msg) {
		return "hi... You wrote: {$msg}";
	});
});

$pid = $child->getPid();
echo "I am original proccess, pid {$pid}\n";