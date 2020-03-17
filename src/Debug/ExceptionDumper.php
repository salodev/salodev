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
	
	static public function ParseFromThrowable($e): array { 
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
	
	static public function DumpFromThrowable( $e, int $nesting = 0): string {
		if ($nesting > 10) {
			return '';
		}
		$data = static::ParseFromThrowable($e);
		$string = '';
		$string .= "{$data['class']} thrown: '{$e->getMessage()}' in file{$e->getFile()} ({$e->getLine()})\n\n";
		$string .= "CALL STACK:\n";
		foreach($data['rsTrace'] as $row) {
			$string .= "#{$row['id']} {$row['file']} {$row['line']} {$row['function']}\n";
		}
		$string .= "\n";
		if ($e->getPrevious()) {
			$string .= "\nPREVIOUS:\n";
			$string .= static::DumpFromThrowable($e, ++$nesting);
		}
		return $string;
	}
	
	static public function ParseValue($value, int $maxDepth = 5, int $maxCount = 10, int $depth = 0) {
		if (is_string($value)) {
			return "'{$value}'";
		}
		if (is_numeric($value)) {
			return "{$value}";
		}
		if(is_callable($value)) {
			return "function() { ... }";
		}
		if (is_object($value)) {
			return static::parseObject($value, $maxDepth, $maxCount, $depth);
		}
		if (is_array($value)) {
			return static::parseArray($value, $maxDepth, $maxCount, $depth);
		}
		if (is_null($value)) {
			return 'NULL';
		}
		if (is_bool($value)) {
			return $value === true ? 'TRUE' : 'FALSE';
		}
		
		return '??' . gettype($value). '??';
	}
	
	static public function parseArray($value, int $maxDepth = 5, int $maxCount = 10, int $depth = 0): string {
		if ($depth >= $maxDepth) {
			return '[ ... ]	';
		}
		$parsed = "[\n";
		if (count($value)) {
			if (range(0, count($value)-1) === array_keys($value)) {
				foreach($value as $v) {
					$parsed .= static::tab(static::ParseValue($v, $maxDepth, $maxCount, $depth+1), $depth +1) . ",\n";
				}
			} else {
				foreach($value as $k => $v) {
					$parsed .= static::tab(static::ParseValue($k, 1, 1, 0) . " => " . static::ParseValue($v, $maxDepth, $maxCount, $depth+1), $depth +1) . ", \n";
				}
			}
		}
		$parsed .= static::tab(']', $depth);
		
		return $parsed;
	}
	
	static public function parseObject($object, int $maxDepth = 5, int $maxCount = 10, int $depth = 0): string {
		if ($depth >= $maxDepth) {
			return get_class($object) . ' { ... }';
		}
		$str  = '';
		$str .= get_class($object). " {\n";
		foreach($object as $property => $value) {
			$str .= static::tab($property, $depth +1) . ': ' . static::ParseValue($value, $maxDepth, $maxCount, $depth+1) . "\n";
		}
		$str .= static::tab("}", $depth);
		
		return $str;
	}
	
	static public function tab(string $string, int $tabIndex, string $tabString = "\t"): string {
		return str_repeat($tabString, $tabIndex) . $string;
	}
}
