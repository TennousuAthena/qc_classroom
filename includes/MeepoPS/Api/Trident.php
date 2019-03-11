<?php
/**
 * API - 三层模型
 * Created by Lane
 * User: lane
 * Date: 16/3/23
 * Time: 下午2:12
 * E-mail: lixuan868686@163.com
 * WebSite: http://www.lanecn.com
 */
namespace MeepoPS\Api;

use MeepoPS\Core\Log;
use MeepoPS\Core\Trident\Confluence;
use MeepoPS\Core\Trident\Business;
use MeepoPS\Core\Trident\Transfer;

class Trident
{
    //Confluence层的相关配置
    public $confluenceIp = '0.0.0.0';
    public $confluencePort = '19911';
    public $confluenceInnerIp = '127.0.0.1';
    public $confluenceName = 'MeepoPS-Trident-Confluence';
    private $_confluenceChildProcessCount = 1;

    //Transfer层的相关配置
    private $_transferHost;
    private $_transferPort;
    public $transferChildProcessCount = 1;
    //Transfer回复数据给客户端的时候转码函数
    public $transferEncodeFunction;
    //Transfer的内网IP和端口, Business要用这个IP和端口链接到Transfer
    public $transferInnerIp = '0.0.0.0';
    public $transferInnerPort = '19912';

    //Business层的相关配置
    public $businessChildProcessCount = 1;
    public $businessName = 'MeepoPS-Trident-Business';

    private $_contextOptionList = array();
    private $_transferApiName = '';
    private $_container = '';

    public static $callbackList = array();
    private $_transferApiPropertyAndMethod = array();

    public static $innerProtocol = 'telnetjson';
    
    /**
     * Trident constructor.
     * @param string $apiName string Api类名
     * @param string $host string 需要监听的地址
     * @param string $port string 需要监听的端口
     * @param array $contextOptionList
     * @param string $container
     */
    public function __construct($apiName, $host, $port, $contextOptionList = array(), $container='')
    {
        //参数合法性校验
        $container = strtolower($container);
        if($container && (!in_array($container, array('confluence', 'business', 'transfer')))){
            Log::write('Container must is confluence | business | transfer', 'FATAL');
        }
        //如果是启动Transfer或者全部启动时, 需要判断参数
        $apiName = $apiName ? '\MeepoPS\Api\\' . ucfirst($apiName) : '';
        if($container != 'confluence' && $container != 'business'){
            if (!$apiName || !$host || !$port) {
                Log::write('$apiName and $host and $port can not be empty.', 'FATAL');
            }
            //接口是否存在
            if(!class_exists($apiName)){
                Log::write('Api class not exists. api=' . $apiName, 'FATAL');
            }
        }
        $this->_transferApiName = $apiName;
        $this->_transferHost = $host;
        $this->_transferPort = $port;
        $this->_container = strtolower($container);
        $this->_contextOptionList = $contextOptionList;
    }

    /**
     * 启动三层模型
     */
    public function run(){
        //根据容器选项启动, 如果为空, 则全部启动
        switch($this->_container){
            case 'confluence':
                $this->_initConfluence();
                break;
            case 'transfer':
                $this->_initTransfer();
                break;
            case 'business':
                $this->_initBusiness();
                break;
            default:
                $this->_initConfluence();
                echo "MeepoPS Confluence Start: \033[40G[\033[49;32;5mOK\033[0m]\n";
                $this->_initTransfer();
                echo "MeepoPS Transfer Start: \033[40G[\033[49;32;5mOK\033[0m]\n";
                $this->_initBusiness();
                echo "MeepoPS Business Start: \033[40G[\033[49;32;5mOK\033[0m]\n";
                break;
        }
    }

    /**
     * 魔术方法。所有不可访问的、不存在的属性, 统统赋值给Transfer所使用的API类
     * __set
     * @param $name
     * @param $value
     */
    public function __set($name, $value){
        //四个回调函数需要单独收集, 其他的和普通属性一样, 直接赋值给API类
        if(in_array($name, array('callbackStartInstance', 'callbackConnect', 'callbackNewData', 'callbackConnectClose'))){
            self::$callbackList[$name] = $value;
        }else{
            $this->_transferApiPropertyAndMethod['property'][$name] = $value;
        }
    }

    public function __call($name, $arguments)
    {
        $this->_transferApiPropertyAndMethod['method'][$name] = $arguments;
    }


    private function _initConfluence(){
        $confluence = new Confluence(self::$innerProtocol, $this->confluenceIp, $this->confluencePort);
        $confluence->childProcessCount = $this->_confluenceChildProcessCount;
        $confluence->instanceName = $this->confluenceName;
    }

    private function _initTransfer(){
        $transfer = new Transfer($this->_transferApiName, $this->_transferHost, $this->_transferPort, $this->_contextOptionList);
        $transfer->innerIp = $this->transferInnerIp;
        $transfer->innerPort = $this->transferInnerPort;

        $transfer->encodeFunction = $this->transferEncodeFunction;

        $transfer->confluenceIp = $this->confluenceInnerIp;
        $transfer->confluencePort = $this->confluencePort;
        //设置API接口的属性
        if($this->_transferApiPropertyAndMethod['property']){
            foreach($this->_transferApiPropertyAndMethod['property'] as $methodName => $arguments){
                $transfer->setApiClassProperty($methodName, $arguments);
            }
        }
        //调用API接口的方法
        if(!empty($this->_transferApiPropertyAndMethod['method'])){
            foreach($this->_transferApiPropertyAndMethod['method'] as $methodName => $arguments){
                $transfer->callApiClassMethod($methodName, $arguments);
            }
        }
    }

    private function _initBusiness(){
        $business = new Business();
        $business->childProcessCount = $this->businessChildProcessCount;
        $business->instanceName = $this->businessName;

        $business->confluenceIp = $this->confluenceInnerIp;
        $business->confluencePort = $this->confluencePort;
    }
}
