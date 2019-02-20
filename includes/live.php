<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - live.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-02-20 - 16:33
 */

class live
{
    /**
     * 获取推流地址
     * 如果不传key和过期时间，将返回不含防盗链的url
     * @param String domain 您的推流域名
     *        streamId 您用来区别不同推流地址的唯一流ID
     *        key 安全密钥
     *        time 过期时间 sample 2016-11-12 12:00:00
     * @return String url
     */
    function getPushUrl($domain, $streamId, $key = null, $time = null){
        if($key && $time){
            $txTime = strtoupper(base_convert(strtotime($time),10,16));
            $txSecret = md5($key.$streamId.$txTime);
            $extStr = "?".http_build_query(array(
                    "txSecret"=> $txSecret,
                    "txTime"=> $txTime
                ));
        }
        return "rtmp://".$domain."/live/".$streamId.(isset($extStr) ? $extStr : "");
    }

//echo getPushUrl("pushtest.com", "123456", "69e0daf7234b01f257a7adb9f807ae9f", "2016-09-11 20:08:07");

    /**
     * 获取播放地址
     * @param String domain 您的播放域名
     *        streamId 您用来区别不同推流地址的唯一流ID
     * @return array url
     */
    function getPlayUrl($domain, $streamId){
        return [
            "rtmp://".$domain."/live/".$streamId,
            "http://".$domain."/live/".$streamId.".flv",
            "http://".$domain."/live/".$streamId.".m3u8"
        ];
    }
}