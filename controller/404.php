<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - 404.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-01-23 - 21:09
 */
function GetCurUrl(){
    $url='http://';
    if(isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']=='on'){
        $url='https://';
    }
    if($_SERVER['SERVER_PORT']!='80'){
        $url.=$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
    }else{
        $url.=$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    }
    return $url;
}
//状态返回404
http_response_code(404)
?>
<!DOCTYPE html>
<html">
<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
    <title>青草课堂 - 系统发生错误 ┭┮﹏┭┮</title>
    <style type="text/css">
        *{ padding: 0; margin: 0; }
        html{ overflow-y: scroll; }
        body{ background: #fff; font-family: '微软雅黑'; color: #333; font-size: 16px; }
        img{ border: 0; }
        .error{ padding: 24px 48px; }
        .face{ font-size: 100px; font-weight: normal; line-height: 120px; margin-bottom: 12px; }
        h1{ font-size: 32px; line-height: 48px; }
        .error .content{ padding-top: 10px}
        .error .info{ margin-bottom: 12px; }
        .error .info .title{ margin-bottom: 3px; }
        .error .info .title h3{ color: #000; font-weight: 700; font-size: 16px; }
        .error .info .text{ line-height: 24px; }
        .copyright{ padding: 12px 48px; color: #999; }
        .copyright a{ color: #000; text-decoration: none; }
    </style>
</head>
<body>
<div class="error">
    <p class="face">:(</p>
    <h1>页面发生错误</h1>
    <div class="content">
        <p>错误信息：404 Not Found</p>
        <p>页面地址：<?php echo GetCurUrl(); ?></p>
    </div>
</div>
<div class="copyright">
    <p><a title="返回首页" href="/">青草课堂</a><sup>Beta</sup> { 网络在线教育智能直播课堂解决方案 }</p>
</div>
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-100755509-9"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'UA-100755509-9');
</script>
</body>
</body>
</html>

