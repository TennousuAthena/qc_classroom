//如果是IE
if($.zui.browser.isIE()){
    console.warn("正在使用不支持的浏览器");
    $.zui.browser.tip();
}
//初始化提示框
$('[data-toggle="tooltip"]').tooltip();