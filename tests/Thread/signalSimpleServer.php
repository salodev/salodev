#!/usr/bin/php
<?php
/**
 * Aim: Make a Socket Service Server that can be finished sending CTRL+C
 * 
 * Execute it and send CTRL+C or a sigint. Program shoud terminate inmediatelly.
 */
require_once(dirname(dirname(dirname(__FILE__))) . '/autoload.php');

use salodev\Implementations\SimpleServer;

SimpleServer::Listen('127.0.0.1', 4000, function($msg) {
	return "hi... You wrote: {$msg}";
});

