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
?>
<nav class="navbar navbar-default container" role="navigation">
    <div class="container-fluid">
        <!-- 导航头部 -->
        <div class="navbar-header">
            <!-- 移动设备上的导航切换按钮 -->
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse-example">
                <span class="sr-only">切换导航</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <!-- 品牌名称或logo -->
            <a class="navbar-brand" href="/" title="网络在线教育智能直播课堂解决方案">青草课堂</a>
        </div>
        <!-- 导航项目 -->
        <div class="collapse navbar-collapse">
            <!-- 一般导航项目 -->
            <ul class="nav navbar-nav">
                <li><a href="/"><i class="icon icon-home"></i>首页</a></li>
            </ul>
            <!-- 右侧的导航项目 -->
            <ul class="nav navbar-nav navbar-right">
                <li><a href="/help"><i class="icon icon-question-sign"></i> 帮助</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">{用户名} <b class="caret"></b></a>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="/">我的...</a></li>
                        <li class="divider"></li>
                        <li><a href="/logout">退出登录</a></li>
                    </ul>
                </li>
            </ul>
        </div><!-- END .navbar-collapse -->
    </div>
</nav>
