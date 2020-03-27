#!/usr/bin/php
<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/autoload.php');

use salodev\Pcntl\Thread;

Thread::SetSignalHandler(SIGINT, function($signo) {
	echo "SIGNAL RECEIVED: {$signo}\n";
	die();
});
do {
	echo "holaa";
	sleep(1);
} while(true);