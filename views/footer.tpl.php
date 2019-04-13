<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - footer.tpl.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-01-24 - 18:15
 */
//防止被恶意访问，泄露信息
if(!defined('DEBUG')) {
    http_response_code(403);
    exit('Access Denied');
}
?>

        </div>
    </div>
<footer class="qc_footer">
    <div class="layui-bg-gray" style="line-height: 5em">
        <p style="text-align: center;"> &copy <a href="https://blog.qmcmc.cn" target="_blank" title="前往作者Blog"> 青草</a> ·<a href="https://github.com/qcminecraft/qc_classroom" target="_blank" rel="nofollow" title="查看项目源代码"> 课堂 </a>
            2016-<?php echo date('Y');?> All rights reserved | <a href="/doc/before-you-use" target="_blank">使用必读</a> | <a href="/doc/privacy-policy" target="_blank">隐私协议</a> |
            <a href="/doc/timeline" target="_blank">时间线</a> |
            <img src="<?php echo $Config["website"]["static"]; ?>img/status.svg" alt="项目构建状态" title="构建状态"><br />
        </p>
    </div>
</footer>