<?php
/**
 * Created by lixuan868686@163.com
 * User: lane
 * Date: 16/7/9
 * Time: 下午6:19
 * E-mail: lixuan868686@163.com
 * WebSite: http://www.lanecn.com
 */
namespace MeepoPS\Core\Trident;

use MeepoPS\Api\Trident;
use MeepoPS\Core\Log;
use MeepoPS\Core\Timer;
use MeepoPS\Library\TcpClient;

class BusinessAndConfluenceService{

    public $confluenceIp;
    public $confluencePort;

    private $_confluence;
    private $_businessAndTranferService;

    public function __construct(){
        $this->_businessAndTranferService = new BusinessAndTransferService();
    }

    /**
     * 向中心机(Confluence层)通知自己, 表示新的Business进程已经上线, 并定时获得Confluence推送的消息。
     */
    public function connectConfluence(){
        $this->_confluence = new TcpClient(Trident::$innerProtocol, $this->confluenceIp, $this->confluencePort, true);
        //实例化一个空类
        $this->_confluence->instance = new \stdClass();
        $this->_confluence->instance->callbackNewData = array($this, 'callbackConfluenceNewData');
        $this->_confluence->instance->callbackConnectClose = array($this, 'callbackConfluenceConnectClose');
        $this->_confluence->confluence = array();
        $this->_confluence->connect();
        $result = $this->_confluence->send(array('token'=>'', 'msg_type'=>MsgTypeConst::MSG_TYPE_ADD_BUSINESS_TO_CONFLUENCE));
        if($result === false){
            Log::write('Business: add confluence failed.' . 'WARNING');
            $this->_closeConfluence();
        }
    }

    /**
     * 收到Confluence发来的消息时
     * @param $connect
     * @param $data
     */
    public function callbackConfluenceNewData($connect, $data){
        switch($data['msg_type']){
            case MsgTypeConst::MSG_TYPE_ADD_BUSINESS_TO_CONFLUENCE:
                $this->_addConfluenceResponse($connect, $data);
                break;
            case MsgTypeConst::MSG_TYPE_PING:
                $this->_receivePingFromConfluence($connect, $data);
                break;
            case MsgTypeConst::MSG_TYPE_RESET_TRANSFER_LIST:
                $this->_businessAndTranferService->resetTransferList($data);
                break;
            default:
                Log::write('Business: Confluence message type is not supported, data=' . json_encode($data) . ', client address: ' . json_encode($connect->getClientAddress()), 'ERROR');
                $this->_closeConfluence();
                return;
        }
    }

    /**
     * 收到Confluence发来的加入Confluence确认信息
     * 如果Confluence确认, 则增加定时器, 检测是否正常收到PING。如果不正常, 则尝试重连。检测频率和Confluence发送PING的频率一样
     * @param $connect
     * @param $data
     */
    private function _addConfluenceResponse($connect, $data){
        //链接失败
        if($data['msg_content'] !== 'OK'){
            $this->_closeConfluence();
            return;
        }
        //链接成功
        $this->_confluence->confluence['confluence_no_ping_limit'] = 0;
        //添加计时器, 如果一定时间内没有收到中心机发来的PING, 则断开本次链接并重新向中心机发起注册
        $this->_confluence->confluence['waiter_confluence_ping_timer_id'] = Timer::add(function(){
            if((++$this->_confluence->confluence['confluence_no_ping_limit']) >= MEEPO_PS_TRIDENT_SYS_PING_NO_RESPONSE_LIMIT){
                //断开链接
                $this->_closeConfluence();
            }
        }, array(), MEEPO_PS_TRIDENT_SYS_PING_INTERVAL);
        Log::write('Business: add Confluence success. ' . $this->confluenceIp . ':' . $this->confluencePort);
    }

    /**
     * 收到Confluence发来的PING消息
     * 收到PING后, 将没有收到PING的次数-1
     * @param $connect
     * @param $data
     */
    private function _receivePingFromConfluence($connect, $data){
        if($data['msg_content'] !== 'PING'){
            return;
        }
        if($this->_confluence->confluence['confluence_no_ping_limit'] >= 1){
            $this->_confluence->confluence['confluence_no_ping_limit']--;
        }
        $connect->send(array('msg_type'=>MsgTypeConst::MSG_TYPE_PONG, 'msg_content'=>'PONG'));
    }

    /**
     * 如果Business和Confluence的链接断开, 则尝试重连
     */
    public function callbackConfluenceConnectClose(){
        Timer::add(array($this, 'connectConfluence'), array(), 1, false);
    }

    /**
     * 重新连入Confluence
     * 包含断开链接,并且重新链接。
     * 调用此方法时自动调用_closeConfluence()方法
     */
    private function _reConnectConfluence(){
        $this->_closeConfluence();
        $this->connectConfluence();
    }

    /**
     * 断开和Confluence的链接
     */
    private function _closeConfluence(){
        if(isset($this->_confluence->confluence['waiter_confluence_ping_timer_id'])){
            Timer::delOne($this->_confluence->confluence['waiter_confluence_ping_timer_id']);
        }
        if (method_exists($this->_confluence, 'close')) {
            $this->_confluence->close();
        }
    }
}