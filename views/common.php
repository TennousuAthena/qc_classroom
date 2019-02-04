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
$Uid = $_COOKIE['qc_uid'];
if($Uid > 0){
    if(password_verify($Uid . $_COOKIE['qc_expire_time'] , utf8_decode($_COOKIE['qc_ukey']))){
        $Is_login = true;
        $conn = new mysqli($Config["database"]["address"], $Config["database"]["username"], $Config["database"]["password"],
            $Config["database"]["name"]);
        $Uinfo = $conn->query("SELECT * FROM `qc_user` WHERE `uid` = '". $Uid ."'")->fetch_assoc();
    }
}else{
    $Is_login = false;
    $Uinfo['username'] = '未登录用户';
}
class View {
    /**
     * @var 是否开启随机数防缓存，方便开发
     */
    public $is_debug = false;

    /**
     * 加载js
     * @param $filename 文件在/assets/js下的名称，包含.js
     * @return int
     */
    function load_js($filename){
        global $Config;
        if(!$this->is_debug) {
            echo "<script src=\"" . $Config["website"]["static"] . "js/" . $filename . "\"></script>" . PHP_EOL;
        }else{
            echo "<script src=\"" . $Config["website"]["static"] . "js/" . $filename . "?" . rand(1000000,9999999) . "\"></script>" . PHP_EOL;
        }
        return 0;
    }

    /**
     * 加载css
     * @param $filename 文件在/assets/css下的名称，包含.css
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
