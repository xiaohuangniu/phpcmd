<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 实体化类
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.9
 + Initial-Time : 2017-8-13 21:36
 + Last-time    : 2017-8-14 14:27 + 小黄牛
 + Desc         : 文件夹进入操作
 +              : cd 路径      进入目录或打开文件，输入cd /直接进入配置CD_PATH的根目录
 +              : cd -h       查看cd命令当前所在的目录
 +              : cd -ls      查看当前目录纵深下的所有文件
 +----------------------------------------------------------------------
*/

# 引入命令行基类
require_once('CmdInterface.php');

class cd implements CmdInterface{
    private $command_data; // 命令行参数 数组
	private $config_data;  // 命令行 配置参数
	private $config_path;  // 命令行 配置路径
	private $data = [];    // 返回描述

    public function  __construct($txt){
        $this->command_data = $txt;
		$this->config_path  = 'config/config.php';
		$this->config_data  = require_once($this->config_path);
    }

    public function Go(){
		# 分支
        switch ($this->command_data[1]){
			case '-h' : 
				$res = $this->H();
			break;
			case '-ls' : 
				$res = $this->Ls();
			break;
			default  :
				$res = $this->path_file();
		}
		return $res;
    }


	/**
	 * 进入目录或打开文件
	 */
	public function path_file(){
		if (empty($this->command_data[1])) { return ['code' => '01','data' => '路径不能为空']; }
		$path = $this->command_data[1];
		if($path == '/'){
			$_SESSION['cmd_path_file'] = '';
		}else{
			$path = rtrim($path, '/').'/';
		}
		
		$cmd_path_file = isset($_SESSION['cmd_path_file']) ? $_SESSION['cmd_path_file'].$path : $path;
		$url           = rtrim($this->config_data['CD_PATH'] . $cmd_path_file, '/');
		
		if (is_file($url)) {
			# 打开文件
			if(!file_exists($url)){
				return ['code' => '01','data' => $cmd_path_file. ' :文件不存在！']; 
			}
			if(!is_writable($url)){
				return ['code' => '01','data' => $cmd_path_file . ' :文件没有读写权限！']; 
			}
			$_SESSION['cmd_post_file'] = rtrim($this->config_data['CD_PATH'] . ltrim($cmd_path_file, '/'), '/');
			$content = file_get_contents($url);
			$suffix  = strtolower(substr(strrchr($url, '.'), 1));

			return ['code' => '05','msg' => $suffix,'data' => $content]; 
		} else {
			# 切换目录
			if (!file_exists($url)) {
				return ['code' => '01','data' => $cmd_path_file . ' :目录不存在！']; 
			}
			$_SESSION['cmd_path_file'] = $cmd_path_file;
			return ['code' => '04','data' => $cmd_path_file]; 
		}
	}

	/**
	 * 查看当前位置
	 */
	public function H(){
		$cmd_path_file = isset($_SESSION['cmd_path_file']) ? $_SESSION['cmd_path_file'] : '/';
		return ['code' => '06','data' => $cmd_path_file]; 
	}
	/**
	 * 查看当前目录下的所有文件
	 */
	public function Ls(){
		$cmd_path_file = isset($_SESSION['cmd_path_file']) ? $this->config_data['CD_PATH'] . ltrim($_SESSION['cmd_path_file'], '/') : $this->config_data['CD_PATH'];
		$this->checkdir($cmd_path_file); 
		return ['code'=>'00', 'data'=>$this->data];
	}

	/**
	 * 递归目录
	 * @param string $basedir 路径
	 * @return string
	 */
	private function checkdir($basedir){
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
						$this->data[] =  "地址: <span class='code-click'>". iconv('gb2312','utf-8//IGNORE',$url) . '</span>';
					} else {
						$this->checkdir($url);
					}
				}
			}
			closedir($dh);
		}
	}


}

				