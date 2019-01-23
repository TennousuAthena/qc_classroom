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
    function load_js($filename){
        global $Config;
        echo "<script src=\"".$Config["website"]["static"]."js/".$filename."\"></script>".PHP_EOL;
        return 0;
    }
    function load_css($filename){
        global $Config;
        echo "<link rel=\"stylesheet\" href=\"".$Config["website"]["static"]."css/".$filename."\" type=\"text/css\" media=\"all\" />".PHP_EOL;
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
include_once ("head.tpl.php");
include_once ("controller/".$UrlPath.".php");
include_once ("foot.tpl.php");
