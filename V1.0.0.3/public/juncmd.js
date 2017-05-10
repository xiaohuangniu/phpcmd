
/*! juncmd.js | (c) 20017 小黄牛 - 1731223728@qq.com | 转载请著名原创作者 */
// 设置CMD界面全屏
$('.cmd').css('height', ($(window).height()-20)+'px');
$('.cmd').css('width' , ($(window).width()-20)+'px');

var cursor_status = 1; 
var cursor_model  = false; 

// 开机显示打字机
(function(a) {
      a.fn.typewriter = function(speed) {
          this.each(function() {
             var d = a(this),
              c = d.html(),
              b = 0;
              d.html("");
             var e = setInterval(function() {
                  var f = c.substr(b, 1);
                 if (f == "<") {
                     b = c.indexOf(">", b) + 1
                 } else {
                     b++
                 }
                 d.html(c.substring(0, b) );
                 if (b >= c.length) {
                     clearInterval(e)
                 }
             },
             speed)
         });
         cursor_model = true;
         return this;
     }
 })(jQuery);
$(".cmd").typewriter(0);

// 打字机显示完自动获取光标
setTimeout(function(){
    var html = $('.cmd').html();
    $('.cmd').html('');
    $('.cmd').focus(); 
    Focus(html);  
}, 700);


// 快捷键
$(document).keyup(function(event){
    switch(event.keyCode) { 
    // ESC键 - 清空当前屏幕
    case 27:
    case 96:
        $('.cmd').html('');
        $('.cmd').focus(); 
        Focus('<div><span class="command-line" contenteditable="false">command line：></span>&nbsp;');
        $('.cmd').append('</div>');
        breack;
    }

});


// 设置光标位置一直在末尾
function Focus(html) {
    var sel, range;
    if (window.getSelection) {
        // IE9 and non-IE
        sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
            range = sel.getRangeAt(0);
            range.deleteContents();
            // Range.createContextualFragment() would be useful here but is
            // non-standard and not supported in all browsers (IE9, for one)
            var el = document.createElement("div");
            el.innerHTML = html;
            var frag = document.createDocumentFragment(), node, lastNode;
            while ( (node = el.firstChild) ) {
                lastNode = frag.appendChild(node);
            }
            range.insertNode(frag);
            // Preserve the selection
            if (lastNode) {
                range = range.cloneRange();
                range.setStartAfter(lastNode);
                range.collapse(true);
                sel.removeAllRanges();
                sel.addRange(range);
            }
        }
    } else if (document.selection && document.selection.type != "Control") {
        // IE < 9
        document.selection.createRange().pasteHTML(html);
    }
 }

// 查看命令行大全
$('#cmd_whole').click(function(){
    var html = $('.cmd').html();
    var txt = '';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 命令行工具当前版本 - V1.0.0.3</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 作者 - 小黄牛</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 邮箱 - 1731223728@qq.com</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 快捷键：ESC 清空当前界面</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 命令行介绍如下：</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 本工具是一个可嵌入在后台管理系统中的便捷式开发工具，适用于中小型项目，研发理念为，方便程序员的日常项目维护，正常状态下的程序调试；</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 本工具可用于商业用途，但不可随意更改作者著作权。</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 命令行使用说明：</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 初次使用命令行，系统提供了一个初始用户，账号密码：admin</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 使用命令行之前，需要先登录工具，进入页面后敲下回车，即可快捷弹出登录指令</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 每个命令行的提交都是使用【回车键】</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 命令行每个参数之间，都是用一个空格进行分割，例如登录指令：user login|name 账号 密码</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 命令行的组成方式：</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 主类 子类 参数1 参数2 参数N </div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 1、工具账号操作指令：</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 注册账号 : user reg 账号[必填] 密码[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 删除账号 : user del 账号[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 修改账号 : user upd 账号[必填] 密码[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 退出工具 : user exit</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 登录工具 : user login|name 账号[必填] 密码[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 查看管理员列表 : user -l 列举所有管理员</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 查看管理员日志 : user log 管理员账号[可选] (只有admin账号可以查看别人的日志)</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ (命令行工具的用户依赖文件作为存储方式，存放地址为：cmd/config/user/)</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 2、工具配置操作指令：</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 新增配置 : conf add 键名[必填] 值[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 修改配置 : conf upd 键名[必填] 值[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 删除配置 : conf del 键名[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 查看单个配置 : conf sel 键名[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 列举所有配置 : conf -l</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ (命令行工具的配置为一维数组，存放地址为：cmd/config/config.php)</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 3、MySql数据库操作指令：</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 检测MySql链接 my -g</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 执行CURD命令  my -x SQL语句[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 数据库分卷备份 my -b 表名或分卷大小,必须是整数[可选] 分卷大小M,必须是整数[可选]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 数据库备份恢复(选择v1开头的版本，会自动恢复所有分卷) my -i sql文件名，不带文件夹[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 数据库备份打包与下载 my -z sql文件名，不带文件夹[必填]  是否需要下载,不为空即可[可选]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 注意：在使用my指令之前，应该使用conf指令修改工具配置文件中的数据库配置信息</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 注意：my -x指令 暂只支持：select delete update insert 四种SQL语句</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 4、PHP环境的相关操作指令：</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 打印服务器基本配置 : php -w</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 打印服务器已编译模块 : php -c</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 打印PHP系统相关参数 : php -l</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 打印PHP相关组件扩展 : php -z</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 打印数据库相关扩展与配置参数 : php -m</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 5、目录的相关操作指令：</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 简单的列举目录下的所有文件 mk -l 目录路径[可选]  不填的情况下使用配置文件中的根目录</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 详细的列举目录下的所有文件 mk -ll 目录路径[可选]  不填的情况下使用配置文件中的根目录</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 相对于配置文件下，新增目录 mk -a 新增目录的路径包含目录名[必填] 0755权限[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 自定义路径下，新增目录 mk -a 目标路径[必填] 相对目标路径新增目录的路径包含目录名[必填] 0755权限[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 检测目录是否存在 mk -s 检测路径[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 检测目录是否存在，若不存在则创建目录 mk -s 检测路径[必填] -y[必填] 0755权限[必填] </div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 删除目录 mk -d 目录路径[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 修改目录名 mk -u 原始路径[必填]  目标路径[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 6、文件的相关操作指令：</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 删除文件 tk -d 文件路径[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 检测文件是否存在 tk -s 文件路径[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 修改文件名 tk -u 原始文件路径[必填]  目标文件路径[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 复制文件 tk -c 原始文件路径[必填]  目标文件路径[必填]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 7、Txt和Log文件的相关操作指令：</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 读取内容 txt -l 文件路径[必填] 读取函数，最大100[可选]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 替换内容 txt -t 文件路径[必填] 原始字符串[必填] 目标字符串[可选]</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ config.php配置文件的一些系统项说明：</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 数据库类型 : DB_TYPE</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 数据库地址 : DB_HOST</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 数据库名称 : DB_NAME</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 数据库账号 : DB_USER</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 数据库密码 : DB_PWD</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 数据库端口 : DB_PORT</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 数据库编码 : DB_CHARSET</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+ 命令行可操作的根目录 : CD_PATH</div>';
    txt += '<div contenteditable="false"><span class="command-cmd">CMD：></span>&nbsp;+---------------------------------------------------------------------------------------------------------------------------------------------------------------------------</div>';
    txt += '<div><span class="command-line" contenteditable="false">command line：></span>&nbsp;';
    $('.cmd').html(html+txt);

    // 设置光标到末尾
    var html = $('.cmd').html();
    $('.cmd').html('');
    $('.cmd').focus(); 
    Focus(html);
    $('.cmd').append('</div>');

});