<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - ci.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-02-04 - 16:15
 */
if (!isset($_SERVER['SHELL'])) {
    http_response_code(403);
    die('Access Denied');
}
// ========================//
//       数据库配置          //
// ========================//
$Config["database"]["address"]     = "127.0.0.1";                                 //数据库地址
$Config["database"]["port"]        = 3306;                                        //数据库端口
$Config["database"]["username"]    = "root";                                      //数据库账号
$Config["database"]["password"]    = "";                                          //数据库密码
$Config["database"]["name"]        = "edu";                                       //数据库名称

// 创建连接
$conn = new mysqli($Config["database"]["address"], $Config["database"]["username"], $Config["database"]["password"],
    $Config["database"]["name"]);
// 检查连接
if ($conn->connect_error) {
    echo "数据库连接失败: " . $conn->connect_error;
}
mysqli_set_charset($conn,"utf8");

//qc_user
$conn->query("CREATE TABLE `edu`.`qc_user` ( `uid` INT NOT NULL AUTO_INCREMENT COMMENT '用户Id' , `username` TEXT NOT NULL COMMENT '用户名' , `password` TEXT NOT NULL COMMENT '用户密码' , `salt` TEXT NOT NULL COMMENT '密码盐值' , `reg_date` INT NOT NULL COMMENT '注册时间（时间戳）' , `email` TEXT NOT NULL COMMENT '邮箱' , `phone` TEXT NOT NULL COMMENT '手机号' , PRIMARY KEY (`uid`)) ENGINE = InnoDB;");
$conn->query("CREATE TABLE `edu`.`qc_avatar` ( `uid` INT NOT NULL COMMENT '用户uid' , `avatar_url` TEXT NOT NULL COMMENT '头像Url' ) ENGINE = InnoDB;");

$conn->close();
echo "构建测试成功!";
?>