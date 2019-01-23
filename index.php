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
// 引入配置文件
if(!file_exists("./config.php")) die("站点尚未初始化，请将config.tpl.php重命名为config.php并进行配置！");
include_once ("config.php");
//http method检查
if (!in_array($_SERVER['REQUEST_METHOD'], array('GET', 'POST'))) {
    exit('Unsupported HTTP method');
}
//引入功能函数库文件
include_once ("includes/function.php");
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
//这里是Routes Start
$Routes['GET']['/']                                                                        = 'home';
$Routes['GET']['/list(/page/(?<page>[0-9]+))?']                                            = 'list';
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
    include_once ("controller/404.php");
    die();
}

//引入页面模板
include_once ("./views/common.php");