<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - config.tpl.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-01-23 - 12:24
 */
// -=这里是青草课堂的配置文件=-

// ========================//
//       数据库配置          //
// ========================//
$Config["database"]["address"]     = "localhost";
$Config["database"]["port"]        = 3306;
$Config["database"]["username"]    = "root";
$Config["database"]["password"]    = "";
$Config["database"]["name"]        = "edu";

// ========================//
//        网站配置          //
// ========================//
$Config["website"]["domain"]        = "";
$Config["website"]["force_https"]    = true;
$Config["website"]["title"]         = "青草课堂";
$Config["website"]["subtitle"]      = "网络在线教育智能直播课堂解决方案";
$Config["website"]["static"]        = "/assets/";

// ========================//
//        腾讯云配置         //
// ========================//
$Config["qcloud"]["appid"]          = 233333;
$Config["qcloud"]["sid"]            = "";
$Config["qcloud"]["skey"]           = "";

// -=     配置文件结束    =-