<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 实体化类
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.1
 + Initial-Time : 2017-5-2 18:43
 + Last-time    : 2017-5-2 18:43 + 小黄牛
 + Desc         : 操作PHP.ini的命令行
 +              : php -w         打印服务器基本配置
 +              : php -c         打印服务器已编译模块
 +              : php -l         打印PHP系统相关参数
 +              : php -z         打印PHP相关组件扩展
 +              : php -m         打印数据库相关扩展与配置参数
 +----------------------------------------------------------------------
*/

# 引入命令行基类
require_once('CmdInterface.php');

class php implements CmdInterface{
    private $command_data; // 命令行参数 数组
	private $config_data;  // 命令行 配置参数
	private $config_path;  // 命令行 配置路径

    public function  __construct($txt){
        $this->command_data = $txt;
		$this->config_path  = 'config/config.php';
		$this->config_data  = require_once($this->config_path);
    }

    public function Go(){
		# 第四个参数，不管是不是中文，先过滤一遍
		if(!empty($this->command_data[3])){
			$this->command_data[3] = iconv('utf-8', 'gbk', $this->command_data[3]);
		}
		# 分支
        switch ($this->command_data[1]){
			case '-w' : 
				$res = $this->W();
			break;
			case '-c' : 
				$res = $this->C();
			break;  
			case '-l' : 
				$res = $this->L();
			break;
			case '-z' : 
				$res = $this->Z();
			break; 
			case '-m' : 
				$res = $this->M();
			break;  
			default  :
				$res = [
					'code' => '01',
					'data' => '暂无该操作类型',
				];
		}
		return $res;
    }

	/**
	 * 打印服务器基本参数
	 */
	public function W(){
		# IP
		if('/'==DIRECTORY_SEPARATOR){$ip_name = $_SERVER['SERVER_ADDR'];}else{$ip_name = @gethostbyname($_SERVER['SERVER_NAME']);}
		$ip = '服务器域名/IP地址 - '.$_SERVER['SERVER_NAME'].'( '.$ip_name.' )';
		
		# 操作系统
		$os = explode(" ", php_uname());
		if('/'==DIRECTORY_SEPARATOR){$xp_name = $os[2];}else{$xp_name = $os[1];}
		$xp = '服务器操作系统 - '.$os[0].' 内核版本：'.$xp_name;

		# 解译引擎
		$ap = 'Apache/Nginx - '.$_SERVER['SERVER_SOFTWARE'];
		# 服务器语言
		$la = '服务器语言 - '.getenv("HTTP_ACCEPT_LANGUAGE");
		# 服务器端口
		$pt = '服务器端口 - '.$_SERVER['SERVER_PORT'];
		# 绝对路径
		$fl = '绝对路径 - '.$_SERVER['DOCUMENT_ROOT']?str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']):str_replace('\\','/',dirname(__FILE__));

		$data[] = $ip;
		$data[] = $xp;
		$data[] = $ap;
		$data[] = $la;
		$data[] = $pt;
		$data[] = $fl;
		return ['code'=>'00', 'data'=>$data];
	}

	/**
	 * 打印服务器已编译模块
	 */
	public function C(){
		$able=get_loaded_extensions();
		foreach ($able as $val) {
			$data[] = $val;
		}
		return ['code'=>'00', 'data'=>$data];
	}

	/**
	 * 打印PHP相关参数 系统
	 */
	public function L(){
		$ve = 'PHP版本 - '.PHP_VERSION;
		$ps = 'PHP运行方式 - '.strtoupper(php_sapi_name());
		$ml = '脚本最大内存 - '.$this->show("memory_limit");
		$sm = 'PHP安全模式 - '.$this->show("safe_mode");
		$ms = 'POST最大限制 - '.$this->show("post_max_size");
		$mf = '上传文件最大限制 - '.$this->show("upload_max_filesize");
		$pr = '浮点数有效位数 - '.$this->show("precision");
		$et = '脚本超时时间 - '.$this->show("max_execution_time").'秒';
		$st = 'socket超时时间 - '.$this->show("default_socket_timeout").'秒';
		$dr = '页面根目录【doc_root】 - '.$this->show("doc_root");
		$ur = '用户根目录【user_dir】 - '.$this->show("user_dir");
		$dl = 'dl()函数【enable_dl】 - '.$this->show("enable_dl");	
		$ip = '是否指定包含文件根目录 - '.$this->show('include_path');
		$er = '显示错误信息 - '.$this->show('display_errors');
		$rg = '自定义全局变量 - '.$this->show('register_globals');
		$qg = '数据反斜杠转义 - '.$this->show('magic_quotes_gpc');
		$so = 'PHP短标签 - '.$this->show('short_open_tag');
		$re = '忽略重复错误信息 - '.$this->show('ignore_repeated_errors');
		$rs = '忽略重复的错误源 - '.$this->show('ignore_repeated_source');
		$rm = '报告内存泄漏 - '.$this->show('report_memleaks');
		$qg = '自动字符串转义 - '.$this->show('magic_quotes_gpc');
		$qr = '外部字符串自动转义 - '.$this->show('magic_quotes_runtime');
		$au = '打开远程文件 - '.$this->show('allow_url_fopen');
		$ra = '声明argv和argc变量 - '.$this->show('register_argc_argv');
		$cookie = isset($_COOKIE) ? '<font color="green">√</font>' : '<font color="red">×</font>';
		$ck = 'Cookie 支持 - '. $cookie;
		$ac = '拼写检查 - '.$this->isfun("aspell_check_raw");
		$bc = '高精度数学运算 - '.$this->isfun("bcadd");
		$pm = 'PREL相容语法 - '.$this->isfun("preg_match");
		$pc = 'PDF文档支持 - '.$this->isfun("pdf_close");
		$sn = 'SNMP网络管理协议 - '.$this->isfun("snmpget");
		$vm = 'VMailMgr邮件处理 - '.$this->isfun("vm_adduser");
		$ci = 'CURL支持 - '.$this->isfun("curl_init");
		$SMTP_I = get_cfg_var("SMTP") ? '<font color="green">√</font>' : '<font color="red">×</font>';
		$sp = 'SMTP支持 - '.$SMTP_I;
		$smtp_l = get_cfg_var("SMTP") ? get_cfg_var("SMTP") : '<font color="red">×</font>';
		$su = 'SMTP地址 - '.$smtp_l;

		$data[] = $ve ;
		$data[] = $ps;
		$data[] = $ml;
		$data[] = $sm;
		$data[] = $ms;
		$data[] = $mf;
		$data[] = $pr;
		$data[] = $et;
		$data[] = $st;
		$data[] = $dr;
		$data[] = $ur;
		$data[] = $dl;
		$data[] = $ip;
		$data[] = $er;
		$data[] = $rg;
		$data[] = $qg;
		$data[] = $so;
		$data[] = $re;
		$data[] = $rs;
		$data[] = $rm;
		$data[] = $qg;
		$data[] = $qr;
		$data[] = $au;
		$data[] = $ra;
		$data[] = $ck;
		$data[] = $ac;
		$data[] = $bc;
		$data[] = $pm;
		$data[] = $pc;
		$data[] = $sn;
		$data[] = $vm;
		$data[] = $ci;
		$data[] = $sp;
		$data[] = $su;

		$disFuns = get_cfg_var("disable_functions");

		if(empty($disFuns)){
			$data[] = '被禁用的函数，系统不支持列举(或无禁用)';
		}else{ 
			$disFuns_array =  explode(',',$disFuns);
			foreach ($disFuns_array as $val) {
				$data[] = '被禁用的函数 - '.$val;
			}	
		}

		return ['code'=>'00', 'data'=>$data];
	}

	/**
	 * 打印PHP相关组件 扩展
	 */
	public function Z(){
		$fl = 'FTP支持 - '.$this->isfun('ftp_login');
		$xl = 'XML支持 - '.$this->isfun('xml_set_object');
		$si = 'Session支持 - '.$this->isfun('session_start');
		$sk = 'Socket支持 - '.$this->isfun('socket_accept');
		$cd = 'Calendar支持 - '.$this->isfun('cal_days_in_month');
		$au = '允许URL打开文件 - '.$this->show('allow_url_fopen');

		if(function_exists('gd_info')) {
            $gd_info = gd_info();
	        $gd      = $gd_info["GD Version"];
	    }else{
			$gd = '<font color="red">×</font>';
		}
		$gd = 'GD库支持 - '.$gd;

		$zp = '压缩文件支持(Zlib) - '.$this->isfun('gzclose');
		$ic = 'IMAP电子邮件系统函数库 - '.$this->isfun('imap_close');
		$jd = '历法运算函数库 - '.$this->isfun('JDToGregorian');
		$pm = '正则表达式函数库 - '.$this->isfun('preg_match');
		$wd = 'WDDX支持 - '.$this->isfun('wddx_add_vars');
		$ic = 'Iconv编码转换 - '.$this->isfun('iconv');
		$me = 'mbstring - '.$this->isfun('mb_eregi');
		$bc = '高精度数学运算 - '.$this->isfun('bcadd');
		$lc = 'LDAP目录协议 - '.$this->isfun('ldap_close');
		$mc = 'MCrypt加密处理 - '.$this->isfun('mcrypt_cbc');
		$mo = '哈稀计算 - '.$this->isfun('mhash_count');

		$data[] = $fl;
		$data[] = $xl;
		$data[] = $si;
		$data[] = $sk;
		$data[] = $cd;
		$data[] = $au;
		$data[] = $gd;
		$data[] = $zp;
		$data[] = $ic;
		$data[] = $jd;
		$data[] = $pm;
		$data[] = $wd;
		$data[] = $ic;
		$data[] = $me;
		$data[] = $bc;
		$data[] = $lc;
		$data[] = $mc;
		$data[] = $mo;

		return ['code'=>'00', 'data'=>$data];
	}

	/**
	 * 打印数据库扩展
	 */
	public function M(){
		$s = '';
		$c = '';
		if(function_exists('mysql_get_server_info')) {
        	$s = @mysql_get_server_info();
			$s = '&nbsp; mysql_server 版本：'.$s;
			$c = '&nbsp; mysql_client 版本：'.@mysql_get_client_info();
		}
    
		$my_1 = 'MySQL 数据库 - '.$this->isfun('mysql_close').$s;
		$my_2 = 'MySQL 数据库 - '.$this->isfun('mysql_close').$c;
		$oc = 'ODBC 数据库 - '.$this->isfun('odbc_close');
		$or = 'Oracle 数据库 - '.$this->isfun('ora_close');
		$ss = 'SQL Server 数据库 - '.$this->isfun('mssql_close');
		$db = 'dBASE 数据库 - '.$this->isfun('dbase_close');
		$ms = 'mSQL 数据库 - '.$this->isfun('msql_close');

		if(extension_loaded('sqlite3')) {
			$sqliteVer = SQLite3::version();
			$si  = '<font color=green>√</font> ';
			$si .= "SQLite3　Ver ";
			$si .= $sqliteVer['versionString'];
		}else {
			$si  = $this->isfun("sqlite_close");
			if($this->isfun("sqlite_close") == '<font color="green">√</font>') {
				$si .= "&nbsp; 版本： ".@sqlite_libversion();
			}
		}

		$si = 'SQLite 数据库 - '.$si;
		$hw = 'Hyperwave 数据库 - '.$this->isfun('hw_close');
		$ps = 'Postgre SQL 数据库 - '.$this->isfun('pg_close');
		$ic = 'Informix 数据库 - '.$this->isfun('ifx_close');
		$da = 'DBA 数据库 - '.$this->isfun('dba_close');
		$dm = 'DBM 数据库 - '.$this->isfun('dbmclose');
		$ff = 'FilePro 数据库 - '.$this->isfun('filepro_fieldcount');
		$sc = 'SyBase 数据库 - '.$this->isfun('sybase_close');


		$data[] = $my_1;
		$data[] = $my_2;
		$data[] = $oc;
		$data[] = $or;
		$data[] = $ss;
		$data[] = $db;
		$data[] = $ms;
		$data[] = $si;
		$data[] = $hw;
		$data[] = $ps;
		$data[] = $ic;
		$data[] = $da;
		$data[] = $dm;
		$data[] = $ff;
		$data[] = $sc;

		$data[] = '-------------------数据库链接信息如下-------------------';

		$data[] = '类型 - '.$this->config_data['DB_TYPE'];
		$data[] = '地址 - '.$this->config_data['DB_HOST'];
		$data[] = '库名 - '.$this->config_data['DB_NAME'];
		$data[] = '账号 - '.$this->config_data['DB_USER'];
		$data[] = '密码 - '.$this->config_data['DB_PWD'];
		$data[] = '端口 - '.$this->config_data['DB_PORT'];
		$data[] = '编码 - '.$this->config_data['DB_CHARSET'];

		return ['code'=>'00', 'data'=>$data];
	}

	/**
	 * 检测PHP设置参数
	 */
	private function show($varName){
		switch($result = get_cfg_var($varName)){
			case 0:
				return '<font color="red">×</font>';
			break;
			
			case 1:
				return '<font color="green">√</font>';
			break;
			
			default:
				return $result;
			break;
		}
	}
	/**
	 * 检测函数支持
	 */ 
	private function isfun($funName = ''){
		if (!$funName || trim($funName) == '' || preg_match('~[^a-z0-9\_]+~i', $funName, $tmp)) return '错误';
		return (false !== function_exists($funName)) ? '<font color="green">√</font>' : '<font color="red">×</font>';
	}
    
}

				