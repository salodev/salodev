#!/usr/bin/php
<?php
declare(ticks=1);
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/autoload.php');
use salodev\Debug\CodeTracer\Expectation;
use salodev\Debug\CodeTracer\Call;

function firstCall() {
	secondCall(1);
	secondCall(2);
	thirdCall();
}

function secondCall($value) {
	$a=$value;
}

function thirdCall() {
	
}

$ex = Expectation::Create('', 'secondCall')->setTimes(2);
$ex->setAnalyzerCallback(function(Call $call) use ($ex) {
	
	if (!$call->getArgumentsCount()) {
		return false;
	}
	$value = $call->getArguments()[0];
	return $value == 1;
});
firstCall();

echo "Reached count: {$ex->getCount()}\n";

class Host {
	
	private $handshaked = false;
	private $guess = null;
	
	public function receive(Guess $guess) {
		$this->guess = $guess;
		$guess->doHandshake($this);
	}
	
	public function doHandshake() {
		$this->handshaked = true;
		$this->meetData();
	}
	
	public function meetData() {
		$this->guess->data = 'my data';
	}
}

class Guess {
	public function doHandshake(Host $host) {
		$host->doHandshake();
	}
}

Expectation::Create(Guess::class, 'doHandshake'); //->since()->setClass(Host::class)->setMethod('meetData');

(new Host)->receive(new Guess);

echo "Reached count: " . Expectation::GetLast()->getCount(). "\n";