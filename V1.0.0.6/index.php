<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.6
 + Initial-Time : 2017-5-2 14:25
 + Last-time    : 2017-5-18 15:48 + 小黄牛
 + Desc         : CMD操作界面
 +----------------------------------------------------------------------
*/
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>PHP - CMD 命令行工具</title>
    </head>
    <link rel="stylesheet" type="text/css" href="public/cmd.css">
    <script type="text/javascript" src="public/jquery.min.js"></script>
    
    
<body>

    <div id="cmd_whole">查看命令行大全</div>

    <div class="cmd"  contenteditable="true" style="word-wrap:break-word;  word-break:break-all; ">
        <div contenteditable="false">欢迎使用PHP-CMD 命令行工具，当前版本为：V1.0.0.6</div>
        <div><span class="command-line" contenteditable="false">command line：></span>&nbsp;</div>
    </div>

</body>
</html>
<script type="text/javascript" src="public/juncmd.js"></script>
<script type="text/javascript">

// 监听回车事件
$(document).keypress(function(e) {   
    if(e.which == 13) {
        var html = '<span class="command-line" contenteditable="false">command line：&gt;</span>&nbsp;';
        var txt  = $('.cmd div:last').html().replace(html,'');

        // 发现火狐浏览器的bug，先暂停火狐浏览器的使用
        if (navigator.userAgent.indexOf("Firefox") > 0) {
            var html = $('.cmd').html();
            $('.cmd').html(html+'<div contenteditable="false"><span class="command-echo">command Eco：></span>&nbsp;已发现火狐浏览器BUG，暂不支持火狐浏览器使用！</div>');
            // 设置光标到末尾
            var html = $('.cmd').html();
            $('.cmd').html('');
            $('.cmd').focus(); 
            Focus(html);
            $('.cmd').append('</div>');
            return false;
        }


        $.ajax( {    
            url:'cmd/cmd.class.php', // 路径相对于当前目录，而不是项目根目录
            data:{    
                'cmd':txt    
            },    
            type:'post',    
            cache:false,      
            success:function(data) {
                // alert(data);
                var obj  = eval('('+data+')');  
                // 抛出异常
                if(!obj.code){alert(data);}
                // 未登录
                if(obj.code == 02){
                    var html = $('.cmd').html();
                    $('.cmd').html(html+'<div contenteditable="false"><span class="command-echo">command Eco：></span>&nbsp;'+obj.data+'</div>');
                    var html = $('.cmd').html();
                    $('.cmd').html(html+'<div><span class="command-line" contenteditable="false">command line：></span>&nbsp;user login|name&nbsp;');

                }else{ // 已登录
                    if($.isArray(obj.data)){
                        for(var i=0;i < obj.data.length;i++){
                            var html = $('.cmd').html();
                            $('.cmd').html(html+'<div contenteditable="false"><span class="command-echo">command Eco：></span>&nbsp;'+obj.data[i]+'</div>');
                        }
                    }else{
                        var html = $('.cmd').html();
                        $('.cmd').html(html+'<div contenteditable="false"><span class="command-echo">command Eco：></span>&nbsp;'+obj.data+'</div>');
                    }
                
                    $('.cmd').focus(); 
                    var html = $('.cmd').html();
                    if(obj.code == '01'){
                        $('.cmd').html(html+'<div><span class="command-line" contenteditable="false">command line：></span>&nbsp;'+txt);
                    }else{
                        $('.cmd').html(html+'<div><span class="command-line" contenteditable="false">command line：></span>&nbsp;');
                    }

                }  

                // 判断是否有下载输出
                if(obj.dow){
                    window.open(obj.dow);
                }

                // 设置光标到末尾
                var html = $('.cmd').html();
                $('.cmd').html('');
                $('.cmd').focus(); 
                Focus(html);
                $('.cmd').append('</div>');

                $('.cmd').scrollTop( $('.cmd')[0].scrollHeight );
            }, error : function(){
                alert("路径异常！");    
            }    
        }); 

        return false;
    } 
}); 

</script>