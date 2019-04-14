switch (window.location.pathname) {
    case '/user/login':{
        //登录功能处理
        let login = function (captchaObj) {
            layer.load(2);
            captchaObj.appendTo('#captcha');
            captchaObj.onReady(function () {
                layer.closeAll('loading');
                $("#captcha-text").remove();
                $("#captcha-wait").remove();
            });
            $("#btn-reset").click(function () {
                captchaObj.reset();
            });
            $('#btn-login').click(function() {
                const result = captchaObj.getValidate();
                if (!result) {
                    return layer.msg("请完成验证", {icon:2})
                }
                let username = $('#username').val();
                let password = $("#password").val();
                let remember = $("div.layui-form-switch")[0].classList[2] == 'layui-form-onswitch' ? 1 : 0;
                if (username !== "" && password !== "" && (remember===1 || remember===0)) {
                    layer.load(2);
                    $.ajax({
                        type: "POST",
                        url: "/user/login",
                        dataType: "JSON",
                        timeout: 5000,
                        data: {
                            "username": username,
                            "password": password,
                            "remember": remember,
                            "geetest_challenge": result.geetest_challenge,
                            "geetest_validate": result.geetest_validate,
                            "geetest_seccode": result.geetest_seccode
                        },
                        error: function(obj){
                            layer.closeAll('loading');
                            layer.msg("发生错误：" + obj.statusText , {icon:2})
                            captchaObj.reset();
                        },
                        success: function(data) {
                            layer.closeAll('loading');
                            if(data.status === "failed" || data.code < 0){
                                if (data.code === -101){
                                    $('#username').val('');
                                    $('#password').val('');
                                }
                                layer.msg(data.msg , {icon:2});
                                captchaObj.reset();
                            }else{
                                //登录成功
                                layer.msg(data.msg, {icon:1});
                                setTimeout(function () {
                                    $.pjax.reload('#pjax-content')
                                }, 2000)
                            }
                        }})
                } else if(username === "") {
                    captchaObj.reset();
                    layer.msg("用户名不能为空", {icon: 2})
                } else if(password === "") {
                    captchaObj.reset();
                    layer.msg("密码不能为空", {icon: 2})
                } else{
                    captchaObj.reset();
                    layer.msg("少年你干了什么？")
                }
            })
        };
        //请求验证码
        $.ajax({
            url: "/api/captcha?r=" + Math.random(),
            type: "get",
            dataType: "json",
            success: function (data) {
                initGeetest({
                    gt: data.gt,
                    challenge: data.challenge,
                    offline: !data.success, // 表示用户后台检测极验服务器是否宕机
                    new_captcha: data.new_captcha, // 用于宕机时表示是新验证码的宕机
                    product: "popup",
                    width: "300px",
                    https: true
                }, login);
            }
        });
        break;
    }
    case '/user/register':{
        //注册功能处理
        let reg = function (captchaObj) {
            captchaObj.onSuccess(function () {
                let result = captchaObj.getValidate();
                let phoneNumber = $("#phone").val();
                $.ajax({
                    url: '/api/sendSms?r=' + Math.random(),
                    type: 'post',
                    dataType: 'json',
                    data: {
                        phoneNumber : phoneNumber,
                        geetest_challenge: result.geetest_challenge,
                        geetest_validate: result.geetest_validate,
                        geetest_seccode: result.geetest_seccode
                    },
                    success: function (data) {
                        if(data.code === 110){
                            layer.msg("短信已发送至您的手机，请查收", {icon:1});
                            $("#lid").val(data.lid);
                            $("#btn-register").removeClass("layui-btn-disabled");
                            $("#btn-register").attr("title","");
                            $("#sendSmsCode").addClass("layui-btn-disabled");
                            $("#sendSmsCode").attr("title","技能CD中...");
                            (function() {
                                let time = 60;
                                let p = document.getElementById("sendSmsCode");
                                let set = setInterval(function () {
                                    time--;
                                    p.innerHTML = "重新发送验证码(" + time + "s)";
                                    if (time === 0) {
                                        p.innerHTML = "重新发送验证码";
                                        clearInterval(set);
                                        $("#sendSmsCode").removeClass("layui-btn-disabled");
                                        $("#sendSmsCode").attr("title", "")
                                    }
                                }, 1000);
                            })()
                        }else{
                            layer.msg("发生错误： " + data.msg, {icon:2});
                        }
                    }
                });
            });
            $('#sendSmsCode').click(function () {
                if($("#sendSmsCode").attr('class').indexOf('layui-btn-disabled') < 0) {
                    let phonePattern = /^1[34578]\d{9}$/;
                    if (phonePattern.test($("#phone").val())) {
                        captchaObj.verify();
                        $("#sendSmsCode").removeClass("layui-btn-danger");
                    } else {
                        $("#sendSmsCode").addClass("layui-btn-danger");
                        layer.msg("请填写正确的手机号", {icon: 2});
                    }
                }
            });
        };
        $('#btn-register').click(function () {
            if ($("#btn-register").attr('class').indexOf('layui-btn-disabled') < 0) {
                //验证用户名
                let uPattern = /^[a-zA-Z0-9_-]{4,16}$/;
                if(!uPattern.test($("#username").val())){
                    layer.msg("用户名应为4-16位英文字母和数字组成!", {icon:2});
                    return 0;
                }
                let username = $("#username").val();

                //验证电话号
                let phonePattern = /^1[34578]\d{9}$/;
                if (!phonePattern.test($("#phone").val())) {
                    layer.msg("错误的电话号码", {icon:2});
                    return 0;
                }
                let phone = $("#phone").val();

                //验证短信验证码
                let codePattern =  /^-?\d+$/;
                if(!codePattern.test($("#smsCode").val())){
                    layer.msg("错误的短信验证码", {icon:2});
                    return 0;
                }
                let code = $("#smsCode").val();

                //验证密码
                let passPattern = /^.*(?=.{9,})(?=.*\d)(?=.*[A-z]).*$/;
                if(!passPattern.test($("#password").val())){
                    layer.msg("密码至少9位，必须包含数字和字母", {icon:2});
                    return 0;
                }
                let password = $("#password").val();

                //验证学历
                if(!$("#educ").val()){
                    layer.msg("请选择年级！", {icon:2});
                    return 0;
                }
                let edu = $("#educ").val();
                //验证lid
                let lidPattern =  /^-?\d+$/;
                if(!lidPattern.test($("#lid").val())){
                    layer.msg("请验证手机号码", {icon:2});
                    return 0;
                }
                let lid = $("#lid").val();

                //总算验证完了，现在提交数据
                $.ajax({
                    url: '/user/register',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        username : username,
                        phone : phone,
                        code : code,
                        password : password,
                        edu : edu,
                        lid : lid
                    },
                    success: function (data) {
                        if(data.status === 'success' && data.code > 0){
                            layer.msg("注册成功！即将跳转至登录页面...", {icon:1});
                            setTimeout(function () {
                                location.href = '/user/login';
                            }, 2000)
                        }else{
                            layer.msg(data.msg, {icon:2});
                        }
                    }
                })
            }
        });
        //请求验证码
        $.ajax({
            url: "/api/captcha?r=" + Math.random(),
            type: "get",
            dataType: "json",
            success: function (data) {
                initGeetest({
                    gt: data.gt,
                    challenge: data.challenge,
                    offline: !data.success, // 表示用户后台检测极验服务器是否宕机
                    new_captcha: data.new_captcha, // 用于宕机时表示是新验证码的宕机
                    product: "bind",
                    width: "300px",
                    https: true
                }, reg);
            }
        });
        break;
    }
    default:{
        console.error("未知地址,无法找到相应功能")
    }
}