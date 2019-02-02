//登录功能处理
$('#btn-login').click(function() {
    var username = $('#username').val();
    var password = $("#password").val();
    var remember = $("div.layui-form-switch")[0].classList[2] == 'layui-form-onswitch' ? 1 : 0;
    if (username != "" && password != "" && (remember==1 || remember==0)) {
        layer.load(2);
        $.ajax({
            type: "POST",
            url: "/user/login",
            dataType: "JSON",
            data: {
                "username": username,
                "password": password,
                "remember": remember
            },
            success: function(data) {
                layer.closeAll('loading');
                console.log(data)
        }})
    } else {
        layer.msg("请检查您的输入")
    }
})
//注册功能处理
