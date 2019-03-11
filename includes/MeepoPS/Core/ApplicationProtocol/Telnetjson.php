<?php
/**
 * 从TCP数据流中解析Telnet+Json协议
 * Telnet的包分割方式: 每个数据包已\n来结尾.如果发现\n,则\n之前为一个数据包.如果没有\n,则等待下次数据的到来
 * Json的数据格式
 * Created by Lane
 * User: lane
 * Date: 16/3/26
 * Time: 下午10:27
 * E-mail: lixuan868686@163.com
 * WebSite: http://www.lanecn.com
 */
namespace MeepoPS\Core\ApplicationProtocol;

use MeepoPS\Core\TransportProtocol\TransportProtocolInterface;

class Telnetjson implements ApplicationProtocolInterface
{
    /**
     * 检测数据, 返回数据包的长度.
     * 没有数据包或者数据包未结束,则返回0
     * @param string $data 数据包
     * @param TransportProtocolInterface $connect 基于传输层协议的链接
     * @return int
     */
    public static function input($data, TransportProtocolInterface $connect)
    {
        //获取首个数据包的大小(结尾的位置)
        $position = strpos($data, "\n");
        //如果没有, 说明接收到的不是一个完整的数据包, 则暂时不处理本次请求, 等待下次接收后再一起处理
        if ($position === false) {
            return 0;
        }
        //返回数据包的长度. 因为计数从0开始,所以返回时+1
        return $position + 1;
    }

    /**
     * 数据编码. 默认在发送数据前自动调用此方法. 不用您手动调用.
     * @param string $data 给数据流中发送的数据
     * @param TransportProtocolInterface $connect 基于传输层协议的链接
     * @return string
     */
    public static function encode($data, TransportProtocolInterface $connect)
    {
        return json_encode($data) . "\n";
    }

    /**
     * 数据解码. 默认在接收数据时自动调用此方法. 不用您手动调用.
     * @param string $data 从数据流中接收到的数据
     * @param TransportProtocolInterface $connect 基于传输层协议的链接
     * @return string
     */
    public static function decode($data, TransportProtocolInterface $connect)
    {
        return json_decode(trim($data), true);
    }
}
