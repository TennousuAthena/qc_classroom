<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - usercenter.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-01-29 - 14:13
 */
//防止被恶意访问，泄露信息
if(!defined('DEBUG')) {
    http_response_code(403);
    exit('Access Denied');
}

class usercenter
{
    /**
     * 加密用户密码
     * @param $password
     * @return array
     */
    public function pass_crypt($password, $salt = ''){
        $options['cost'] = 12;
        if($salt == ''){
            $options['salt'] = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
        }else{
            $options['salt'] = $salt;
        }
        return array(
            'salt' => $options['salt'],
            'password' => password_hash($password, PASSWORD_BCRYPT, $options)
        );
    }

    /**
     * 判断是否为移动端 via:http://t.cn/EtkiCVd
     * @return bool
     */
    public function is_mobile() {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是电脑微信
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel',
                'lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi',
                'openwave','nexusone','cldc','midp','wap','mobile','MicroMessenger');
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') ===
                    false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }

    /**
     * 设置cookie
     * @param string $name cookie名
     * @param mixed $content 内容
     * @param int $expire ?秒后过期
     * @param bool $secure 是否https
     * @param string $pre 前缀
     */
    public function set_cookie($name, $content, $expire=2592000, $secure = false, $pre="qc_"){
        if($expire==0){
            setcookie($pre . $name, $content, 0, '/', $_SERVER['HTTP_HOST'], $secure, true);
        }else {
            setcookie($pre . $name, $content, time() + $expire, '/', $_SERVER['HTTP_HOST'], $secure, true);
        }
    }

    /**
     * 获取用户头像
     * @param int $uid 用户名
     * @param object $conn 数据库连接信息
     * @return string 用户头像上传地址
     * todo:使用时前面要加$Config["website"]["static"]
     */
    public function get_avatar($uid, $conn){
        if($conn->query("SELECT * FROM `qc_avatar` WHERE `uid` = '". $uid."'")->num_rows <= 0){
            return '/img/akari.jpg';
        }else{
            return $conn->query("SELECT * FROM `qc_avatar` WHERE `uid` = '". $uid."'")->fetch_assoc()['avatar_url'];
        }
    }
}