<?php
namespace salodev;

use Exception;

class Worker {
	static private $_stopped = true;
	static private $_tasks   = [];
	static public function Start(int $usleep = 1000, callable $exceptionCatcherCallback = null): void {
		self::$_stopped = false;
		while (count(self::$_tasks)) {
			usleep($usleep);
			foreach(self::$_tasks as $taskIndex => $taskInfo) {
				if (self::$_stopped) {
					break 2;
				}
				try {
					$taskInfo['callback']($taskIndex);
				} catch(Exception $e) {
					if ($taskInfo['persistent']!==true) {
						self::removeTask($taskIndex);
					}
					if (!is_callable($exceptionCatcherCallback)) {
						throw $e;
					}
					$exceptionCatcherCallback($e, $taskInfo);
				}
				if ($taskInfo['persistent']!==true) {
					self::removeTask($taskIndex);
				}
			}
		};
	}
	static public function Stop(): void {
		self::$_stopped = true;
	}
	
	static public function AddTask(callable $callback, bool $persistent = true, string $taskName = 'no name'): int {
		self::$_tasks[] = [
			'callback'   => $callback, 
			'persistent' => $persistent, 
			'taskName'   => $taskName,
		];
		end(self::$_tasks);
		return key(self::$_tasks); // returns index id.
	}
	
	static public function RemoveTask(int $taskIndex): void {
		if (!isset(self::$_tasks[$taskIndex])) {
			return;
		}
		$task = self::$_tasks[$taskIndex];
		unset(self::$_tasks[$taskIndex]);
	}
	
	static public function Clear(): void {
		self::$_tasks = [];
	}
	
	static public function IsRunning(): bool {
		return !self::$_stopped;
	}
	
	static public function GetCountTasks(): int {
		return count(self::$_tasks);
	}
	
	static public function GetTasksList(): array {
		return self::$_tasks;
	}
}