<?php
namespace salodev;

class Process {
    private $_stream;
    private $_command;
    private $_pipes;
    public function __construct($command, $wd, $env = array()) {
        $this->_command = $command;
        $this->_stream = proc_open($this->_command, array(
           0 => array('pipe', 'r'),
           1 => array('pipe', 'w'),
           2 => array('pipe', 'w'),
        ), $this->_pipes, $wd, $env);
        if (!is_resource($this->_stream)) {
            throw new \Exception('Proccess can not be started.');
        }
        stream_set_blocking($this->_pipes[0], 0);
        stream_set_blocking($this->_pipes[1], 0);
        stream_set_blocking($this->_pipes[2], 0);
    }
    public function write($string) {
        fwrite($this->_pipes[0], $string);
    }
    public function read($length = 256) {
        $buffer = '';
        while($read = fread($this->_pipes[1], $length)) {
            $buffer .= $read;
        }
        return $buffer;
    }
    public function readError($length = 256) {
        $buffer = '';
        while($read = fread($this->_pipes[2], $length)) {
            $buffer .= $read;
        }
        return $buffer;
    }
    public function terminate($signal = 15) {
        proc_terminate($this->_stream, $signal);
    }
    public function getStatus() {
        return proc_get_status($this->_stream);
    }
    public function close(){
        proc_close($this->_stream);
    }
    public function isRunning() {
        $status = $this->getStatus();
        return $status['running'];
    }
    
}