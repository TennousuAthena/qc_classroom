<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - common.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-01-23 - 18:01
 */
//防止被恶意访问，泄露信息
if(!defined('DEBUG')) {
    http_response_code(403);
    exit('Access Denied');
}
//判断用户是否登录
$usercenter = new usercenter();
$Uid = $_COOKIE['qc_uid'];
$conn = new mysqli($Config["database"]["address"], $Config["database"]["username"], $Config["database"]["password"],
    $Config["database"]["name"]);
if($Uid > 0){
    if((int)$_COOKIE['qc_expire_time'] > time() && password_verify($Uid . $_COOKIE['qc_expire_time'] , utf8_decode($_COOKIE['qc_ukey']))){
        $Is_login = true;
        $Uinfo = $conn->query("SELECT * FROM `qc_user` WHERE `uid` = '". $Uid ."'")->fetch_assoc();
    }
}else{
    $usercenter->set_cookie('uid', '0');
    $Is_login = false;
    $Uinfo['username'] = '未登录用户';
}
require_once ('data/title.db.php');
if($UrlPath === 'showCourse'){
    $data = $conn->query('SELECT * FROM `qc_course` WHERE `scid` = \''. $Parameters['csid'] .'\' LIMIT 1')->fetch_assoc();
    $title = $data['name'];
}
class View {
    /**
     * @var bool 是否开启随机数防缓存，方便开发
     */
    public $is_debug = false;

    /**
     * 加载js
     * @param string $filename 文件在/assets/js下的名称，包含.js
     * @param bool $async 是否异步加载脚本
     * @return null
     */
    function load_js($filename, $async = false){
        global $Config;
        $return = '<script src="'.$Config["website"]["static"].'js/'.$filename;
        if($this->is_debug) $return.='?' . rand(1000000, 9999999);

        $return.='"';

        if($async) $return.=' async';
        $return.='></script>' . PHP_EOL;
        echo $return;
        return null;
    }

    /**
     * 加载css
     * @param string $filename 文件在/assets/css下的名称，包含.css
     * @return int
     */
    function load_css($filename){
        global $Config;
        if(!$this->is_debug) {
            echo "<link rel=\"stylesheet\" href=\"" . $Config["website"]["static"] . "css/" . $filename . "\" type=\"text/css\" media=\"all\" />" . PHP_EOL;
        }else{
            echo "<link rel=\"stylesheet\" href=\"" . $Config["website"]["static"] . "css/" . $filename . "?" . rand(1000000,9999999) ."\" type=\"text/css\" media=\"all\" />" . PHP_EOL;
        }
        return 0;
    }
    function google_analytics($gid="UA-100755509-9"){
        if(!$this->is_debug) {
            echo "        <script async src=\"https://www.googletagmanager.com/gtag/js?id=" . $gid . "\"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '$gid');
        </script>
";
        }
    }
}
//写访问日志
if(!strstr($_SERVER['HTTP_USER_AGENT'], 'curl')) {
    $usercenter->write_log($conn, 'visit', GetCurUrl(), $Uid);
}

$view = new View();
$view->is_debug = DEBUG;
//部分controller不需要view
if($UrlPath == 'show' || $UrlPath == 'callback' || $UrlPath == 'api' || $_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once("controller/" . $UrlPath . ".php");
}else{
    require_once("head.tpl.php");
    require_once("nav.tpl.php");
    require_once("controller/" . $UrlPath . ".php");
    require_once("footer.tpl.php");
    require_once("foot.tpl.php");
}