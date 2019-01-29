<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - usercenter.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-01-29 - 14:13
 */

class usercenter
{
    /**
     * 加密用户密码
     * @param $password
     * @return array
     */
    public function pass_crypt($password, $salt = ''){
        $options['cost'] = 12;
        if($salt == ''){
            $options['salt'] = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
        }else{
            $options['salt'] = $salt;
        }
        return array(
            'salt' => $options['salt'],
            'password' => password_hash($password, PASSWORD_BCRYPT, $options)
        );
    }
}