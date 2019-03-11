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
// 注意：所有项目都是必填项，否则程序无法正常运行！
DEFINE("DEBUG", true);   //是否开启调试模式，建议不在生产环境中启用
DEFINE("DEMO", false);   //是否开启演示模式，请勿启用！
// ========================//
//       数据库配置          //
// ========================//
$Config["database"]["address"]     = "localhost";                                 //数据库地址
$Config["database"]["port"]        = 3306;                                        //数据库端口
$Config["database"]["username"]    = "root";                                      //数据库账号
$Config["database"]["password"]    = "";                                          //数据库密码
$Config["database"]["name"]        = "edu";                                       //数据库名称

// ========================//
//        网站配置          //
// ========================//
$Config["website"]["domain"]        = "";                                          //网站域名
$Config["website"]["title"]         = "青草课堂";                                   //网站标题
$Config["website"]["subtitle"]      = "网络在线教育智能直播课堂解决方案";               //网站副标题
$Config["website"]["static"]        = "/assets/";                                 //网站静态资源目录，可以跨域名
$Config["website"]["https"]         = true;                                       //是否启用SSL

// ========================//
//       域名配置           //
// ========================//
$Config["domain"]["video"]          = "";                                          //视频播放域名
$Config["domain"]["live_stream"]    = "";                                          //直播推流域名
$Config["domain"]["live_play"]      = "";                                          //直播播放域名

// ========================//
//        腾讯云配置         //
// ========================//
$Config["qcloud"]["appid"]          = 233333;                                       //腾讯云AppId
$Config["qcloud"]["sid"]            = "";                                           //腾讯云密钥ID
$Config["qcloud"]["skey"]           = "";                                           //腾讯云密钥Key
$Config["qcloud"]["smsid"]          = "";                                           //腾讯云短信appid
$Config["qcloud"]["smskey"]         = "";                                           //腾讯云短信Key
$Config["qcloud"]["CDN_KEY"]        = "";                                           //腾讯云Token 鉴权Key

// ========================//
//       Geetest配置        //
// ========================//
$Config["geetest"]["id"]            = "";                                            //极验id
$Config["geetest"]["key"]           = "";                                            //极验key

// -=     配置文件结束    =-