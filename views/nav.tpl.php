<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - nav.tpl.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-01-24 - 09:43
 */
//防止被恶意访问，泄露信息
if(!defined('DEBUG')) {
    http_response_code(403);
    exit('Access Denied');
}
$usercenter = new usercenter();
?>
<header>
    <div class="layui-hide-lg layui-hide-md layui-hide-sm layui-bg-orange" style="line-height: 2em">
        <p> <i class="layui-icon layui-icon-about"></i> 请注意：青草课堂的设计没有考虑移动端适配，若有页面问题请及时<a href="https://github.com/qcminecraft/qc_classrom/issues" target="_blank">反馈</a>！</p>
    </div>
    <noscript>
            <i class="layui-icon layui-icon-about"></i> 使用青草课堂<b style="color:#f00;">必须</b>开启JavaScript，否则无法使用！
    </noscript>
    <div class="layui-layout layui-layout-admin">
        <div class="layui-header">
            <div class="layui-logo"><a href="/"><img src="<?php echo $Config["website"]["static"]; ?>img/logo_.png" style="height: 60px;"></a> </div>
            <ul class="layui-nav layui-layout-left layui-hide-xs">
                <li class="layui-nav-item <?php if($_SERVER['REQUEST_URI'] === '/') echo "layui-this" ?>"><a href="/"><em class="layui-icon layui-icon-home"> </em>首页</a></li>
                <?php if($usercenter->get_user_group($conn, $Uid) > 1){ ?>
                    <li class="layui-nav-item <?php if($_SERVER['REQUEST_URI'] === '/teacher/createCourse') echo "layui-this" ?>"><a href="/teacher/createCourse"><em class="layui-icon layui-icon-add-1"> </em>创建课程</a></li>
                <?php }?>
                <?php if($usercenter->get_user_group($conn, $Uid) > 1){ ?>
                    <li class="layui-nav-item <?php if($_SERVER['REQUEST_URI'] === '/teacher/') echo "layui-this" ?>"><a href="/teacher/"><em class="layui-icon layui-icon-username"> </em> 教师中心</a></li>
                <?php } ?>
                <li class="layui-nav-item <?php if($_SERVER['REQUEST_URI'] === '/user/myCourse') echo "layui-this" ?>"><a href="/user/myCourse"><em class="layui-icon layui-icon-list"> </em> 我的课程</a></li>
            </ul>
            <ul class="layui-nav layui-layout-right">
                <li class="layui-nav-item">
                    <a href="<?php if(!$Is_login){ echo "/user/login"; }else{?>javascript:;<?php } ?>">
                        <img src="<?php echo $Config["website"]["static"] . $usercenter->get_avatar($Uid, $conn); ?>" class="layui-nav-img">
                        <?php echo $Uinfo['username']; ?>
                    </a>
                    <dl class="layui-nav-child">
                        <?php if($Is_login){ ?>
                        <dd><a href="###">基本资料</a></dd>
                        <dd><a href="###">安全设置</a></dd>
                        <dd><a href="/user/logout">退出登录</a></dd>
                        <?php }else{ ?>
                        <dd><a href="/user/login">登录</a></dd>
                        <dd><a href="/user/register">注册</a></dd>
                        <?php } ?></dl>
                </li>
            </ul>
        </div>
    </div>
    <div style="height: 2em"></div>
</header>