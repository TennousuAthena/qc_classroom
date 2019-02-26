<?php
/**
 * Created by lixuan868686@163.com
 * User: lane
 * Date: 16/7/9
 * Time: 下午11:30
 * E-mail: lixuan868686@163.com
 * WebSite: http://www.lanecn.com
 */
namespace MeepoPS\Core\Trident;

class Tool{
    public static function verifyAuth($token){
        return true;
    }

    public static function encodeTransferAddress($ip, $port){
        return base64_encode($ip . '_' . $port);
    }

    public static function decodeTransferAddress($key){
        $result = explode('_', base64_decode($key));
        return array('transfer_ip' => $result[0], 'transfer_port' => $result[1]);
    }

    public static function encodeClientId($transferIp, $transferPort, $connectId){
        return base64_encode($transferIp . '_' . $transferPort . '_' . $connectId);
    }

    public static function decodeClientId($clientId){
        $result = explode('_', base64_decode($clientId));
        return array('transfer_ip' => $result[0], 'transfer_port' => $result[1], 'connect_id' => $result[2]);
    }
}