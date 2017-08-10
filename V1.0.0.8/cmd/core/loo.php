<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 实体化类
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.1
 + Initial-Time : 2017-8-8 11:04
 + Last-time    : 2017-8-10 09:36 + 小黄牛
 + Desc         : 各种检测扫描的命令行
 +              : loo bom [随便输入即可]                         扫描bom头文件，第三个参数不为空时则自动清除BOM头
 +              : loo vif false或需要过滤的目录，用|符合隔开      扫描文件代码是否存在安全隐藏
 +----------------------------------------------------------------------
*/

# 引入命令行基类
require_once('CmdInterface.php');

class loo implements CmdInterface{
    private $command_data; // 命令行参数 数组
	private $config_data;  // 命令行 配置参数
	private $config_path;  // 命令行 配置路径
	private $data = [];    // 返回描述
	private $bom  = 0;     // bom头数量

	# 漏洞检测过滤
	private $vif  = [      
		# 可能会被直接SQL注入的原生变量
		'$_get', '$_post', '$_session', '$_cookie', 
		# 可能会被非法运行代码的系统方法
		'system(', 'exec(', 'passthru(', 'shell_exec(', 'popen(', 'proc_open(', 'pcntl_exec(', 
		# 可能会被非法运行函数的系统方法
		'create_function(', 'call_user_func_array(', 'call_user_func(', 'assert(', 
		# 可能会被非法覆盖变量提交的系统方法
		'parse_str(', 'mb_parse_str(', 'import_request_variables('
	]; 

	# 默认不过滤的目录
	private $on_vif = ['cmd', 'thinkphp','ThinkPHP','yii','vendor'];
	# 默认只过滤的文件后缀
	private $vif_suffix = ['php', 'html', 'htm', 'txt', 'log', 'json', 'arr', 'array', 'con', 'conf', 'config'];
	# 扫描存在漏洞的文件数：
	private $vif_num = 0;

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
			case 'bom' : 
				$res = $this->bom();
			break;
			case 'vif' : 
				$res = $this->vif();
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
	 * 扫描全站漏洞
	 */
	public function vif(){
		$this->data[] = "启动全站漏洞扫描模式...";
		$this->data[] = "系统扫描将分为三大部分：1、SQL注入，2、系统代码注入，3、PHP函数攻击，4、非法变量提交";
		$this->data[] = "系统将扫描以下PHP关键词：";
		
		foreach ($this->vif as $v) {
			$this->data[] = $v;
		} 

		$type = true;
		if(!empty($this->command_data[2]) && $this->command_data[2] == 'false'){
			$type = false;
		}

		if ($type) {
			$this->data[] = "系统默认不扫描以下目录：";
			foreach ($this->on_vif as $v) {
				$this->data[] = $v;
			} 
		}

		if(!empty($this->command_data[2]) && $this->command_data[2] != 'false'){
			$this->data[] = "您设置将要绕过以下目录：";
			$array = explode('|', $this->command_data[2]);
			foreach ($array as $v) {
				$this->on_vif[] = $v;
				$this->data[] = $v;
			} 
		}

		$this->data[] = "开始扫描...";
		$this->fileDir($this->config_data['CD_PATH'], $type);
		$this->data[] = "扫描到存在漏洞的文件数：".$this->vif_num."个";
		$this->data[] = "扫描完成...";

		return ['code'=>'00', 'data'=>$this->data];
	}

	/****************************** 以下为VIF漏洞扫描相关函数 *********************/
	/**
	 * 递归目录结构
	 * @param string $dir  地址
	 * @param bool   $type 是否开启目录过滤，或需要过滤的目录用|符合隔开
	 * @return bool false
	 */
	private function fileDir($dir, $type) {
		if (!is_dir($dir)) {
			$this->data[] = "目录：{$dir} 不存在！";
			return false;
		}
		# 开启不过滤目录
		if($type){
			$path_data = explode('/', $dir);
			if (in_array(end($path_data), $this->on_vif)) {
				return false;
			}
		}
		
		# 打开目录
		$handle = opendir($dir);
		while (($file = readdir($handle)) !== false) {
			# 排除掉当前目录和上一个目录
			if ($file == "." || $file == "..") {
				continue;
			}
			$file = $dir . '/' . $file;
			# 如果是文件就进入过滤，否则递归调用
			if (is_file($file)) {
				$this->fileScan($file);
			} elseif (is_dir($file)) {
				$this->fileDir($file, $type);
			}
		}
	}

	/**
	 * 打开文件过滤内容
	 * @param string $file 地址
	 * @return bool false
	 */
	 private function fileScan($file){
		 $file = str_replace('//','/', $file);
		 $suffix = strtolower(substr(strrchr($file, '.'), 1));
		 # 做文件后缀验证
		 if (!in_array($suffix, $this->vif_suffix)) { return false;}
		 
		 # 读取文件内容，并将空格全部删除
		 $fp  = fopen($file, "r");
		 $length = filesize($file);
		 if ($length <= 0) { return false; }

		 $res = fread($fp, $length);
		 $res = str_replace([" ","　","\t","\n","\r"], ["","","","",""], $res);
		 $res = $this->txtScan($res);

		 if (!empty($res)) {
			 $this->vif_num += 1; 
			 $this->data[]  = $file . ' <font color="red">' .$res. '</font>';
		 }
		
	 }

	 /**
	  * 字符串批量查询匹配
	  * @param string $res 文件内容
	  * @return string 过滤成功后的关键字
	  */
	  private function txtScan($res){
		  $vif = '';
		  foreach ($this->vif as $v) {
			  if(stristr($res, $v) !== false) {
				  $vif .= $v.' ';
			  }
		  }
		  return $vif;
	  }

	/****************************** 以下为BOM头相关 *****************************/

	/**
	 * 扫描BOM头文件，并做修改
	 */
	public function bom(){
		$this->data[] = "开启BOM头文件扫描模式...";
		
		if(!empty($this->command_data[2])){
			$this->checkdir($this->config_data['CD_PATH'], true);
		}else{
			$this->checkdir($this->config_data['CD_PATH']);
		}

		$this->data[] = "扫描出带BOM头文件数为：".$this->bom;
		$this->data[] = "扫描完成...";
		return ['code'=>'00', 'data'=>$this->data];
	}

	/****************************** BOM头相关函数 *****************************/

	/**
	 * 递归目录
	 * @param string $basedir 路径
	 * @param bool   $type 是否自动修复
	 * @return string
	 */
	private function checkdir($basedir, $type = false){
		if(!is_dir($basedir)){
			if(!file_exists($basedir)){
				$this->data[] = "路径：{$basedir} 不存在！";
				return false;
			}
		}

		if ($dh = opendir($basedir)) {
			while (($file = readdir($dh)) !== false) {
				if ($file != '.' && $file != '..') {
					$url = $basedir . "/" . $file;
					if (!is_dir($url)) {
						$url = str_replace('//','/', $url);
						$this->data[] =  "地址: ". iconv('gb2312','utf-8//IGNORE',$url) . ' ' . $this->checkBOM($url, $type);
					} else {
						$this->checkdir($url, $type);
					}
				}
			}
			closedir($dh);
		}
	}
	
	/**
	 * 修复与检测BOM头
	 * @param string $filename 路径
	 * @param bool   $type 是否自动修复
	 * @return string
	 */
	private function checkBOM($filename, $type = false){
		$contents  = file_get_contents($filename);
		$charset[1] = substr($contents, 0, 1);
		$charset[2] = substr($contents, 1, 1);
		$charset[3] = substr($contents, 2, 1);
		if (ord($charset[1]) == 239 && ord($charset[2]) == 187 && ord($charset[3]) == 191) {
			if ($type === true) {
				$rest = substr($contents, 3);
				$this->rewriteUpd($filename, $rest);
				$this->bom += 1;
				return '<font color="red">找到BOM头，并自动清除成功！</font>';
			} else {
				$this->bom += 1;
				return '<font color="red">有BOM头！</font>';
			}
		} else {
			return "无BOM头";
		}
	}

	/**
	 * 修改文件内容
	 * @param string $filename 路径
	 * @param string $data 内容
	*/
	public function rewriteUpd($filename, $data){
		$filenum = fopen($filename, "w");
		flock($filenum, LOCK_EX);
		fwrite($filenum, $data);
		fclose($filenum);
	}	
}
