//登录功能处理
var handler = function (captchaObj) {
    layer.load(2);
    captchaObj.appendTo('#captcha');
    captchaObj.onReady(function () {
        layer.closeAll('loading');
        $("#captcha-text").remove();
        $("#captcha-wait").remove();
    });
    $("#btn-reset").click(function () {
        captchaObj.reset();
    })
    $('#btn-login').click(function() {
        var result = captchaObj.getValidate();
        if (!result) {
            return layer.msg("请完成验证", {icon:2})
        }
        var username = $('#username').val();
        var password = $("#password").val();
        var remember = $("div.layui-form-switch")[0].classList[2] == 'layui-form-onswitch' ? 1 : 0;
        if (username != "" && password != "" && (remember==1 || remember==0)) {
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
                    if(data.status == "failed" || data.code < 0){
                        if(data.msg == 'OK'){
                            layer.msg("服务器发生错误" , {icon:2});
                        }else{
                            layer.msg(data.msg , {icon:2});
                        }
                        captchaObj.reset();
                    }else{
                        //登录成功
                        layer.msg(data.msg, {icon:1});
                    }
                }})
        } else if(username == "") {
            captchaObj.reset();
            layer.msg("用户名不能为空", {icon: 2})
        } else if(password == "") {
            captchaObj.reset();
            layer.msg("密码不能为空", {icon: 2})
        } else{
            captchaObj.reset();
            layer.msg("少年你干了什么？")
        }
    })
};
//注册功能处理

//请求验证码
$.ajax({
    url: "/api/captcha",
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
        }, handler);
    }
});