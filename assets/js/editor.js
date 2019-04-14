$(function() {
    $("head").append("<link>");
    let css = $("head").children(":last");
    css.attr({rel: "stylesheet", type: "text/css", href: Static + 'css/editormd.min.css'});
    $.getScript(Static + 'js/editormd.min.js', function () {
        let editor = editormd("note_editor", {
            width: "90%",
            height: 600,
            markdown: "### 欢迎来到青草课堂笔记\r\n\r\n笔记其实 too simple",
            path: Static + 'js/em_lib/',
            emoji: true,
            tex  : true,
            toolbarIcons : function() {
                return ["save", "undo", "redo", "|",
                    "bold", "del", "italic", "quote", "|",
                    "h1", "h2", "h3", "h4", "h5", "h6", "|",
                    "list-ul", "list-ol", "hr", "|",
                    "link", "image", "code", "code-block", "table", "emoji", "html-entities", "pagebreak", "||",
                    "watch", "preview"]
            },
            toolbarIconsClass : {
                save : "fa-floppy-o"
            },
            lang : {
                toolbar : {
                    save : "保存"
                }
            },
            onload : function() {
                let keyMap = {
                    "Ctrl-S": function () {
                        alert("已保存");
                    }
                };
                this.addKeyMap(keyMap)
            }
        });
    });
});