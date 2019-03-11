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
        break;
    }
    case 'sendSms':{
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            require_once("includes/geetest/lib/class.geetestlib.php");
            require_once("includes/qcsms/SmsSingleSender.php");
            $GtSdk = new GeetestLib($Config["geetest"]["id"], $Config["geetest"]["key"]);
            $data = array(
                "user_id" => isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : "temp_user",
                "client_type" => $usercenter->is_mobile() ? "h5" : "web",
                "ip_address" => get_real_ip()
            );
            $result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $data);
            if (!$result) {
                    $return = [
                        'status' => 'failed',
                        'code'   => -105,
                        'msg'    => '验证码信息错误'
                    ];
                    die(json_encode($return));
                }
            if (!preg_match('/^1[34578]\d{9}$/', $_POST['phoneNumber'])) {
                $return = [
                    'status' => 'failed',
                    'code'   => -111,
                    'msg'    => '手机号不合法'
                ];
                die(json_encode($return));
            }
            $data = $conn->query('SELECT * FROM `qc_user` WHERE `phone` = \''. $_POST['phoneNumber'] .'\' LIMIT 1');
            if($data->num_rows > 0){
                $return = [
                    'status' => 'failed',
                    'code'   => -205,
                    'msg'    => '手机号已被注册'
                ];
                die(json_encode($return));
            }

            $verifyCode = rand(100000, 999999);
            $ssender = new SmsSingleSender($Config["qcloud"]["smsid"], $Config["qcloud"]["smskey"]);
            $result = $ssender->sendWithParam("86", $_POST['phoneNumber'], 244008, [$verifyCode , 10], "青草Minecraft", "", "");

            $conn->query('INSERT INTO `qc_phone_sms` (`lid`, `target`, `sendTime`, `sendIP`, `code`) VALUES (NULL, \''. $_POST['phoneNumber'] .'\', \''. time() .'\', \''. get_real_ip() .'\', \''. $verifyCode .'\')');

            $lid = $conn->query('SELECT * FROM `qc_phone_sms` WHERE `target` = \''. $_POST['phoneNumber'] .'\' ORDER BY `qc_phone_sms`.`lid` DESC')->fetch_assoc()['lid'];

            if(strstr($result, "OK")){
                $return = [
                    'status' => 'success',
                    'code'   => 110,
                    'msg'    => '成功发送短信',
                    'lid'    => $lid,
                ];
                die(json_encode($return));
            }else{
                $return = [
                    'status' => 'failed',
                    'code'   => -110,
                    'msg'    => '短信无法正常发送'
                ];
                die(json_encode($return));
            }
        }else{
            $return = [
                'status' => 'failed',
                'code'   => -98,
                'msg'    => '错误的请求方法'
            ];
            die(json_encode($return));
        }
        break;
    }
    case 'analytics':{
        // **********************
        // * Author: stneng
        // * Introduction: https://stneng.com/google-analytics-异步请求（服务端请求）/
        // **********************
        $tid='UA-100755509-9';

        function create_uuid(){
            $str = md5(uniqid(mt_rand(), true));
            $uuid = substr($str,0,8) . '-';
            $uuid .= substr($str,8,4) . '-';
            $uuid .= substr($str,12,4) . '-';
            $uuid .= substr($str,16,4) . '-';
            $uuid .= substr($str,20,12);
            return $uuid;
        }

        if (!isset($_COOKIE["uuid"])) {
            $uuid=create_uuid();
            setcookie("uuid", $uuid , time()+368400000);
        }else{
            $uuid=$_COOKIE["uuid"];
        }

        if (function_exists("fastcgi_finish_request")) {
            fastcgi_finish_request(); //对于fastcgi会提前返回请求结果，提高响应速度。
        }

        $url='v=1&t=pageview&';
        $url.='tid='.$tid.'&';
        $url.='cid='.$uuid.'&';
        $url.='dl='.rawurlencode(rawurldecode($_SERVER['HTTP_REFERER'])).'&';
        $url.='uip='.rawurlencode(rawurldecode($_SERVER['REMOTE_ADDR'])).'&';
        $url.='ua='.rawurlencode(rawurldecode($_SERVER['HTTP_USER_AGENT'])).'&';
        $url.='dt='.rawurlencode(rawurldecode($_GET['dt'])).'&';
        $url.='dr='.rawurlencode(rawurldecode($_GET['dr'])).'&';
        $url.='ul='.rawurlencode(rawurldecode($_GET['ul'])).'&';
        $url.='sd='.rawurlencode(rawurldecode($_GET['sd'])).'&';
        $url.='sr='.rawurlencode(rawurldecode($_GET['sr'])).'&';
        $url.='vp='.rawurlencode(rawurldecode($_GET['vp'])).'&';
        $url.='z='.$_GET['z'];
        $url='https://www.google-analytics.com/collect?'.$url;
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
        die(json_encode(['status'=>'success']));
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