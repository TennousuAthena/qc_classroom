//layui
var util = layui.util
    ,laydate = layui.laydate
    ,layer = layui.layer;

//固定块
util.fixbar({
    bar1: true
    ,bar2: true
    ,css: {right: 50, bottom: 100}
    ,bgcolor: '#393D49'
    ,click: function(type){
        if(type === 'bar1'){
            layer.msg('我也不知道我该说什么')
        } else if(type === 'bar2') {
            layer.msg('我更不知道了')
        }
    }
});

//localstorage验证
if(window.localStorage && (window.localStorage.setItem('lstest', 'true') , window.localStorage.getItem('lstest') == "true")){
    //把第一次来的用户引到 /sign_up
    //这辈子写过最玄学的JS（捂脸），改天改掉
    if(window.localStorage.getItem('flag') < 1){
        window.location.href = '/sign_up';
    }
    if(window.localStorage.getItem('flag') < 11) {
        window.localStorage.setItem('flag', window.localStorage.getItem('flag') + 1);
    }
}else{
    layer.msg("您的浏览器不支持LocalStorage 部分功能将受到影响")
}
