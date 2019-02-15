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
mysqli_report(MYSQLI_REPORT_STRICT);
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
    die( "Error 数据库连接失败: " . $conn->connect_error);
}
mysqli_set_charset($conn,"utf8");

//设置编码
$conn->query("ALTER DATABASE `". $Config["database"]["name"] ."` COLLATE utf8_swedish_ci");

//qc_group 用户组表
$conn->query("CREATE TABLE `qc_group` (
  `gid` tinyint NOT NULL COMMENT '用户组ID',
  `name` char NOT NULL COMMENT '用户组名称'
);");
$conn->query("ALTER TABLE `qc_group`
ADD PRIMARY KEY `gid` (`gid`);");
//qc_group 默认数据
$qc_group = [
    0 => '临时封禁',
    1 => '学生',
    2 => '教师',
    3 => '管理员',
];
foreach ($qc_group as $key => $value){
    $conn->query("INSERT INTO `qc_group` (`gid`, `name`)
  VALUES ('". $key ."', '". $value ."');");
}
//qc_user 用户数据基本表
$conn->query("CREATE TABLE `qc_user` (
	`uid` INT NOT NULL AUTO_INCREMENT COMMENT '用户Id',
	`username` TEXT NOT NULL COMMENT '用户名',
	`password` TEXT NOT NULL COMMENT '用户密码',
	`reg_date` INT NOT NULL COMMENT '注册时间（时间戳）',
	`email` TEXT NOT NULL COMMENT '邮箱',
	`phone` TEXT NOT NULL COMMENT '手机号',
	`gender` tinyint NOT NULL DEFAULT '0' COMMENT '性别（0:未知，1:男，2:女)',
	`gid` tinyint(4) NOT NULL COMMENT '用户所在组',
    FOREIGN KEY (`gid`) REFERENCES `qc_group` (`gid`),
	PRIMARY KEY (`uid`)
) ENGINE = InnoDB;");
//qc_avatar 用户头像表
$conn->query("CREATE TABLE `qc_avatar` (
  `uid` int(11) NOT NULL COMMENT '用户uid',
  `avatar_url` text NOT NULL COMMENT '头像Url',
  FOREIGN KEY (`uid`) REFERENCES `qc_user` (`uid`)
);");
$conn->query("ALTER TABLE `qc_avatar`
ADD FOREIGN KEY (`uid`) REFERENCES `qc_user` (`uid`);");
//qc_phone_sms 短信验证记录表
$conn->query("CREATE TABLE `qc_phone_sms` (
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
//qc_log 日志表
$conn->query("CREATE TABLE `qc_log` (
  `lgid` int NOT NULL COMMENT '日志Id' AUTO_INCREMENT PRIMARY KEY,
  `type` char NOT NULL COMMENT '日志类型',
  `detail` text NOT NULL COMMENT '日志内容',
  `ip` char NOT NULL COMMENT '操作IP',
  `uid` int NOT NULL COMMENT '操作uid',
  `env` text NOT NULL COMMENT '操作环境',
  `result` tinyint NOT NULL COMMENT '操作结果（小于0失败，大于0成功）'
);");

$conn->close();
echo "青草课堂 ：构建测试成功! \n";
?>