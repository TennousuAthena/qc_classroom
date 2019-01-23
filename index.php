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
// 域名检测
if($_SERVER['HTTP_HOST'] != $Config["website"]["domain"]) header("Location: "."http://".$Config["website"]["domain"]);
//数据库连接检测
$conn = new mysqli($Config["database"]["address"], $Config["database"]["username"], $Config["database"]["password"]);
if ($conn->connect_error) die("<h1>发生致命错误</h1><br />连接失败: " . $conn->connect_error);
//引入页面模板
include_once ("./views/common.php");