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
switch ($Parameters['method']){
    case '':
        {
            //用户中心
            if(!$Is_login) header("Location: /user/login");

            break;
        }
    case 'register':
        {
            //注册
            if($_SERVER['REQUEST_METHOD'] == 'GET') {
                require_once("views/user/register.php");
            }elseif ($_SERVER['REQUEST_METHOD']=='POST'){
                //检查所有输入
                foreach ($_POST as $key => $value)
                {
                    inject_check($value);
                }
                //检查用户名
                if(!preg_match('/^[a-zA-Z0-9_-]{4,16}$/', $_POST['username'])){
                    $return = [
                        'status' => 'failed',
                        'code'   => -201,
                        'msg'    => '用户名不合法'
                    ];
                    die(json_encode($return));
                }
                //用户名查重
                $data = $conn->query('SELECT * FROM `qc_user` WHERE `username` = \''. $_POST['username'] .'\' LIMIT 1');
                if($data->num_rows > 0){
                    $return = [
                        'status' => 'failed',
                        'code'   => -204,
                        'msg'    => '用户名已被占用'
                    ];
                    die(json_encode($return));
                }
                //检查密码
                if(!preg_match('/^.*(?=.{9,})(?=.*\d)(?=.*[A-z]).*$/', $_POST['password'])){
                    $return = [
                        'status' => 'failed',
                        'code'   => -202,
                        'msg'    => '密码不合法'
                    ];
                    die(json_encode($return));
                }
                //检查年级
                if(!$_POST['edu'] > 6 && !$_POST['edu'] <=0){
                    $return = [
                        'status' => 'failed',
                        'code'   => -203,
                        'msg'    => '年级不合法'
                    ];
                    die(json_encode($return));
                }
                //电话号码查重
                $data = $conn->query('SELECT * FROM `qc_user` WHERE `phone` = \''. $_POST['phone'] .'\' LIMIT 1');
                if($data->num_rows > 0){
                    $return = [
                        'status' => 'failed',
                        'code'   => -205,
                        'msg'    => '手机号已被注册'
                    ];
                    die(json_encode($return));
                }
                //从数据库中查询lid,电话号,验证码，和用户输入进行比对
                $data = $conn->query('SELECT * FROM `qc_phone_sms` WHERE `lid` = \''. $_POST['lid'] .'\' LIMIT 1')->fetch_assoc();
                //防止暴力破出验证码，检验频率
                $time = time() - 100;
                $row = $conn->query('SELECT * FROM `qc_log` WHERE `result` < \'0\' AND `ip` = \''. get_real_ip() .'\' AND `time` > \''. $time .'\' LIMIT 10');
                if($row->num_rows > 5){
                    $return = [
                        'status' => 'failed',
                        'code'   => -208,
                        'msg'    => '尝试次数过多'
                    ];
                    $usercenter->write_log($conn, 'request_too_frequent', ' ', $Uid, '-208');
                    die(json_encode($return));
                }

                if($_POST['phone'] == $data['target'] && $_POST['code'] == $data['code'] && $data['flag'] != 1){
                    //验证码是否过期
                    if(time() - $data['sendTime'] > 10 * 60){
                        $return = [
                            'status' => 'failed',
                            'code'   => -206,
                            'msg'    => '验证码过期'
                        ];
                        die(json_encode($return));
                    }
                    //验证成功，把数据插flag
                    $conn->query('UPDATE `qc_phone_sms` SET `flag` = \'1\' WHERE `lid` = \''. $_POST['lid'] .'\';');
                    //注册
                    $pass = $usercenter->pass_crypt($_POST['password']);

                    $conn->query('INSERT INTO `qc_user` (`username`, `password`, `reg_date`, `email`, `phone`)
VALUES (\''. $_POST['username'] .'\', \''. $pass .'\', \''. time() .'\', \'\', \''. $_POST['phone'] .'\');');

                    $data = $conn->query('SELECT * FROM `qc_user` WHERE `username` = \''. $_POST['username'] .'\' LIMIT 1')->fetch_assoc();
                    $conn->query('INSERT INTO `qc_user_detail` (`uid`, `realname`, `education`, `grade`)
VALUES (\''. $data['uid'] .'\', \'\', \''. $_POST['edu'] .'\', \'\');');

                    $return = [
                        'status' => 'success',
                        'code'   => 200,
                        'msg'    => '注册成功'
                    ];
                    $usercenter->write_log($conn, 'register', 'registered successfully', $data['uid'], '1');
                    die(json_encode($return));

                }else{
                    $usercenter->write_log($conn, 'wrong_sms_code', ' ', $Uid, '-207');
                    $return = [
                        'status' => 'failed',
                        'code'   => -207,
                        'msg'    => '短信验证码错误'
                    ];
                    die(json_encode($return));
                }

            }

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
                $result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $user_info);
                if (!$result) {
                    $return = [
                            'status' => 'failed',
                            'code'   => -105,
                            'msg'    => '验证码信息错误'
                        ];
                    die(json_encode($return));
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
                if(password_verify($_POST['password'], $sqlResult['password'])){
                    $return = [
                        'status' => 'success',
                        'code'   => 1000,
                        'msg'    => '登录成功，将在3秒后前往用户中心',
                    ];
                    $usercenter->write_log($conn, 'login', 'login successfully', $sqlResult['uid'], 1);
                    if($_POST['remember'] == 0){
                        $Expire = 0;
                    }else{
                        $Expire = 3600*24*30;
                    }
                    $usercenter->set_cookie('uid', $sqlResult['uid'], $Expire , $Config["website"]["https"]);
                    $usercenter->set_cookie('ukey', password_hash($sqlResult['uid'] . time()+3600*24*30 , PASSWORD_DEFAULT), $Expire , $Config["website"]["https"]);
                    $usercenter->set_cookie('expire_time', time()+3600*24*30, $Expire , $Config["website"]["https"]);
                    die(json_encode($return));
                }else{
                    $usercenter->write_log($conn, 'wrong_psw', json_encode($_POST), '0', '-1');
                    $return = [
                        'status' => 'failed',
                        'code'   => -102,
                        'msg'    => '错误的密码'
                    ];
                    die(json_encode($return));
                }
            }
            $conn->close();
            break;
        }
    case  'forget_password':
        {
            //忘记密码

            break;
        }
    case 'logout':
        {
            //退出登录
            if(!$Is_login) header("Location: /user/login");
            if(parse_url($_SERVER['HTTP_REFERER'])['host'] != $Config["website"]["domain"]){
                http_response_code(403);
                $Errinfo = '来源不正确';
                require_once ("views/error.php");
            }else{
                $usercenter->write_log($conn, 'logout', $Uid, $Uid, '1');
                $usercenter->set_cookie('uid', 0, -100 , $Config["website"]["https"]);
                $usercenter->set_cookie('ukey', '0', -100 , $Config["website"]["https"]);
                $usercenter->set_cookie('expire_time', time(), -100 , $Config["website"]["https"]);
                ?>
                <div class="layui-card layui-container">
                    <div class="layui-card-header layui-bg-red">您已退出登录</div>
                    <div class="layui-card-body">
                        <p>您的账户已安全退出</p>
                        <br />
                        <p>将在3秒后回到<a href="/" data-pjax="false">首页</a>...</p>
                        <p class="layui-word-aux">不要吐槽青草前端写得丑，我尽力了好吗QAQ...</p>
                    </div>
                </div>
                <script type="text/javascript">
                    setTimeout(function () {
                        window.location.href='/';
                    }, 3000)
                </script>
                <?php
            }
            break;
        }
    default:
        {
            $Errinfo = '页面不存在';
            require_once ("views/error.php");
        }
}