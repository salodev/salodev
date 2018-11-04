<?php

namespace salodev\Debug\CodeTracer;


class Call {
	
	private $file      = '';
	private $line      = 0;
	private $class     = '';
	private $function  = '';
	private $type      = '';
	private $arguments = [];
	private $object    = null;
	
	public function __construct(array $data) {
		$this->file      = $data['file'     ];
		$this->line      = $data['line'     ];
		$this->class     = $data['class'    ]??'';
		$this->function  = $data['function' ];
		$this->type      = $data['type'     ]??'';
		$this->arguments = $data['args'     ]??[];
		$this->object    = $data['object'   ]??null;
	}
	
	public function getFile(): string {
		return $this->file;
	}
	public function getLine(): int {
		return $this->line;
	}
	public function getClass(): string {
		return $this->class;
	}
	public function getFunction(): string {
		return $this->function;
	}
	public function getType(): string {
		return $this->type;
	}
	public function getArguments(): array {
		return $this->arguments;
	}
	public function getArgumentsCount(): int {
		return count($this->arguments);
	}
	public function getObject() {
		return $this->object;
	}
}