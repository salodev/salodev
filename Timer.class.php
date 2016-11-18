<?php

class Timer {
	static public function TimeOut($fn, $useconds) {
		$currentTime = microtime(true);
		$timeToStart = $currentTime + ($useconds/1000);
		$taskIndex = Worker::AddTask(function($taskIndex) use ($timeToStart, $fn) {
			if (microtime(true)>= $timeToStart) {
				Worker::RemoveTask($taskIndex);
				$fn();
			}
		}, true, "{$useconds}us TIMED OUT TASK");
		return $taskIndex;
	}
	static public function Interval($fn, $useconds) {
		$baseTime = microtime(true);
		$counter = 0;
		$taskIndex = Worker::AddTask(function($taskIndex) use ($timeToStart, $fn, $baseTime, &$counter, $useconds) {
			if (microtime(true)>= $baseTime + (($useconds/1000)*$counter)) {
				$counter++;
				$fn();
			}
		}, true, "INTERVAL TASK");
		return $taskIndex;
	}
	static public function Delete($taskIndex){
		return Worker::RemoveTask($taskIndex);
	}
}