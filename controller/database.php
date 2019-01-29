<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - database.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-01-29 - 13:29
 */
// 创建连接
$conn = new mysqli($Config["database"]["address"], $Config["database"]["username"], $Config["database"]["password"],
    $Config["database"]["name"]);
// 检查连接
if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}
mysqli_set_charset($conn,"utf8");

// qc_user 表检查
$result = $conn->query("SELECT id, firstname, lastname FROM ".$Config["database"]["prefix"]."user");

if ($result->num_rows <= 0) {
    //没有数据
    $conn->query("CREATE TABLE `edu`.`qc_user` ( `uid` INT NOT NULL AUTO_INCREMENT COMMENT '用户Id' , `username` TEXT NOT NULL COMMENT '用户名' , `password` TEXT NOT NULL COMMENT '用户密码' , `salt` TEXT NOT NULL COMMENT '密码盐值' , `reg_date` INT NOT NULL COMMENT '注册时间（时间戳）' , `email` TEXT NOT NULL COMMENT '邮箱' , `phone` TEXT NOT NULL COMMENT '手机号' , PRIMARY KEY (`uid`)) ENGINE = InnoDB;");

    /*
    if (!mysqli_query($conn, "INSERT INTO `qc_user` (`uid`, `username`, `password`, `salt`, `reg_date`, `email`, `phone`) VALUES ('1', 'admin', '$2y$12$233333333333333333333uSsLNq6Pw15iP56uSUEXTx7UXE/OjRPW', '2333333333333333333333', '1', 'qingcaomc@gmail.com', '12333333333')")) {
        die("Error: " . $sql . "<br>" . mysqli_error($conn));
    }
    */
}
// qc_ugroup 表检查


$conn->close();

$usercenter = new usercenter();
echo $usercenter->pass_crypt("233333")['password'];