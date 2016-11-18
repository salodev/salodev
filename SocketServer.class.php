<?php

class SocketServer {
    private $_status;
    private $_pidFile = '/tmp/SocketServer.pid';
    private $_pidHijos = array();
    private $_padre = true;
    public $_pid = null;
    
    public function __construct() {
    }
    
    public function becomeDaemon() {
        return posix_getpid();
    }
    
    public function setPidFile($pidFile) {
        $this->_pidFile = $pidFile;
    }
    
    public function openPidFile($file) {
        if(file_exists($file)){
            $fp = fopen($file, "r");
            $pid = fgets($fp, 1024);
            fclose($fp);
            if(posix_kill($pid, 0)){
                $this->logConsola("Server already running with PID: $pid");
                exit(1);
            }
            $this->logConsola("Removing PID file for defunct server process $pid");
            if(!unlink($file)){
                $this->logConsola("Cannot unlink PID file $file");
                exit(1);
            }
        }

		$fp = fopen($file, 'w');
        if(!$fp){
			throw new Exception("Unable to open PID file $file for writing...");
        }
        return $fp;
    }
    
    public function changeIidentity($uid, $gid){
        global $pidFile;
        if(!posix_setgid($gid)){
            $this->logConsola("Unable to setgid to $gid!");
            unlink($pidFile);
            exit;
        }
        if(!posix_setuid($uid)){
            $this->logConsola("Unable to setuid to $uid!");
            unlink($pidFile);
            exit;
        }
    }
    
    public function sigHandler($signo){
        switch($signo){
            case SIGTERM:
            case SIGINT:
                if ($this->_padre) {
                    if ($signo == SIGTERM) {
                        $this->logConsola("SIGTERM recibido.");
                    } else {
                        $this->logConsola("SIGINT recibido.");
                    }
                    foreach($this->_pidHijos as $pidHijo) {
                        $out = "Enviando SIGTERM al hijo #{$pidHijo}...";
                        $killed = posix_kill($pidHijo, SIGTERM);
                        $out .= $killed ? "OK" : "ERROR";
                        $this->logConsola($out);
                    }
                }
                exit(0);
                break;
            case SIGCHLD:
                if ($this->_padre) {
                    $this->logConsola("SIGCHLD recibido.");

                    $pid = pcntl_waitpid(-1, $this->_status, WUNTRACED);
                    $this->logConsola('PID: ' . $pid);
                    foreach($this->_pidHijos as $k => $pidHijo) {
                        if ($pidHijo==$pid) {
                            unset($this->_pidHijos[$k]);
                            break;
                        }
                    }
                }
                break;
            default:
                // not implemented yet...
                break;
        }
    }
    
    public function logConsola($texto) {
        echo date('Y-m-d H:i:s') . " {$texto}\n";
    }
    
    public function listen($address, $port, $fn) {
        set_time_limit(0);

        ob_implicit_flush();

        pcntl_signal(SIGCHLD, array($this, 'sigHandler'));
        pcntl_signal(SIGTERM, array($this, 'sigHandler'));
        pcntl_signal(SIGINT,  array($this, 'sigHandler'));

        $fh = $this->openPidFile($this->_pidFile);
        $socket = new Socket();
        try {
            $socket->create(AF_INET, SOCK_STREAM, 0);
        } catch (Exception $e) {
            $this->logConsola($e->getMessage());
        }
        $socket->setNonBlock();
        $socket->setOption(SOL_SOCKET, SO_REUSEADDR, 1);


        while(!$socket->bind($address, $port)){
            $this->logConsola("socket_bind() failed: reason: ".$socket->getErrorText());
            sleep(3);
        }

        if(($ret = $socket->listen(0)) < 0){
            $this->logConsola("socket_listen() failed: reason: ".$socket->getErrorText());
        }

        // change_identity($usuarioID, $grupoID);

        $this->logConsola("SERVIDOR LISTO. ESPERANDO CONEXIONES EN {$address}:{$port}");

        $pid = $this->becomeDaemon();
        fputs($fh, $pid);
        fclose($fh);
        declare(ticks = 1);

        while(true){
            $connection = $socket->accept();
            usleep(400);
            if (!$connection) {
                continue;
            }

            $pid = pcntl_fork();
            if ($pid) {
                $this->_pidHijos[] = $pid;
                //$quit++;
                //PABDR$EE


            } else {
                $connection->setNonBlock();
                $this->_padre = false;
                set_time_limit(0);
                $socket->close();
                
                // Invocamos la funcion de callback;
                $fn($connection);
                
                pcntl_wait($this->_status);
                exit(0);
                $connection->close();
                exit(0);
            }
            $connection->close();
        }
        if(posix_getpid() == $pid){
            unlink($this->_pidFile);
        }
    }
    
    public function addListener($address, $port, $fn){
        $socket = new Socket();
        $socket->create(AF_INET, SOCK_STREAM, 0);
        $socket->setNonBlock();
        $socket->setOption(SOL_SOCKET, SO_REUSEADDR, 1);
        
        $tries = 0;
        while(!$socket->bind($address, $port)){
            $tries++;
            if ($tries>10) {
                throw new Exception("Socket bind failed after {$tries} tries: " . $socket->getErrorText());
            }
            sleep(3);
        }

        if(($ret = $socket->listen(0)) < 0){
            throw new Exception("Socket Listen failed: " . $socket->getErrorText());
        }
        
        Worker::AddTask(function() use($socket, $fn) {
            $connection = $socket->accept();
            if (!$connection) {
                return;
            }
            
            $this->_pid = pcntl_fork();
            if ($this->_pid) {
                $this->_pidHijos[] = $this->_pid;
                //$quit++;
                //PABDR$EE
                $connection->close();
            } else {
                $connection->setNonBlock();
                $this->_padre = false;
                // $socket->close();
                
                // Invocamos la funcion de callback;
                $fn($connection);
                
                pcntl_wait($this->_status);
            }
        });
    }
    
    public function start() {
        set_time_limit(0);
        ob_implicit_flush();

        pcntl_signal(SIGCHLD, array($this, 'sigHandler'));
        pcntl_signal(SIGTERM, array($this, 'sigHandler'));
        pcntl_signal(SIGINT,  array($this, 'sigHandler'));

        $fh = $this->openPidFile($this->_pidFile);

        $pid = $this->becomeDaemon();
        fputs($fh, $pid);
        fclose($fh);
        declare(ticks = 1);

        $this->logConsola("SERVER READY. WAITING FOR CONNECTIONS");
        Worker::Start();
        if(posix_getpid() == $pid){
            unlink($this->_pidFile);
        }
    }
}