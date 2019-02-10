<?php
/**
 *  ___   ____ _
 * / _ \ / ___| | __ _ ___ ___ _ __ ___   ___  _ __ ___
 *| | | | |   | |/ _` / __/ __| '__/ _ \ / _ \| '_ ` _ \
 *| |_| | |___| | (_| \__ \__ \ | | (_) | (_) | | | | | |
 * \__\_\\____|_|\__,_|___/___/_|  \___/ \___/|_| |_| |_|
 * 青草课堂 - register.php
 * Copyright (c) 2015 - 2019.,QCTech ,All rights reserved.
 * Created by: QCTech
 * Created Time: 2019-01-29 - 16:46
 */
//防止被恶意访问，泄露信息
if(!defined('DEBUG')) {
    http_response_code(403);
    exit('Access Denied');
}
if($Is_login){
    header("Location: /user/");
}
?>
<div class="layui-card layui-container">
    <div class="layui-card-header">注册</div>
    <div class="layui-card-body">
        <form class="layui-form">
            <div class="layui-form-item">
                <label class="layui-form-label"><i class="layui-icon layui-icon-username"></i>用户名</label>
                <div class="layui-input-inline">
                    <input type="text" name="username" id="username" required  lay-verify="required" placeholder="请输入您的用户名" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label"><i class="layui-icon layui-icon-cellphone"></i>手机号</label>
                <div class="layui-input-inline">
                    <input type="text" name="phone" id="phone" required  lay-verify="required" placeholder="请输入您的手机号" autocomplete="off" class="layui-input">
                </div>
                <div class="layui-input-inline">
                    <a class="layui-btn layui-btn-sm layui-btn-radius" id="sendSmsCode"></label>发送验证码</a>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label"><i class="layui-icon layui-icon-vercode"></i>验证码</label>
                <div class="layui-input-inline">
                    <input type="number" name="smsCode" id="smsCode" required  lay-verify="required" placeholder="请输入您的短信验证码" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label"><i class="layui-icon layui-icon-password"></i>密码</label>
                <div class="layui-input-inline">
                    <input type="password" name="password" id="password" required lay-verify="required" placeholder="请输入您的密码" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label"><i class="layui-icon layui-icon-read"></i>年级</label>
                <div class="layui-input-inline">
                    <select id="educ" lay-filter="educ" name="educ" lay-search>
                        <option value="">请选择你的学历</option>
                        <option value="1">小学</option>
                        <option value="2">初中</option>
                        <option value="3">高中</option>
                        <option value="4">大学</option>
                        <option value="5">其他</option>
                    </select>
                </div>
            </div>
            <input type="text" style="display: none;" name="lid" id="lid" value="">
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button type="button" class="layui-btn layui-btn-disabled" id="btn-register">提交</button>
                    <button type="reset" class="layui-btn layui-btn-primary" id="btn-reset">重置</button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $view->load_js("gt.min.js"); ?>
<?php $view->load_js("account.js"); ?>
