<?php
//防止被恶意访问，泄露信息
if(!defined('DEBUG')) {
    http_response_code(403);
    exit('Access Denied');
}
if(!$title){
    $title = ($Title_DB[$URI]!=null)?$Title_DB[$URI].' - ' : '';
    $og_title = $Title_DB[$URI];
}else{
    $og_title = $title;
    $title = $title.' - ';
}
?>
<!DOCTYPE html>
<html lang="zh-cmn-Hans" prefix="og: http://ogp.me/ns#">
    <head>
        <meta charset="utf-8">
        <meta name="renderer" content="webkit">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="青草课堂是一个开源的智能网络在线教育解决方案，他可以快速为您搭建网络课堂平台。">
        <meta name="keywords" content="青草课堂,青草网校,课堂解决方案,智能课堂,智慧课堂,青草MC,青草视频在线观看,物理课堂,青草慕课,课堂.online,在线课堂,在线教育解决方案,网课平台">

        <meta property="og:locale" content="zh_CN" />
        <meta property="og:title" content="<?php echo $og_title ?>" />

        <link rel="dns-prefetch" href="//<?php echo $Config["domain"]["video"] ?>">
        <link rel="dns-prefetch" href="//<?php echo $Config["domain"]["live_play"] ?>">

        <title><?php echo $title ?><?php echo $Config["website"]["title"]." - ".$Config["website"]["subtitle"]?></title>

            <?php $view->load_css("layui.css"); ?>
            <?php $view->load_css("main.css"); ?>

            <?php $view->load_js("jquery.min.js"); ?>
    </head>
    <body class="layui-layout-body">
    <!-- 让IE8/9支持媒体查询，从而兼容栅格 -->
    <!--[if lt IE 9]>
    <?php $view->load_js("html5.min.js"); ?>
    <?php $view->load_js("respond.min.js"); ?>
    <![endif]-->

    <?php $view->load_js("../layui.js"); ?>
    <?php $view->load_js("main.js"); ?>
    <div class="wrap">
