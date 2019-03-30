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

$data = $conn->query('SELECT * FROM `qc_course` WHERE `scid` = \''. $Parameters['csid'] .'\' LIMIT 1')->fetch_assoc();
if($data['name'] == ''){
    $Errinfo = '课程不见了~';
    require_once ("views/error.php");
}
$base_url = $data['file_url'];
if($Config["qcloud"]["CDN_KEY"]){
    $file_url = [
        'origin'   => '//'.$Config['domain']['video'].CDNSign($data['file_url'], $Config["qcloud"]["CDN_KEY"]),
        '720p'     => '//'.$Config['domain']['video'].CDNSign($data['file_url'].'.f30.mp4', $Config["qcloud"]["CDN_KEY"]),
        '480p'     => '//'.$Config['domain']['video'].CDNSign($data['file_url'].'.f20.mp4', $Config["qcloud"]["CDN_KEY"]),
    ];
}else{
    $Errinfo = '未启用CDN KEY';
    require_once ("views/error.php");
}
if(!$Errinfo) {
    ?>
        <div class="layui-container">
        <h1><?php echo $data['name'] ?></h1>
        <hr/>
        <div class="layui-row">
            <div id="player"></div>
        </div>
        <div class="layui-row" style="margin: 0 0 100px">
            <div class="layui-text">
                <hr />
                <p><?php echo $data['describe'] ?></p>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        <?php if($data['type']==2 && $data['file_url']){   ?>
        const vid = '//<?php echo $Config['domain']['video'].$base_url ?>';
        const dp = new DPlayer({
            container: document.getElementById('player'),
            screenshot: true,
            theme: '#5FB878',
            mutex: true,
            preload: 'auto',
            logo: '/assets/img/logo_.png',
            video: {
                defaultQuality: 1,
                quality: [
                    <?php if($Uid > 0) { ?>
                    {
                        name: '原画',
                        url: '<?php echo $file_url['origin'] ?>',
                        type: 'auto'
                    },
                        <?php }?>{
                        name: '720p',
                        url: '<?php echo $file_url['720p'] ?>',
                        type: 'auto'
                    }, {
                        name: '480p',
                        url: '<?php echo $file_url['480p'] ?>',
                        type: 'auto'
                    }],
                pic: vid + '.0_0.p0.jpg'
            },
            <?php if($data['subtitle_url']){ ?>
            subtitle: {
                color: '#cdcdcd',
                fontSize: '3vw',
                url: '//<?php echo $Config['domain']['video'] . $data['subtitle_url'] ?>'
            },<?php } ?>
        });
        <?php }else if($data['type']==1 && @$data['stream_id']){ ?>

        const dp = new DPlayer({
            container: document.getElementById('player'),
            screenshot: true,
            theme: '#5FB878',
            mutex: true,
            preload: 'auto',
            logo: '/assets/img/logo_.png',
            video: {
                url: 'rmtp://<?php echo $Config["domain"]["live_play"] ?>/live/<?php echo $data['stream_id']?>'
            }
        });
        <?php } ?>

        <?php if(!$Is_login) echo "dp.notice('登录后解锁原画画质哦~', 5000);";?>
        $('.dplayer-logo').addClass('player-logo');
        $('.dplayer-logo').removeClass('dplayer-logo');
    </script>

    <?php
}
?>