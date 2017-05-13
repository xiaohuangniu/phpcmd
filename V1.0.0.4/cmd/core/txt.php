<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 实体化类
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.1
 + Initial-Time : 2017-5-3 11:43
 + Last-time    : 2017-5-3 11:43 + 小黄牛
 + Desc         : TXT文本（不限制log等同类文件）操作的命令行
 +              : txt -l str/index.txt[必填] 16423[可选]函数                  获取txt内容
 +              : txt -t str/index.txt[必填] test[必填] abc[可选]             内容替换
 +----------------------------------------------------------------------
*/

# 引入命令行基类
require_once('CmdInterface.php');

class txt implements CmdInterface{
    private $command_data; // 命令行参数 数组
	private $config_data;  // 命令行 配置参数
	private $config_path;  // 命令行 配置路径

    public function  __construct($txt){
        $this->command_data = $txt;
		$this->config_path  = 'config/config.php';
		$this->config_data  = require_once($this->config_path);
    }

    public function Go(){
		# 第3-5个参数，不管是不是中文，先过滤一遍
		if(!empty($this->command_data[2])){
			$this->command_data[2] = iconv('utf-8', 'gbk', $this->command_data[2]);
		}
		if(!empty($this->command_data[3])){
			$this->command_data[3] = iconv('utf-8', 'gbk', $this->command_data[3]);
		}
		if(!empty($this->command_data[4])){
			$this->command_data[4] = iconv('utf-8', 'gbk', $this->command_data[4]);
		}
		
		# 分支
        switch ($this->command_data[1]){
			case '-l' : 
				$res = $this->L();
			break;
			case '-t' : 
				$res = $this->T();
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
	 * 读取txt内容
	 */
	public function L(){
		# 过滤根目录路径是否有设置
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}
		if (empty($this->command_data[2])) { return ['code' => '01','data' => '需要打开的文件参数不允许为空']; }
		if (substr(strrchr($this->command_data[2], '.'), 1) == ''){ return ['code' => '01','data' => '必须是带文件名的路径参数']; }

		$file = $this->config_data['CD_PATH'].$this->command_data[2];
		if (!file_exists($file)) { return ['code' => '01','data' => '文件不存在']; }
		
		# 检查能否打开文件
		$res = fopen($file, 'r');fclose($res);
		if(!$res){ return ['code' => '01','data' => '打开文件失败，请检查文件权限']; }

		$res = file($file);
		# 附带参数-获取文件内容长度
		if(!empty($this->command_data[3])){
			if($this->command_data[3] > 100){ return ['code' => '01','data' => '最大只允许读取前100行']; }
			$i = 1;
			foreach($res as &$line){
				$data[] = "第{$i}行 - ". htmlspecialchars(iconv('gbk', 'utf-8', $line));
				if ($i == $this->command_data[3]) { break; } 
				$i++;
			}
		}else{
			$i = 1;
			foreach($res as &$line){
				$data[] = "第{$i}行 - ". htmlspecialchars(iconv('gbk', 'utf-8', $line));
				if ($i == 100) { break; } 
				$i++;
			}
		}

		return ['code' => '00','data' => $data];
	}

	/**
	 * 替换txt内容
	 */
	public function T(){
		# 过滤根目录路径是否有设置
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}
		if (empty($this->command_data[2])) { return ['code' => '01','data' => '需要打开的文件参数不允许为空']; }
		if (substr(strrchr($this->command_data[2], '.'), 1) == ''){ return ['code' => '01','data' => '必须是带文件名的路径参数']; }
		if (empty($this->command_data[3])) { return ['code' => '01','data' => '原始字符串参数不允许为空']; }

		$file = $this->config_data['CD_PATH'].$this->command_data[2];
		if (!file_exists($file)) { return ['code' => '01','data' => '文件不存在']; }
		
		# 检查能否打开文件
		$file = fopen($file, 'r');
		if(!$file){ return ['code' => '01','data' => '打开文件失败，请检查文件权限']; }

		# 开始修改文件内容
		$target = !empty($this->command_data[4]) ? $this->command_data[4] : '';
		$str = '';
		while (!feof($file)){
			$buf  = fgets($file);
			$str .= str_replace($this->command_data[3], $target, $buf);
		}
		$res  = fopen($this->config_data['CD_PATH'].$this->command_data[2], 'w');
		$info = fwrite($res, $str);
		fclose($res);
		fclose($file);
		if($info != false){ return ['code' => '00','data' => 'TXT内容修改成功']; }
		
		return ['code' => '01','data' => 'TXT内容修改失败，请检查命令行是否规范'];	
	}

	/**
	 * 判断配置路径是否已设置
	 */
	private function Vif(){
		if (empty($this->config_data['CD_PATH'])) {
			return  ['code' => '01','data' => '请输入【conf upd CD_PATH 路径】，设置cd根目录后，再执行cd命令行操作'];
		}
		return false;
	}
}

				