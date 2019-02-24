<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - teacher.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-02-18 - 19:19
 */
//防止被恶意访问，泄露信息
if(!defined('DEBUG')) {
    http_response_code(403);
    exit('Access Denied');
}
//引入usercenter
$usercenter = new usercenter();
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    header('Content-Type: application/json');
    //禁止缓存
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
}
if(!$Is_login) header("Location: /user/login");
if($usercenter->get_user_group($conn, $Uid) < 2){
    $Errinfo = '权限不足';
    require_once ("views/error.php");
}
switch ($Parameters['mod']){
    case '':{
        //教师中心
        break;
    }
    case 'createCourse':{
        //创建课程
        if($_SERVER['REQUEST_METHOD'] == 'GET'){
            require_once ('views/teacher/createCourse.phtml');
        }elseif ($_SERVER['REQUEST_METHOD'] == 'POST'){
            
        }
        break;
    }
    default:{
        $Errinfo = '页面不存在';
        require_once ("views/error.php");
    }
}