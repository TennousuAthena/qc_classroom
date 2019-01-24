<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - show.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-01-24 - 11:22
 */
setcookie("qc_flag", "1");
?>
<!DOCTYPE HTML>
<!--
	under the CCA 3.0 license (html5up.net/license)
-->
<html>
<head>
    <title>青草课堂 - 网络在线教育智能直播课堂解决方案</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <?php $view->load_css("main_show.css"); ?>
</head>
<body class="is-preload">

<!-- Header -->
<header id="header">
    <h1>青草课堂</h1>
    <p>线上线下相结合</p>
    <p>网络在线教育智能直播课堂解决方案</p>
</header>

<!-- Signup Form -->
<form id="signup-form" method="post" action="#">
    <input type="submit" value="立即体验" onclick="location.href='/'" />
</form>

<!-- Footer -->
<footer id="footer">
    <ul class="icons">
        <li><a href="https://twitter.com/Qc_Minecraft" class="icon fa-twitter" target="_blank"><span class="label">Twitter</span></a></li>
        <li><a href="https://github.com/qcminecraft" class="icon fa-github" target="_blank"><span class="label">GitHub</span></a></li>
        <li><a href="https://blog.qmcmc.cn/start-page.html" target="_blank" class="icon fa-envelope-o"><span class="label">Email</span></a></li>
    </ul>
    <ul class="copyright">
        <li>&copy; 青草科技.</li><li>Credits: HTML5 ↑</li>
    </ul>
</footer>

<!-- Scripts -->
<?php $view->load_js("main_show.js"); ?>

<?php $view->google_analytics(); ?>
</body>
</html>
