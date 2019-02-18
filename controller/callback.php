<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - callback.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-01-24 - 12:03
 */
//防止被恶意访问，泄露信息
if(!defined('DEBUG')) {
    http_response_code(403);
    exit('Access Denied');
}
//禁止缓存
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
//json格式
header('Content-Type: application/json');
//引入usercenter
$usercenter = new usercenter();
//数据库
$conn = new mysqli($Config["database"]["address"], $Config["database"]["username"], $Config["database"]["password"],
    $Config["database"]["name"]);
switch ($Parameters['mod']){
    case 'videoTranscoding':{

        break;
    }
    default:{
        $return = [
            'status' => 'failed',
            'code'   => -99,
            'msg'    => '未知模块'
        ];
        die(json_encode($return));
    }
}