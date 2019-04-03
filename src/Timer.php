<?php
namespace salodev;

class Timer {
	static public function TimeOut(callable $fn, int $useconds): int {
		$currentTime = microtime(true);
		$timeToStart = $currentTime + ($useconds/1000);
		$taskIndex = Worker::AddTask(function($taskIndex) use ($timeToStart, $fn) {
			if (microtime(true)>= $timeToStart) {
				Worker::RemoveTask($taskIndex);
				$fn($taskIndex);
			}
		}, true, "{$useconds}us TIMED OUT TASK");
		return $taskIndex;
	}
	
	static public function Interval(callable $fn, int $useconds): int {
		$baseTime = microtime(true);
		$counter = 0;
		$taskIndex = Worker::AddTask(function($taskIndex) use ($fn, $baseTime, &$counter, $useconds) {
			if (microtime(true)>= $baseTime + (($useconds/1000)*$counter)) {
				$counter++;
				$fn($taskIndex);
			}
		}, true, "INTERVAL TASK");
		return $taskIndex;
	}
	
	static public function Delete(int $taskIndex): void {
		return Worker::RemoveTask($taskIndex);
	}
}