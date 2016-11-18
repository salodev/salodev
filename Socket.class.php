<?php

class Socket {
    private $resource = null;
    
    public function __construct($resource = null) {
        if ($resource !== null && !is_resource($resource)) {
            throw new Exception('Parameter given must be null or valid resource');
        }
        $this->resource = $resource;
    }
    
    public function create($domain = AF_INET, $type = SOCK_STREAM, $protocol = 0) {
        $resource = socket_create($domain, $type , $protocol);
        if (!is_resource($resource)) {
            throw new Exception($this->getErrorText(), $this->getLastError());
        }
        $this->resource = $resource;
    }
    
    public function getLastError() {
        return socket_last_error($this->resource);
    }
    
    public function getErrorText() {
        return socket_strerror($this->resource);
    }
    
    public function setNonBlock() {
        socket_set_nonblock($this->resource);
    }
    
    public function setBlock() {
        socket_set_block($this->resource);
    }
    
    public function setOption($level, $name, $val) {
        socket_set_option($this->resource, $level, $name, $val);
    }
    
    public function bind($address, $port) {
        return socket_bind($this->resource, $address, $port);
    }
    
    public function listen($backlog = 0) {
        return socket_listen($this->resource, $backlog);
    }
    
    public function accept() {
        $newResource = socket_accept($this->resource);
        if (!$newResource) {
            return false;
        }
        $newSocket = new Socket($newResource);
        return $newSocket;
    }
    
    public function close() {
        socket_close($this->resource);
    }
    
    public function read($length, $type = PHP_BINARY_READ) {
        return socket_read($this->resource, $length, $type);
    }
    
    public function readLine($maxIterations = 1000){
        $i = 0;
        $content = '';
        while(false !== ($buffer = $this->read(512)) && $i <= $maxIterations) {
            $i++;
            $content .= $buffer;
            if (strpos($content, "\n") !== false || strpos($content, "\r") !== false) {
                return $content;
            }
        }
    }
    
    public function write($buffer, $length = null) {
        if ($length===null) {
            $length = strlen($buffer);
        }
        return socket_write($this->resource, $buffer, $length);
    }
}