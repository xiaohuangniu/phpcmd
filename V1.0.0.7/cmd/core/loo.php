<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 实体化类
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.1
 + Initial-Time : 2017-8-8 11:04
 + Last-time    : 2017-8-8 13:47 + 小黄牛
 + Desc         : 各种检测扫描的命令行
 +              : loo bom [随便输入即可]      扫描bom头文件，第三个参数不为空时则自动清除BOM头
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
			default  :
				$res = [
					'code' => '01',
					'data' => '暂无该操作类型',
				];
		}
		return $res;
    }

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
