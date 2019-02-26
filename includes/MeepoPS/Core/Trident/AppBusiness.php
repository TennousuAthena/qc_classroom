<?php
/**
 * 业务功能
 * Created by lixuan868686@163.com
 * User: lane
 * Date: 16/7/10
 * Time: 下午10:42
 * E-mail: lixuan868686@163.com
 * WebSite: http://www.lanecn.com
 */
namespace MeepoPS\Core\Trident;

use MeepoPS\Core\Log;

class AppBusiness{
    
    /**
     * 发送给所有人消息
     * @param $message
     * @return bool
     */
    public static function sendToAll($message){
        $message = BusinessAndTransferService::formatMessageToTransfer($message);
        $message['msg_type'] = MsgTypeConst::MSG_TYPE_SEND_ALL;
        foreach(BusinessAndTransferService::$transferList as $transfer){
            self::_send($transfer, $message);
        }
        return true;
    }

    /**
     * 给指定的某个人发送消息
     * @param $message
     * @param $clientId
     * @return bool
     */
    public static function sendToOne($message, $clientId){
        if(!$clientId){
            return false;
        }
        $clientDecodeId = Tool::decodeClientId($clientId);
        //选择Transfer
        $transferKey = Tool::encodeTransferAddress($clientDecodeId['transfer_ip'], $clientDecodeId['transfer_port']);
        if(!isset(BusinessAndTransferService::$transferList[$transferKey])){
            Log::write('AppBusiness: choice transfer failed', 'warning');
            return false;
        }
        $transfer = BusinessAndTransferService::$transferList[$transferKey];
        //整理消息格式
        $message = BusinessAndTransferService::formatMessageToTransfer($message);
        $message['msg_type'] = MsgTypeConst::MSG_TYPE_SEND_ONE;
        //发送给谁的在Transfer的链接的ID
        $message['to_client_connect_id'] = $clientDecodeId['connect_id'];
        return self::_send($transfer, $message);
    }

    /**
     * 给自己发送消息
     * @param $message
     * @return bool
     */
    public static function sendToMe($message){
        return self::sendToOne($message, $_SERVER['MEEPO_PS_CLIENT_UNIQUE_ID']);
    }

    /**
     * 发送操作
     * @param $connect
     * @param $data
     * @return bool|int
     */
    private static function _send($connect, $data){
        return $connect->send($data);
    }
}