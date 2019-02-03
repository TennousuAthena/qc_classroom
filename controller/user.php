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
//引入usercenter
$usercenter = new usercenter();
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
                //引入geetest
                require_once ("includes/geetest/lib/class.geetestlib.php");

                //检查输入
                if($_POST['username'] == '' && $_POST['password'] == ''){
                    $return = [
                        'status' => 'failed',
                        'code'   => -103,
                        'msg'    => '用户名或密码为空'
                    ];
                    die(json_encode($return));
                }
                if($_POST['geetest_challenge'] == '' && $_POST['geetest_validate']=='' && $_POST['geetest_seccode'] == ''){
                    $return = [
                        'status' => 'failed',
                        'code'   => -104,
                        'msg'    => '验证码信息错误'
                    ];
                    die(json_encode($return));
                }

                //验证geetest
                $GtSdk = new GeetestLib($Config["geetest"]["id"], $Config["geetest"]["key"]);
                $user_info = array(
                    "user_id" => isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : "temp_user",
                    "client_type" => $usercenter->is_mobile() ? "h5" : "web",
                    "ip_address" => get_real_ip()
                );
                if ($_SESSION['gtserver'] == 1) {   //服务器正常
                    $result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $user_info);
                    if (!$result) {
                        $return = [
                            'status' => 'failed',
                            'code'   => -105,
                            'msg'    => '验证码信息错误'
                        ];
                        die(json_encode($return));
                    }
                }else{  //服务器宕机,走failback模式
                    if ($GtSdk->fail_validate($_POST['geetest_challenge'],$_POST['geetest_validate'],$_POST['geetest_seccode'])) {
                        //echo '{"status":"success"}'; OK
                    }else{
                        $return = [
                            'status' => 'failed',
                            'code'   => -106,
                            'msg'    => '离线模式下验证码信息错误'
                        ];
                        die(json_encode($return));
                    }
                }


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