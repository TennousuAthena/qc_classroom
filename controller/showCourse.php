<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - showCourse.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-02-26 - 14:59
 */
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/dplayer@1.25.0/dist/DPlayer.min.css">
<div class="layui-container">
    <div id="player"></div>
</div>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/dplayer@1.25.0/dist/DPlayer.min.js"></script>
<script>
    const vid = '//vid.edu.qmcmc.cn/test/test_video/%E9%BA%BB%E6%9E%9D%20%E5%87%86%C3%97%E3%82%84%E3%81%AA%E3%81%8E%E3%81%AA%E3%81%8E%20%E3%80%8C%E7%B5%82%E3%82%8F%E3%82%8A%E3%81%AE%E4%B8%96%E7%95%8C%E3%81%8B%E3%82%89%E3%80%8D.mp4';
    const dp = new DPlayer({
        container: document.getElementById('player'),
        screenshot: true,
        theme: '#5FB878',
        mutex: true,
        logo: '/assets/img/logo_.png',
        video: {
            defaultQuality: 1,
            quality: [{
                name: '原画',
                url: vid,
                type: 'auto'
            }, {
                name: '720p',
                url: vid+'.f30.mp4',
                type: 'auto'
            }, {
                name: '480p',
                url: vid+'.f20.mp4',
                type: 'auto'
            }],
            pic: vid+'.0_0.p0.jpg',
            thumbnails: vid+'.0_0.p0.jpg'
        },
        subtitle: {
            color: '#cdcdcd',
            fontSize : '3vw',
            url: '//vid.edu.qmcmc.cn/test/test_video/%E7%BB%88%E3%82%8F%E3%82%8A%E3%81%AE%E4%B8%96%E7%95%8C%E3%81%8B%E3%82%89.vtt'
        },
    });
    $('.dplayer-logo').addClass('player-logo');
    $('.dplayer-logo').removeClass('dplayer-logo');
</script>