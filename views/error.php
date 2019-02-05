<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - error.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-02-05 - 12:30
 */
?>
<div class="layui-card layui-container">
    <div class="layui-card-header layui-bg-red">错误：<?php echo $Errinfo; ?></div>
    <div class="layui-card-body">
        <p>这样的话咱也没办法啊，可能是某草疏忽了也可能是你正在乱搞┑(￣Д ￣)┍</p>  <br />
        <p><a href="/" title="返回<?php $Config['website']['title']; ?>" class="layui-word-aux">返回首页</a></p>
    </div>
</div>