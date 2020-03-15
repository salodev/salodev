<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace salodev\Debug;

/**
 * Description of ExceptionDumper
 *
 * @author salomon
 */
class ExceptionDumper {
	
	static public function ParseFromThrowable(\Throwable $e): array { 
		$exceptionClass = get_class($e);
		$message = "'{$e->getMessage()}' in file {$e->getFile()} ({$e->getLine()})";
		$rsTrace = [];
		foreach($e->getTrace() as $k => $info) {
			$class = &$info['class'];
			$type = &$info['type'];
			$argumentsString = '';
			$argsList = [];
			if (!empty($info['args'])) {
				foreach($info['args'] as $arg) {
					$argsList[] = static::ParseValue($arg, 10);
				}
				$argumentsString = implode(', ', $argsList);
			}
			$file = &$info['file'];
			$line = &$info['line'];
			$rsTrace[] = [
				'id' => "$k ",
				'function' => $class . $type . $info['function'] . '(' . $argumentsString . ')',
				'location' => '../' . basename($file) . ' (' . $line . ')',
				'file' => &$info['file'],
				'line' => &$info['line'],
				'args' => '',
			];
		}
		
		return [
			'class'   => $exceptionClass,
			'message' => $message,
			'rsTrace' => $rsTrace,
		];
	}
	
	static public function DumpFromThrowable(\Throwable $e): string {
		$data = static::ParseFromThrowable($e);
		$string = '';
		$string .= "{$data['class']}: '{$e->getMessage()}' in file{$e->getFile()} ({$e->getLine()})\n\n";
		$string .= "CALL STACK:\n";
		foreach($data['rsTrace'] as $row) {
			$string .= "#{$row['id']} {$row['file']} {$row['line']} {$row['function']}\n";
		}
		$string .= "\n";
		if ($e->getPrevious()) {
			$string .= "\nPREVIOUS:\n";
			$string .= static::DumpFromThrowable($e);			
		}
		return $string;
	}
	
	static public function ParseValue($value, int $maxDepth = 5, int $maxCount = 10, int $depth = 0) {
		
		if (is_string($value)) {
			$parsed = "'{$value}'";
		} elseif (is_numeric($value)) {
			$parsed = "{$value}";
		} elseif(is_callable($value)) {
			$parsed = "function() {}";
		} elseif (is_object($value)) {
			$parsed = get_class($value)."(...)";
		} elseif (is_array($value)) {
		
			if ($depth >= $maxDepth) {
				return '[ ... ]	';
			}
			$parsed = "[\n";
			if (count($value)) {
				if (range(0, count($value)-1) == array_keys($value)) {
					foreach($value as $v) {
						$parsed .= str_repeat("\t", $depth + 1) . static::ParseValue($v, $maxDepth, $maxCount, $depth+1) . ",\n";
					}
				} else {
					foreach($value as $k => $v) {
						$parsed .= str_repeat("\t", $depth + 1) . "{$k} => " . static::ParseValue($v, $maxDepth, $maxCount, $depth+1) . ", \n";
					}
				}
			}
			$parsed .= str_repeat("\t", $depth) .']';
		} elseif (is_null($value)) {
			$parsed = 'NULL';
		} elseif (is_bool($value)) {
			$parsed = $value ? 'TRUE' : 'FALSE';
		} else {
			$parsed = '??'. gettype($value).'??';
		}
		
		return $parsed;
	}
}
