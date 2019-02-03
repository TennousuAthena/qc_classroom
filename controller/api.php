<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - api.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-02-03 - 14:54
 */
//禁止缓存
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
//json格式
header('Content-Type: application/json');
//引入usercenter
$usercenter = new usercenter();
switch ($Parameters['mod']){
    case 'captcha':{
        require_once ("includes/geetest/lib/class.geetestlib.php");
        $GtSdk = new GeetestLib($Config["geetest"]["id"], $Config["geetest"]["key"]);
        $user_info = array(
            "user_id" => isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : "temp_user",
            "client_type" => $usercenter->is_mobile() ? "h5" : "web",
            "ip_address" => get_real_ip()
        );
        $gt_status = $GtSdk->pre_process($user_info, 1);
        $_SESSION['gtserver'] = $gt_status;
        $_SESSION['user_id'] = $user_info['user_id'];
        echo $GtSdk->get_response_str();
    }
}