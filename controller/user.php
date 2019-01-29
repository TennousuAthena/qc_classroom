<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - user.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-01-29 - 14:34
 */
switch ($Parameters['method']){
    case '':
        {
            //用户中心

            break;
        }
    case 'register':
        {
            //注册

            break;
        }
    case 'login':
        {
            //登录
            require_once ("views/user/login.php");
            break;
        }
    case  'forget_password':
        {
            //忘记密码

            break;
        }
    default:
        {
            header("Location: /not_found");
            die();
        }
}