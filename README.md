PHPCMD 命令行插件
===============================================
小黄牛
-----------------------------------------------

### 1731223728@qq.com 

+ 当前最新版本 - V1.0.0.8

+ 作者 - 小黄牛

+ 邮箱 - 1731223728@qq.com     


## 本次改版重要说明

+ 本次改版主要在loo 命令行分支中新增一条 vif 指令，用于日常扫描文件代码是否存在安全隐藏

### 本次改版详细说明

+ 1、新增：扫描文件代码是否存在安全隐藏


``` 
loo vif false或需要不被扫描的目录，用|符合隔开；当为false时禁用系统默认的绕过目录[可选]

系统默认不扫描目录有：cmd, thinkphp, ThinkPHP, yii, vendor

系统只扫描以下类型的文件内容：.php, .html, .htm, .txt, .log, .json, .arr, .array, .con, .conf, .config

系统只扫描以下PHP系统关键字：

    1、可能会被直接SQL注入的原生变量
		$_get, $_post, $_session, $_cookie 
	
    2、可能会被非法运行代码的系统方法
		system, exec, passthru, shell_exec, popen, proc_open, pcntl_exec, 
	
    3、可能会被非法运行函数的系统方法
		'create_function, call_user_func_array, call_user_func, assert, 
	
    4、可能会被非法覆盖变量提交的系统方法
		'parse_str, mb_parse_str, import_request_variables
```