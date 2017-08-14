
$(".code-out").click(function(){
    code_out();
});
function code_out(){
    $(".code-upd").fadeOut(1000); 
    // 清除编辑器
    $('.CodeMirror').slideUp(1100, function(){ $(this).remove() });
}

function prompt(Data) {  
		// 设置默认值
		var Data       = arguments[0]    ? arguments[0]    : '{}';   
		var Id         = Data.Id         ? Data.Id         : '';  
		var Content    = Data.Content    ? Data.Content    : ''; 
		var Color      = Data.Color      ? Data.Color      : 'red'; 
		var OutState   = Data.OutState   ? Data.OutState   : true; 
		var OutTime    = Data.OutTime    ? Data.OutTime    : 2500;
		$('#'+Id).html(Content);

		// 修改初始化样式
		$('#'+Id).css('position','absolute');
		$('#'+Id).css('opacity','-10');
		$('#'+Id).css('left','0px');
		$('#'+Id).css('z-index','99999999999999999999');
		$('#'+Id).css('color',Color);
        
		// 向左滑渐现
		$("#"+Id).animate({left:'0px',opacity:'1'},700);

		// 开启自动关闭功能
		if(OutState == true){
			window.setTimeout(function(){
				// 向右滑渐隐
				$("#"+Id).animate({left:'0px',opacity:'0'},700);
			},OutTime);
		}

        $('#'+Id).click(function(){
			// 向右滑渐隐
			$("#"+Id).animate({left:'0px',opacity:'0'},700);
        });
}; 

function cmd_code(model){
    if(model == 'php') {
        model = 'application/x-httpd-php';
    }else if(model == 'xml') {
        model = 'text/xml';
    }else if(model == 'js') {
        model = 'text/javascript';
    }else if(model == 'css') {
        model = 'text/css';
    }else if(model == 'sql') {
        model = 'text/x-sql';
    }else{
        model = 'text/html';
    }
    
    var type   = {
        // 高亮显示-关联着自动补齐
        /**
         * application/x-httpd-php
         * text/css
         * text/javascript
         * text/html
         * text/x-sql
        */
        mode:'application/x-httpd-php',

        //显示行号
        lineNumbers:true,

        //设置主题
        theme:"seti",

        //代码折叠
        lineWrapping:true,
        foldGutter: true,
        gutters:["CodeMirror-linenumbers", "CodeMirror-foldgutter"],

        //全屏模式
        fullScreen:true,

        //括号匹配
        matchBrackets:true,
        completeSingle: false,
        selectionPointer: true,
        // 按下ctrl键唤起智能提示
        extraKeys:{
            "Ctrl": "autocomplete",
        }
    };
    var editor = CodeMirror.fromTextArea(document.getElementById("code"), type);

    // 满足自动触发自动联想功能 
    document.onkeydown=function(e){
        e=window.event||e;
        switch(e.keyCode){
            case 8:  break;//删除键
            case 13: break;//回车键
            case 27: 
            case 96: break;//清屏键
            case 37: break;//左键
            case 38: break;//向上键
            case 39: break;//右键
            case 40: break;//向下键
            default:
                if($('.code-upd').css('display') == 'block'){
                    editor.showHint();
                }
            break;
        }
    }

    // 提交文件保存更新
    $(".code-post").click(function(){
        $.ajax( {    
            url:'cmd/post.class.php', // 路径相对于当前目录，而不是项目根目录
            data:{    
                'type':'upd',
                'content':editor.getValue()
            },    
            type:'post',    
            cache:false,      
            success:function(data) {
                // alert(data);
                var obj  = eval('('+data+')');  
                // 抛出异常
                if(!obj.code){alert(data);}

                if(obj.code == '00' || obj.code == '01'){
                    prompt({'Id':'code-vif','Content':obj.data});
                }else if(obj.code == '02'){
                    code_out();
                    var html = $('.cmd').html();
                    $('.cmd').html(html+'<div contenteditable="false"><span class="command-echo">command Eco：></span>&nbsp;'+obj.data+'</div>');
                    var html = $('.cmd').html();
                    $('.cmd').html(html+'<div><span class="command-line" contenteditable="false">command line：></span>&nbsp;user login|name&nbsp;');
                    // 设置光标到末尾
                    var html = $('.cmd').html();
                    $('.cmd').html('');
                    $('.cmd').focus(); 
                    Focus(html);
                    $('.cmd').append('</div>');
                    $('.cmd').scrollTop( $('.cmd')[0].scrollHeight );
                }
            }, error : function(){
                alert("路径异常！");    
            }    
        }); 
    });
}

// 点击URL，提交获取文件内容
$(document).on('click','span', function(){
    var file = $(this).html();
	$.ajax( {    
        url:'cmd/post.class.php', // 路径相对于当前目录，而不是项目根目录
        data:{    
            'type':'click',
            'url':file
        },    
        type:'post',    
        cache:false,      
        success:function(data) {
            var obj  = eval('('+data+')');  
            // 抛出异常
            if(!obj.code){alert(data);}
            if(obj.code == '05'){
                $('#code').val(obj.data);
                $(".code-upd").fadeIn(1000);  
                cmd_code(obj.msg);
            }else if(obj.code == '02'){
                code_out();
                var html = $('.cmd').html();
                $('.cmd').html(html+'<div contenteditable="false"><span class="command-echo">command Eco：></span>&nbsp;'+obj.data+'</div>');
                var html = $('.cmd').html();
                $('.cmd').html(html+'<div><span class="command-line" contenteditable="false">command line：></span>&nbsp;');
                // 设置光标到末尾
                var html = $('.cmd').html();
                $('.cmd').html('');
                $('.cmd').focus(); 
                Focus(html);
                $('.cmd').append('</div>');
                $('.cmd').scrollTop( $('.cmd')[0].scrollHeight );
            }else if(obj.code == '02'){
                code_out();
                var html = $('.cmd').html();
                $('.cmd').html(html+'<div contenteditable="false"><span class="command-echo">command Eco：></span>&nbsp;'+obj.data+'</div>');
                var html = $('.cmd').html();
                $('.cmd').html(html+'<div><span class="command-line" contenteditable="false">command line：></span>&nbsp;user login|name&nbsp;');
                // 设置光标到末尾
                var html = $('.cmd').html();
                $('.cmd').html('');
                $('.cmd').focus(); 
                Focus(html);
                $('.cmd').append('</div>');
                $('.cmd').scrollTop( $('.cmd')[0].scrollHeight );
            }
        }, error : function(){
            alert("路径异常！");    
        }    
    }); 
});