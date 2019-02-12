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
    die( "数据库连接失败: " . $conn->connect_error);
}
mysqli_set_charset($conn,"utf8");

//qc_user 用户数据基本表
$conn->query("CREATE TABLE `edu`.`qc_user` (
	`uid` INT NOT NULL AUTO_INCREMENT COMMENT '用户Id',
	`username` TEXT NOT NULL COMMENT '用户名',
	`password` TEXT NOT NULL COMMENT '用户密码',
	`reg_date` INT NOT NULL COMMENT '注册时间（时间戳）',
	`email` TEXT NOT NULL COMMENT '邮箱',
	`phone` TEXT NOT NULL COMMENT '手机号',
	`gender` tinyint NOT NULL DEFAULT '0' COMMENT '性别（0:未知，1:男，2:女)',
	PRIMARY KEY (`uid`)
) ENGINE = InnoDB;");
//qc_avatar 用户头像表
$conn->query("CREATE TABLE `edu`.`qc_avatar` (
	`uid` INT NOT NULL COMMENT '用户uid',
	`avatar_url` TEXT NOT NULL COMMENT '头像Url'
) ENGINE = InnoDB;");
//qc_phone_sms 短信验证记录表
$conn->query("CREATE TABLE `edu`.`qc_phone_sms` (
	`lid` INT NOT NULL AUTO_INCREMENT COMMENT 'ID',
	`target` TEXT NOT NULL COMMENT '目标手机号',
	`sendTime` INT NOT NULL COMMENT '发送时间（时间戳）',
	`sendIP` TEXT NOT NULL COMMENT '发送者IP',
	`code` INT NOT NULL COMMENT '短信代码',
	`flag` tinyint NOT NULL DEFAULT '0' COMMENT '使用标记',
	PRIMARY KEY (`lid`)
) ENGINE = InnoDB;");
//qc_user_detail 用户详情信息表
$conn->query("CREATE TABLE `qc_user_detail` (
  `uid` int(11) NOT NULL COMMENT '唯一指定Uid',
  `realname` char NOT NULL COMMENT '用户真实姓名',
  `education` tinyint NOT NULL COMMENT '学历（1：小学，2：初中，3：高中，4：大学，5：其他）',
  `grade` tinyint NOT NULL COMMENT '年级',
  FOREIGN KEY (`uid`) REFERENCES `qc_user` (`uid`)
);");

$conn->close();
echo "构建测试成功!";
?>