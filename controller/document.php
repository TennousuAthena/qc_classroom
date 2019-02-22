<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - document.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-02-20 - 16:41
 */
//防止被恶意访问，泄露信息
if(!defined('DEBUG')) {
    http_response_code(403);
    exit('Access Denied');
}
$targetFile = 'views/document/'.$Parameters['name'].'.phtml';
if(!file_exists($targetFile)){
    http_response_code(404);
    $Errinfo = '文档不存在';
    require_once ("views/error.php");
}else{
    require_once ($targetFile);
}
echo '<div class="layui-container">
    <div id="container"></div>
</div><br /><br /><br /><br /><br />
<link rel="stylesheet" href="//imsun.github.io/gitment/style/default.css">
<script type="text/javascript" src="https://cdn.jsdelivr.net/gh/qcminecraft/qc_classrom@5a8e655cd8bdc6bac80a0ace2da984afd7f99efd/assets/js/gitment.min.js"></script>
<script>
    let gitment = new Gitment({
        id: window.location.pathname,
        owner: \'qcminecraft\',
        repo: \'qc_classroom-comment\',
        oauth: {
            client_id: \'146880877967adb358f9\',
            client_secret: \'b735e411a514ca5d5037dc6fb1be42e2bb5b57ba\',
        },
    })
    gitment.render(\'container\')
</script>';