<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - user.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-01-29 - 14:34
 */
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    header('Content-Type: application/json');
}
switch ($Parameters['method']){
    case '':
        {
            //用户中心

            break;
        }
    case 'register':
        {
            //注册

            break;
        }
    case 'login':
        {
            //登录
            if($_SERVER['REQUEST_METHOD'] == 'GET') {
                require_once("views/user/login.php");
            }elseif ($_SERVER['REQUEST_METHOD']=='POST'){
                //检查注入
                inject_check($_POST['password']);
                if(!inject_check($_POST['username']) || !inject_check($_POST['password'])){
                $return = [
                    'status' => 'failed',
                    'code'   => -10,
                    'msg'    => '输入含有非法参数'
                ];
                die(json_encode($return));
                }
                $usercenter = new usercenter();
                // 创建连接
                $conn = new mysqli($Config["database"]["address"], $Config["database"]["username"], $Config["database"]["password"],
                    $Config["database"]["name"]);

                //用户名是否存在
                if($conn->query("SELECT * FROM `qc_user` WHERE `username` = '". $_POST['username']."'")->num_rows > 0){
                    //用户存在
                    $sqlResult = $conn->query("SELECT * FROM `qc_user` WHERE `username` = '". $_POST['username']."'")->fetch_assoc();
                }elseif ($conn->query("SELECT * FROM `qc_user` WHERE `email` = '". $_POST['username']."'")->num_rows > 0){
                    //邮箱
                    $sqlResult = $conn->query("SELECT * FROM `qc_user` WHERE `email` = '". $_POST['username']."'")->fetch_assoc();
                }elseif ($conn->query("SELECT * FROM `qc_user` WHERE `phone` = '". $_POST['username']."'")->num_rows > 0){
                    //手机
                    $sqlResult = $conn->query("SELECT * FROM `qc_user` WHERE `phone` = '". $_POST['username']."'")->fetch_assoc();
                }else{
                    $return = [
                        'status' => 'failed',
                        'code'   => -101,
                        'msg'    => '用户不存在'
                    ];
                    die(json_encode($return));
                }
                //验证密码是否正确
                if($usercenter->pass_crypt($_POST["password"], $sqlResult['salt'])['password'] == $sqlResult['password']){
                    $return = [
                        'status' => 'success',
                        'code'   => 1000,
                        'msg'    => '登录成功'
                    ];
                    die(json_encode($return));
                }else{
                    $return = [
                        'status' => 'failed',
                        'code'   => -102,
                        'msg'    => '错误的密码'
                    ];
                    die(json_encode($return));
                }
            }
            break;
        }
    case  'forget_password':
        {
            //忘记密码

            break;
        }
    default:
        {
            header("Location: /not_found");
            die();
        }
}