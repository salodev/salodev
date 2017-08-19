<?php

namespace salodev\Debug\ObjectInspector;

class URLHandler {
	public $parameters = array();
	public $uri = null;

	public function __construct() {
		$this->parse();
	}
	public function parse($url = null) {
		if ($url === null) {
			$url = &$_SERVER['REQUEST_URI'];
		}
		@list($uri,$qs) = explode('?', $url);
		$this->uri = $uri;
		$this->parameters = array();
		if (empty($qs)) return;

		$qp = explode('&', $qs);
		foreach($qp as $q){
			list($n,$v) = explode('=', $q);
			$this->parameters[$n] = $v;
		}
	}

	public function encodeQueryString() {
		$tmp = array();
		foreach($this->parameters as $n => $v) {
			$tmp[] ="{$n}=$v";
		}
		return implode('&', $tmp);
	}

	public function getURI() {
		$ret = $this->uri;
		if (count($this->parameters)) {
			$ret .= '?' . $this->encodeQueryString();
		}

		return $ret;
	}
}