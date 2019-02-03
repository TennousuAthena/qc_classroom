<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - index.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-01-23 - 12:13
 */
DEFINE("DEBUG", true);   //是否开启调试模式，建议不在生产环境中启用
//关掉Notice
error_reporting(E_ALL^E_NOTICE);
// 引入配置文件
if(!file_exists("./config.php")) die("站点尚未初始化，请将config.tpl.php重命名为config.php并进行配置！");
require_once ("config.php");
//http method检查
if (!in_array($_SERVER['REQUEST_METHOD'], array('GET', 'POST'))) {
    exit('Unsupported HTTP method');
}
//引入功能函数库文件
require_once ("includes/function.php");
//开启session
session_start();
// 域名检测
if($_SERVER['HTTP_HOST'] != $Config["website"]["domain"]) header("Location: "."http://".$Config["website"]["domain"]);
//数据库连接检测
$conn = new mysqli($Config["database"]["address"], $Config["database"]["username"], $Config["database"]["password"]);
if ($conn->connect_error) die("<h1>发生致命错误</h1><br />连接失败: " . $conn->connect_error);

//Router
$NotFound = true;
$HTTPParameters = array();
if (in_array($_SERVER['REQUEST_METHOD'], array('PUT', 'DELETE', 'OPTIONS'))) {
    parse_str(file_get_contents('php://input'), $HTTPParameters);
}
$Routes = array(
    'GET' => array(),
    'POST' => array()
);


//Support HTTP Method: GET / POST
/*
 * $Routes的第一维存放请求类型，第二维存放路径
 * 在括号内添加正则，<>内为页面变量名称，变量值为指向目标控制器
 */
$Routes['GET']['/']                                                                        = 'home';
$Routes['GET']['/sign_up']                                                                 = 'show';
$Routes['GET']['/list(/page/(?<page>[0-9]+))?']                                            = 'list';
$Routes['GET']['/user/(?<method>.*)']                                                      = 'user';
$Routes['POST']['/user/(?<method>.*)']                                                     = 'user';
$Routes['GET']['/api/(?<mod>.*)']                                                     = 'api';


//这里是Routes End


$UrlPath = 'home';
$ParametersVariableName = '_' . $_SERVER['REQUEST_METHOD'];
foreach ($Routes[$_SERVER['REQUEST_METHOD']] as $URL => $Controller) {
    if (preg_match("#^" . $URL . "$#i", $_SERVER['REQUEST_URI'], $Parameters)) {
        $NotFound = false;
        $Parameters = array_merge($Parameters, $HTTPParameters);
        foreach ($Parameters as $Key => $Value) {
            if (!is_int($Key)) {
                ${$ParametersVariableName}[$Key] = urldecode($Value);
                $_REQUEST[$Key] = urldecode($Value);
            }
        }
        $UrlPath = $Controller;
        break;
    }
}
if ($NotFound === true) {
    require_once("controller/http_error.php");
    die();
}
if(!file_exists("controller/".$UrlPath.".php")){
    require_once("controller/http_error.php");
    die("路由出现问题");
}
//引入页面模板
require_once("./views/common.php");