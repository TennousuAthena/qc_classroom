<?php
/**
 * 汇聚管理层
 * 集中管理Transfer和Business的在线/离线状态。提供离线踢出, 上线推送等功能。
 * Created by lixuan868686@163.com
 * User: lane
 * Date: 16/6/29
 * Time: 下午3:20
 * E-mail: lixuan868686@163.com
 * WebSite: http://www.lanecn.com
 */
namespace MeepoPS\Core\Trident;

use MeepoPS\Core\Log;
use MeepoPS\Core\MeepoPS;
use MeepoPS\Core\Timer;

class Confluence extends MeepoPS{

    //所有的Business列表
    private $_businessList = array();
    //所有的Transfer列表
    private $_transferList = array();

    public function __construct($protocol, $host, $port, array $contextOptionList=array())
    {
        $this->callbackStartInstance = array($this, 'callbackConfluenceStartInstance');
        $this->callbackConnect = array($this, 'callbackConfluenceConnect');
        $this->callbackNewData = array($this, 'callbackConfluenceNewData');
        $this->callbackConnectClose = array($this, 'callbackConfluenceConnectClose');
        parent::__construct($protocol, $host, $port, $contextOptionList);
    }

    /**
     * 回调函数 - 进程启动时
     * 设置定时器, 每段时间强制发送所有的Transfer给所有的Business
     */
    public function callbackConfluenceStartInstance(){
        //设置定时器, 每段时间强制发送所有的Transfer给所有的Business
        Timer::add(function (){
            $this->broadcastToBusiness();
        }, array(), MEEPO_PS_TRIDENT_SYS_CONFLUENCE_BROADCAST_INTERVAL);
    }

    /**
     * 定时器广播给Business。
     * 定时向所有Business更新一次全量的Transfer
     */
    public function timerBroadcastToBusiness(){
        $message = array();
        $message['msg_type'] = MsgTypeConst::MSG_TYPE_RESET_TRANSFER_LIST;
        $message['msg_content']['transfer_list'] = $this->_transferList;
        foreach($this->_businessList as $business){
            $business->send($message);
        }
    }
    
    /**
     * 回调函数 - 收到新链接时
     * 新链接加入时, 先不做处理, 等待token验证通过后再处理
     * token的验证是收到token后校验, 因此会进入callbackConfluenceNewData方法中
     * 再此处加入一次性的定时器, 如果N秒后仍然未通过验证, 则断开链接。
     * @param $connect
     */
    public function callbackConfluenceConnect($connect){
        $connect->confluence['waiter_verify_timer_id'] = Timer::add(function ($connect){
            Log::write('Confluence: Wait for token authentication timeout. client address: ' . json_encode($connect->getClientAddress()), 'ERROR');
            $this->_close($connect);
        }, array($connect), MEEPO_PS_TRIDENT_SYS_WAIT_VERIFY_TIMEOUT, false);
    }

    /**
     * 回调函数 - 收到新消息时
     * 只接受新增Transfer、新增Business、PONG三种消息
     * @param $connect
     * @param $data
     */
    public function callbackConfluenceNewData($connect, $data){
        switch($data['msg_type']){
            //新的Transfer加入
            case MsgTypeConst::MSG_TYPE_ADD_TRANSFER_TO_CONFLUENCE:
                //token校验
                if(!isset($data['token']) || Tool::verifyAuth($data['token']) !== true){
                    Log::write('Confluence: New link token validation failed, client address: ' . json_encode($connect->getClientAddress()), 'ERROR');
                    $this->_close($connect);
                    return;
                }
                $result = $this->_addTransfer($connect, $data);
                if($result){
                    //删除等待校验超时的定时器
                    Timer::delOne($connect->confluence['waiter_verify_timer_id']);
                }else{
                    Log::write('Confluence: _addTransfer return result: ' . $result . ', client address: ' . json_encode($connect->getClientAddress()), 'ERROR');
                }
                break;
            //新的Business加入
            case MsgTypeConst::MSG_TYPE_ADD_BUSINESS_TO_CONFLUENCE:
                //token校验
                if(!isset($data['token']) || Tool::verifyAuth($data['token']) !== true){
                    Log::write('Confluence: New link token validation failed, client address: ' . json_encode($connect->getClientAddress()), 'ERROR');
                    $this->_close($connect);
                    return;
                }
                $result = $this->_addBusiness($connect, $data);
                if($result){
                    //删除等待校验超时的定时器
                    Timer::delOne($connect->confluence['waiter_verify_timer_id']);
                }else{
                    Log::write('Confluence: _addBusiness return result: ' . $result . ', client address: ' . json_encode($connect->getClientAddress()), 'ERROR');
                }
                break;
            //PONG
            case MsgTypeConst::MSG_TYPE_PONG:
                $this->_pong($connect, $data);
                break;
            default:
                Log::write('Confluence: New link message type is not supported, client address: ' . json_encode($connect->getClientAddress()) .', data=' . json_encode($data), 'ERROR');
                $this->_close($connect);
                return;
        }
    }

    /**
     * 回调函数 - 断开链接时
     * @param $connect
     */
    public function callbackConfluenceConnectClose($connect){
        if(isset($this->_transferList[$connect->id])){
            unset($this->_transferList[$connect->id]);
            $this->broadcastToBusiness();
        }else{
            unset($this->_businessList[$connect->id]);       
        }
    }

    /**
     * 新增一个Transfer
     * @param $connect
     * @param $data
     * @return bool
     */
    private function _addTransfer($connect, $data){
        if(empty($data['msg_content']['ip']) || empty($data['msg_content']['port'])) {
            return false;
        }
        $this->_transferList[$connect->id] = array(
            'ip' => $data['msg_content']['ip'],
            'port' => $data['msg_content']['port'],
        );
        //初始化发送PING未收到PONG的次数
        $connect->confluence['ping_no_response_count'] = 0;
        //设定PING的定时器
        $connect->confluence['ping_timer_id'] = Timer::add(function ($connect){
            $connect->send(array('msg_type'=>MsgTypeConst::MSG_TYPE_PING, 'msg_content'=>'PING'));
        }, array($connect), MEEPO_PS_TRIDENT_SYS_PING_INTERVAL);
        //检测PING回复情况
        $connect->confluence['check_ping_timer_id'] = Timer::add(array($this, 'checkPingLimit'), array($connect), MEEPO_PS_TRIDENT_SYS_PING_INTERVAL);
        $this->broadcastToBusiness();
        //告知对方, 已经收到消息, 并且已经添加成功了
        return $connect->send(array('msg_type'=>MsgTypeConst::MSG_TYPE_ADD_TRANSFER_TO_CONFLUENCE, 'msg_content'=>'OK'));
    }

    /**
     * 新增一个Business
     * @param $connect
     * @param $data
     * @return bool
     */
    private function _addBusiness($connect, $data){
        $connect->confluence['ping_no_response_count'] = 0;
        $this->_businessList[$connect->id] = $connect;
        //设定PING的定时器
        $connect->confluence['ping_timer_id'] = Timer::add(function ($connect){
            $connect->send(array('msg_type'=>MsgTypeConst::MSG_TYPE_PING, 'msg_content'=>'PING'));
        }, array($connect), MEEPO_PS_TRIDENT_SYS_PING_INTERVAL);
        //检测PING回复情况
        $connect->confluence['check_ping_timer_id'] = Timer::add(array($this, 'checkPingLimit'), array($connect), MEEPO_PS_TRIDENT_SYS_PING_INTERVAL);
        $this->broadcastToBusiness($connect);
        //告知对方, 已经收到消息, 并且已经添加成功了
        return $connect->send(array('msg_type'=>MsgTypeConst::MSG_TYPE_ADD_BUSINESS_TO_CONFLUENCE, 'msg_content'=>'OK'));
    }

    /**
     * 接收到消息PONG
     * @param $connect
     * @param $data string
     */
    private function _pong($connect, $data){
        if($data['msg_content'] === 'PONG'){
            $connect->confluence['ping_no_response_count']--;
        }
    }


    /**
     * 检测PING的回复情况
     * @param $connect
     */
    public function checkPingLimit($connect){
        $connect->confluence['ping_no_response_count']++;
        //超出无响应次数限制时断开连接
        if( ($connect->confluence['ping_no_response_count'] - 1) >= MEEPO_PS_TRIDENT_SYS_PING_NO_RESPONSE_LIMIT){
            $conn = '';
            if(isset($this->_businessList[$connect->id])){
                $conn = $this->_businessList[$connect->id];
            }else if(isset($this->_transferList[$connect->id])){
                $conn = $this->_transferList[$connect->id];
            }
            Log::write('Confluence: PING no response beyond the limit, has been disconnected, client address: ' . json_encode($connect->getClientAddress()), 'ERROR');
            $this->_close($connect);
        }
    }

    /**
     * 关闭连接
     * @param $connect
     */
    private function _close($connect){
        if(isset($connect->confluence['ping_timer_id'])){
            Timer::delOne($connect->confluence['ping_timer_id']);
        }
        if(isset($connect->confluence['check_ping_timer_id'])){
            Timer::delOne($connect->confluence['check_ping_timer_id']);
        }
        if(isset($connect->confluence['waiter_verify_timer_id'])){
            Timer::delOne($connect->confluence['waiter_verify_timer_id']);
        }
        if (method_exists($connect, 'close')) {
            $connect->close();
        }
    }

    /**
     * 给Business发送消息
     * @param null $connect resource
     */
    public function broadcastToBusiness($connect=null){
        $message = array();
        $message['msg_type'] = MsgTypeConst::MSG_TYPE_RESET_TRANSFER_LIST;
        $message['msg_content']['transfer_list'] = $this->_transferList;
        //新增Business时, 只给指定的Business发送
        if(!is_null($connect)){
            $connect->send($message);
            return;
        }
        //新增Transfer时, 给所有的Business发送
        foreach($this->_businessList as $business){
            $business->send($message);
        }
    }
}
