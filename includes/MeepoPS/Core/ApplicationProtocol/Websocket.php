<?php
/**
 * 从TCP数据流中解析WebSocket协议
 * WebSocket协议为Version 13
 * Created by Lane
 * User: lane
 * Date: 16/3/26
 * Time: 下午10:27
 * E-mail: lixuan868686@163.com
 * WebSite: http://www.lanecn.com
 */
namespace MeepoPS\Core\ApplicationProtocol;

use MeepoPS\Core\TransportProtocol\TransportProtocolInterface;
use MeepoPS\Core\Log;

class Websocket implements ApplicationProtocolInterface
{
    //基础头长, 既1位的fin + 3位RSV + 4位opcode + 1位mask + 7位payloadLen + 32位maskingKey = 48位 = 6字节
    const BASE_HEADER_LENGTH = 6;

    /**
     * 检测数据, 返回数据包的长度.
     * 没有数据包或者数据包未结束,则返回0
     * @param string $data 数据包
     * @param TransportProtocolInterface $connect 基于传输层协议的链接
     * @return bool|int
     */
    public static function input($data, TransportProtocolInterface $connect)
    {
        //数据长度
        $dataLength = strlen($data);
        //头部未完 (当前帧未完)
        if($dataLength < self::BASE_HEADER_LENGTH){
            return 0;
        }
        if($dataLength === 0){
            //断开连接
            self::_disConnect($connect);
            return 0;
        }
        //是否已经握手 - websocket底层仍旧是HTTP协议.首次建立链接仍旧需要握手
        if (!isset($connect->isHandshake) || $connect->isHandshake !== true) {
            self::_handshake($data, $connect);
            return 0;
        }
        //已完成握手后
        $allFrameLength = 0;
        $connect->wsPackageFrameList = array();
        while(true){
            $frameLength = self::_unWrap($allFrameLength, $data, $connect, $dataLength);
            //不需要后续处理的
            if($frameLength === null){
                return 0;
            }
            //有不完整的帧
            if($frameLength === 0){
                return 0;
            }
            //出错了
            if($frameLength === false){
                return 0;
            }
            //包已完整(有fin=1的帧)
            if($frameLength === true){
                return $allFrameLength;
            }
            $data = substr($data, $frameLength);
        }
        return 0;
    }

    /**
     * 数据编码.在发送数据前调用此方法.
     * @param string $data 数据包
     * @param TransportProtocolInterface $connect 基于传输层协议的链接
     * @return string
     */
    public static function encode($data, TransportProtocolInterface $connect)
    {
        $dataLength = strlen($data);
        if ($dataLength <= 125) {
            $ret = chr(0x81) . chr($dataLength) . $data;
        } else if ($dataLength <= 0xFFFF) {
            $ret = chr(0x81) . chr(0x7E) . pack('n', $dataLength) . $data;
        } else {
            $ret = chr(0x81) . chr(0x7F) . pack('xxxxN', $dataLength) . $data;
        }
        return $ret;
    }

    /**
     * 数据解码.在接收数据前调用此方法
     * @param string $data 单个完整的数据包 - 不使用,因为为减少计算,在input的时候将包的所有帧都整理到了一个数组中
     * @param TransportProtocolInterface $connect 基于传输层协议的链接
     * @return string
     */
    public static function decode($data, TransportProtocolInterface $connect)
    {
        $oriData = '';
        foreach($connect->wsPackageFrameList as $frame){
            //获取原始数据
            $oriData .= $frame['data'];
        }
        unset($connect->wsPackageFrameList);
        return $oriData;
    }

    /**
     * 进行首次建立链接的握手
     * @param string $data
     * @param TransportProtocolInterface $connect 基于传输层协议的链接
     */
    private static function _handshake($data, TransportProtocolInterface $connect)
    {
        //如果是policy-file-request. 在与Flash通信时, 三次握手之后客户端会发送一个<policy-file-request>
        if (strpos($data, '<policy') === 0) {
            $xml = '<?xml version="1.0"?><cross-domain-policy><site-control permitted-cross-domain-policies="all"/><allow-access-from domain="*" to-ports="*"/></cross-domain-policy>' . "\0";
            $connect->send($xml);
            $connect->substrReadData(strlen($data));
            return;
        }
        //只能是GET, 不支持的其他类型
        if (strpos($data, 'GET') !== 0) {
            // Bad websocket handshake request.
            $connect->send("HTTP/1.1 400 Bad Request\r\n\r\nHandshake failed. Because only supports GET HTTP method");
            self::_disConnect($connect);
            return;
        }
        //开始处理正常的请求.即HTTP协议头的GET请求
        //head和body是用\r\n\r\n来分割的,获取head结束的位置
        if (!strpos($data, "\r\n\r\n")) {
            return;
        }
        //解析HTTP协议头
        self::_parseHttpHeader($data, $connect);
        //WebSocket版本不能低于13
        if(empty($_SERVER['Sec-WebSocket-Version']) || $_SERVER['Sec-WebSocket-Version'] < 13){
            $connect->send("HTTP/1.1 400 Bad Request\r\n\r\nSec-WebSocket-Version can not be less than 13", true);
            self::_disConnect($connect);
            return;
        }
        //校验Sec-WebSocket-Key
        if(empty($_SERVER['Sec-WebSocket-Key'])){
            $connect->send("HTTP/1.1 400 Bad Request\r\n\r\nSec-WebSocket-Key not exists", true);
            self::_disConnect($connect);
            return;
        }
        $key = base64_encode(sha1($_SERVER['Sec-WebSocket-Key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        //整理需要返回的握手的响应信息
        $response = "HTTP/1.1 101 Switching Protocols\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nServer: MeepoPS/" . MEEPO_PS_VERSION . "\r\n";
        $response .= "Sec-WebSocket-Accept: $key\r\nSec-WebSocket-Version: 13\r\n\r\n";
        //消费掉数据流中握手的部分
        $connect->substrReadData(strlen($data));
        $connect->send($response, false);
        $connect->isHandshake = true;
    }

    /**
     * 协议HTTP协议头
     * @param $data
     * @param TransportProtocolInterface $connect 基于传输层协议的链接
     */
    private static function _parseHttpHeader($data, TransportProtocolInterface $connect)
    {
        //将超全局变量设为空.
        $_POST = $_GET = $_COOKIE = $_REQUEST = $_SESSION = $_FILES = $GLOBALS['HTTP_RAW_POST_DATA'] = array();
        $_SERVER = array();
        //解析HTTP头
        $http = explode("\r\n\r\n", $data, 2);
        $headerList = explode("\r\n", $http[0]);

        // ---------- 填充$_SERVER ----------
        //HTTP协议开头第一行
        list($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $_SERVER['SERVER_PROTOCOL']) = explode(' ', $headerList[0]);
        //客户端IP和端口
        $clientAddress = $connect->getClientAddress();
        $_SERVER['REMOTE_ADDR'] = $clientAddress[0];
        $_SERVER['REMOTE_PORT'] = $clientAddress[1];
        $_SERVER['SERVER_SOFTWARE'] = 'MeepoPS/' . MEEPO_PS_VERSION . '( ' . PHP_OS . ' ) PHP/' . PHP_VERSION;
        $_SERVER['REQUEST_SCHEME'] = 'Websocket';
        $_SERVER['QUERY_STRING'] = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        unset($headerList[0]);
        //循环剩下的HTTP头信息
        foreach ($headerList as $header) {
            if (empty($header)) {
                continue;
            }
            //将一条头分割为名字和值
            $header = explode(':', $header, 2);
            $name = trim(strtolower($header[0]));
            $value = trim($header[1]);
            switch ($name) {
                case 'host':
                    $_SERVER['HTTP_HOST'] = $value;
                    $value = explode(':', $value);
                    $_SERVER['SERVER_NAME'] = $value[0];
                    if (isset($value[1])) {
                        $_SERVER['SERVER_PORT'] = $value[1];
                    }
                    break;
                case 'user-agent':
                    $_SERVER['HTTP_USER_AGENT'] = $value;
                    break;
                case 'accept':
                    $_SERVER['HTTP_ACCEPT'] = $value;
                    break;
                case 'accept-charset':
                    $_SERVER['HTTP_ACCEPT_CHARSET'] = $value;
                    break;
                case 'accept-language':
                    $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $value;
                    break;
                case 'accept-encoding':
                    $_SERVER['HTTP_ACCEPT_ENCODING'] = $value;
                    break;
                case 'connection':
                    $_SERVER['HTTP_CONNECTION'] = $value;
                    break;
                case 'cache_control':
                    $_SERVER['CACHE_CONTROL'] = $value;
                    break;
                case 'cookie':
                    $_SERVER['HTTP_COOKIE'] = $value;
                    $cookieList = explode(';', $value);
                    foreach($cookieList as $cookie){
                        $cookie = explode('=', $cookie);
                        if(count($cookie) === 2){
                            $_COOKIE[trim($cookie[0])] = trim($cookie[1]);
                        }
                    }
                    unset($cookieList, $cookie);
                    break;
                case 'sec-websocket-key':
                    $_SERVER['Sec-WebSocket-Key'] = $value;
                    break;
                case 'sec-websocket-version':
                    $_SERVER['Sec-WebSocket-Version'] = $value;
                    break;
                case 'sec-websocket-extensions':
                    $_SERVER['Sec-WebSocket-Extensions'] = $value;
                    break;
                case 'sec-websocket-protocol':
                    $_SERVER['Sec-WebSocket-Protocol'] = $value;
                    break;
                case 'origin':
                    $_SERVER['HTTP_ORIGIN'] = $value;
                    break;
                case 'upgrade':
                    $_SERVER['UPGRADE'] = $value;
                    break;
                case 'pragma':
                    $_SERVER['PRAGMA'] = $value;
                    break;
                case 'content-length':
                    $_SERVER['Content-Length'] = strlen($data);
                    break;
                case 'content-type':
                    $_SERVER['CONTENT_TYPE'] = $value;
                    break;
                case 'referer':
                    $_SERVER['HTTP_REFERER'] = $value;
                    break;
                case 'if-modified-since':
                    $_SERVER['HTTP_IF_MODIFIED_SINCE'] = $value;
                    break;
                case 'if-none-match':
                    $_SERVER['HTTP_IF_NONE_MATCH'] = $value;
                    break;
            }
        }
        unset($name, $value, $header, $headerList, $http);

        //GET
        parse_str($_SERVER['QUERY_STRING'], $_GET);

        //REQUEST
        $_REQUEST = array_merge($_GET, $_POST);
    }

    /**
     * 解包 计算帧长度, 所有帧长度, 获取原始数据
     * @param $allFrameLength
     * @param $data
     * @param $connect
     * @param $dataLength
     * @return bool|int 0则说明数据不完整需要等待, false表示错误, true表示数据包完整, 大于0的int表示数据中第一个完整帧的大小
     */
    private static function _unWrap(&$allFrameLength, $data, $connect, $dataLength){
        //第一个字节
        $firstByte = ord($data[0]);
        //fin, 标志是否是本次数据包的最后一帧, 最后一帧为1, 否则为0
        $fin = $firstByte >> 7;
        //本次请求的操作
        $opcode = $firstByte & 0x0F;
        //数据长度
        $frameDataLength = $payLoadLen = ord($data[1]) & 0x7F;
        //是否有掩码
        $isMask = (ord($data[1]) & 0x80) >> 7;
        //协议规定, 客户端向服务器传输的数据帧必须进行掩码处理。服务器若接收到未经过掩码处理的数据帧，则必须主动关闭连接。
        if($isMask != 1){
            self::_disConnect($connect);
            return 0;
        }
        //执行opcode的操作
        $opCodeResult = self::_opCode($opcode, $connect, $payLoadLen);
        if($opCodeResult === false){
            return false;
        }
        //默认头长。 如果payloadLen小于126, 头长度是默认, $payLoadLen是就是数据大小
        $headerLength = self::BASE_HEADER_LENGTH;
        //如果payloadLen=126, 头长多2个字节
        if ($payLoadLen === 126) {
            $headerLength += 2;
            if ($headerLength > $dataLength) {
                return 0;
            }
            $frameDataLength = unpack('nlength', substr($data, 2, 2));
            $frameDataLength = $frameDataLength['length'];
            //如果payloadLen=127, 头长多8个字节
        }else if ($payLoadLen === 127){
            $headerLength += 8;
            if ($headerLength > $dataLength) {
                return 0;
            }
            $frameDataLength = unpack('N2', substr($data, 2, 8));
            $frameDataLength = $frameDataLength[1] * 4294967296 + $frameDataLength[2];
            //其他
        }else if($payLoadLen > 127){
            Log::write('WebSocket protocol received data errors, because the payloadlen is not correct. payloadlen='.$payLoadLen, 'WARNING');
            self::_disConnect($connect);
            return false;
        }
        //当前帧的完整长度
        $frameLength = $headerLength + $frameDataLength;
        //当前收到的数据小于当前帧的完成长度(数据不全)
        if($dataLength < $frameLength){
            return 0;
        }
        if($opCodeResult === null){
            $connect->substrReadData($frameLength);
            return null;
        }
        //本数据包所有帧的头+数据的长度
        $allFrameLength += $frameLength;
        //完整的帧
        $frame = substr($data, 0, $frameLength);
        //获取maskKey和数据
        list($masks, $message) = self::_getMaskAndMessage($frame, $payLoadLen);
        //对数据解码为客户端输入的原始数据
        $decodeMessage = self::_getOriData($message, $masks);
        //本数据包的帧列表
        $connect->wsPackageFrameList[] = array(
            //单个帧的数据(不含头, 已解码)
            'data' => $decodeMessage,
        );
        //当前帧是否是最后一帧, 如果是则返回
        if ($fin === 1) {
            return true;
        }
        return $frameLength;
    }

    /**
     * 获取原始数据
     * @param $frame string 帧
     * @param $payloadLen int
     * @return string
     */
    private static function _getMaskAndMessage($frame, $payloadLen){
        //0是marks, 1是message
        $ret = array();
        if ($payloadLen === 126) {
            $ret[0] = substr($frame, 4, 4);
            $ret[1] = substr($frame, 8);
        } else if ($payloadLen === 127) {
            $ret[0] = substr($frame, 10, 4);
            $ret[1] = substr($frame, 14);
        } else {
            $ret[0] = substr($frame, 2, 4);
            $ret[1] = substr($frame, 6);
        }
        return $ret;
    }

    /**
     * 获取原始数据
     * @param $message string 加密过的数据
     * @param $masks string 掩码
     * @return string
     */
    private static function _getOriData($message, $masks){
        //对数据解码为客户端输入的原始数据
        $decodeMessage = '';
        for ($i = 0; $i < strlen($message); $i++) {
            $decodeMessage .= $message[$i] ^ $masks[$i % 4];
        }
        return $decodeMessage;
    }


    private static function _opCode($opCode, $connect, $payLoadLen){
        switch ($opCode) {
            //Continuation Frame
            case 0x0:
            //Text Frame
            case 0x1:
            //Binary Frame
            case 0x2:
                break;
            //请求关闭链接
            case 0x8:
                self::_disConnect($connect);
                return null;
            //ping
            case 0x9:
                //执行Ping时的回调函数
                if ($connect->instance->callbackWSPing) {
                    try {
                        call_user_func($connect->instance->callbackWSPing, $connect);
                    } catch (\Exception $e) {
                        Log::write('MeepoPS: execution callback function callbackWSPing-' . json_encode($connect->instance->callbackWSPing) . ' throw exception' . json_encode($e), 'ERROR');
                    }
                } else {
                    $connect->send(pack('H*', '8a00'));
                }
                //如果数据中消息的长度为假,则从接收数据缓冲区中删除规定的消息头部分.
                if (!$payLoadLen) {
                    $connect->substrReadData(self::BASE_HEADER_LENGTH);
                }
                return null;
            //Pong
            case 0xa:
                //执行Pong时的回调函数
                if ($connect->instance->callbackWSPong) {
                    try {
                        call_user_func($connect->instance->callbackWSPong, $connect);
                    } catch (\Exception $e) {
                        Log::write('MeepoPS: execution callback function callbackWSPong-' . json_encode($connect->instance->callbackWSPong) . ' throw exception' . json_encode($e), 'ERROR');
                    }
                }
                //如果数据中消息的长度为假,则从接收数据缓冲区中删除规定的消息头部分.
                if (!$payLoadLen) {
                    $connect->substrReadData(self::BASE_HEADER_LENGTH);
                }
                return null;
            default :
                Log::write('opcode ' . $opCode . ' incorrect', 'WARNING');
                self::_disConnect($connect);
                return false;
        }
        return true;
    }

    /**
     * 主动断开链接
     * @param $connect
     */
    private static function _disConnect($connect){
        if($connect->instance->callbackWSDisconnect){
            try {
                call_user_func($connect->instance->callbackWSDisconnect, $connect);
            } catch (\Exception $e) {
                Log::write('MeepoPS: execution callback function callbackWSDisconnect-' . json_encode($connect->instance->callbackWSDisconnect) . ' throw exception' . json_encode($e), 'ERROR');
            }
        }
        $connect->close();
    }
}
