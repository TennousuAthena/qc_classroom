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
class View {
    /**
     * @var 是否开启随机数防缓存，方便开发
     */
    public $is_debug;

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
        echo "        <script async src=\"https://www.googletagmanager.com/gtag/js?id=".$gid."\"></script>
            <script>
              window.dataLayer = window.dataLayer || [];
              function gtag(){dataLayer.push(arguments);}
              gtag('js', new Date());
              gtag('config', '$gid');
            </script>
";
    }
}

$view = new View();
$view->is_debug = DEBUG;
include_once ("head.tpl.php");
include_once ("nav.tpl.php");
include_once ("controller/".$UrlPath.".php");
include_once ("foot.tpl.php");
