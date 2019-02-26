<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - server.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-02-26 - 15:13
 */

require_once ('../includes/MeepoPS/index.php');

//使用WebSocket协议传输的Api类
$webSocket = new \MeepoPS\Api\Websocket('0.0.0.0', '23333');
$webSocket->callbackNewData = function ($connect, $data){
    $msg = '收到用户发送的消息: ' . $data;
    $message = array(
        'errno' => 0, 'errmsg' => 'OK', 'data' => array(
            'content' => $msg, 'create_time' => date('Y-m-d H:i:s'),
        ),
    );
    $connect->send(json_encode($message));
};

//启动MeepoPS
\MeepoPS\runMeepoPS();