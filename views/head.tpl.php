<!DOCTYPE html>
<html lang="zh-cn">
    <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $Config["website"]["title"]." - ".$Config["website"]["subtitle"]?></title>

        <?php $view->load_css("layui.css"); ?>
        <?php $view->load_css("main.css"); ?>

        <?php $view->load_js("jquery.min.js"); ?>
    </head>
    <body>
    <!-- 让IE8/9支持媒体查询，从而兼容栅格 -->
    <!--[if lt IE 9]>
    <?php $view->load_js("html5.min.js"); ?>
    <?php $view->load_js("respond.min.js"); ?>
    <![endif]-->
