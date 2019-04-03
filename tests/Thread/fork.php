#!/usr/bin/php
<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/autoload.php');
use salodev\Pcntl\Thread;
declare(ticks = 1);
$pidChild = Thread::Fork(function() {
	$pid = posix_getpid();
	echo "I am forked, pid {$pid}\n";
});


$pid = posix_getpid();
echo "I am original proccess, pid {$pid}\n";