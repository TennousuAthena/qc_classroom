<?php
/**
 * 基于TCP的客户端链接
 * 本函数参考Workerman实现
 * Created by lixuan868686@163.com
 * User: lane
 * Date: 16/6/27
 * Time: 下午4:40
 * E-mail: lixuan868686@163.com
 * WebSite: http://www.lanecn.com
 */
namespace MeepoPS\Library;

use MeepoPS\Core\Event\EventInterface;
use MeepoPS\Core\Log;
use MeepoPS\Core\MeepoPS;
use MeepoPS\Core\TransportProtocol\Tcp;

class TcpClient extends Tcp{

    public $callbackConnect;
    public $host;
    public $port;

    private $_protocol;
    private $_isAsync;

    /**
     * TcpClient constructor.
     * @param string $protocol
     * @param string $host
     * @param string $port
     * @param bool $isAsync
     */
    public function __construct($protocol, $host, $port, $isAsync=false){
        //传入的协议是应用层协议还是传输层协议
        $protocol = '\MeepoPS\Core\ApplicationProtocol\\' . ucfirst($protocol);
        if($protocol){
            if (class_exists($protocol)) {
                $this->_protocol = '\MeepoPS\Core\ApplicationProtocol\\' . $protocol;
                $this->_applicationProtocolClassName = $protocol;
            } else {
                Log::write('Application layer protocol class not found. portocol:' . $protocol, 'FATAL');
            }
        }

        //属性赋值
        $this->host = $host;
        $this->port = $port;
        $this->id = self::$_recorderId++;
        $this->_isAsync = $isAsync ? STREAM_CLIENT_ASYNC_CONNECT : STREAM_CLIENT_CONNECT;
        $this->_currentStatus = self::CONNECT_STATUS_CONNECTING;
        //更改统计信息
        self::$statistics['total_connect_count']++;
        self::$statistics['current_connect_count']++;
    }

    public function connect(){
        $remoteSocket = 'tcp://' . $this->host . ':' . $this->port;
        $this->_connect = stream_socket_client($remoteSocket, $errno, $errmsg, 5, $this->_isAsync);
        if(!$this->_connect){
            Log::write('TcpClient link to '.$remoteSocket.' failed.', 'ERROR');
            $this->_currentStatus = self::CONNECT_STATUS_CLOSED;
            return;
        }
        //监听此链接
        MeepoPS::$globalEvent->add(array($this, 'checkConnection'), array(), $this->_connect, EventInterface::EVENT_TYPE_WRITE);
    }

    /**
     * @param $tcpConnect resource TCP链接
     */
    public function checkConnection($tcpConnect){
        if(!stream_socket_get_name($tcpConnect, true)){
            $this->destroy();
            Log::write('Get Socket name found socket resource is invalid.', 'ERROR');
            return;
        }
        MeepoPS::$globalEvent->delOne($tcpConnect, EventInterface::EVENT_TYPE_WRITE);
        stream_set_blocking($tcpConnect, 0);
        if (function_exists('socket_import_stream')) {
            $socket = socket_import_stream($tcpConnect);
            @socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1);
            @socket_set_option($socket, SOL_TCP, TCP_NODELAY, 1);
        }
        MeepoPS::$globalEvent->add(array($this, 'read'), array(), $tcpConnect, EventInterface::EVENT_TYPE_READ);
        if($this->_sendBuffer){
            MeepoPS::$globalEvent->add(array($this, 'sendEvent'), array(), $tcpConnect, EventInterface::EVENT_TYPE_WRITE);
        }
        $this->_currentStatus = self::CONNECT_STATUS_ESTABLISH;
        $this->_clientAddress = stream_socket_get_name($tcpConnect, true);
    }
}