<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 实体化类
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.2
 + Initial-Time : 2017-5-3 11:43
 + Last-time    : 2017-5-8 19:29 + 小黄牛
 + Desc         : 文件操作的命令行
 +              : tk -d str/index.php[必填]                                  删除文件
 +              : tk -s str/index.php[必填]                                  检查文件是否存在
 +              : tk -u str/index.php[必填]  str/test.php[必填]               修改文件名
 +              : tk -c str/index.php[必填]  str/test.php[必填]               复制文件 
 +----------------------------------------------------------------------
*/

# 引入命令行基类
require_once('CmdInterface.php');

class tk implements CmdInterface{
    private $command_data; // 命令行参数 数组
	private $config_data;  // 命令行 配置参数
	private $config_path;  // 命令行 配置路径

    public function  __construct($txt){
        $this->command_data = $txt;
		$this->config_path  = 'config/config.php';
		$this->config_data  = require_once($this->config_path);
    }

    public function Go(){
		# 第3-4个参数，不管是不是中文，先过滤一遍
		if(!empty($this->command_data[2])){
			$this->command_data[2] = iconv('utf-8', 'gbk', $this->command_data[2]);
		}
		if(!empty($this->command_data[3])){
			$this->command_data[3] = iconv('utf-8', 'gbk', $this->command_data[3]);
		}
		# 分支
        switch ($this->command_data[1]){
			case '-d' : 
				$res = $this->D();
			break;
			case '-s' : 
				$res = $this->S();
			break;   
			case '-u' : 
				$res = $this->U();
			break; 
			case '-c' : 
				$res = $this->C();
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
	 * 删除文件
	 */
	public function D(){
		# 过滤操作权限
		$vif = $this->userVif();
		if( $vif != false ){ return $vif;}

		# 过滤根目录路径是否有设置
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}
		if (empty($this->command_data[2])) { return ['code' => '01','data' => '需要删除的文件参数不允许为空']; }
		if (substr(strrchr($this->command_data[2], '.'), 1) == ''){ return ['code' => '01','data' => '必须是带文件名的路径参数']; }

		$file = $this->config_data['CD_PATH'].$this->command_data[2];
		if (!file_exists($file)) { return ['code' => '01','data' => '文件不存在']; }
		
		$res = @unlink($file); 
		if(!$res){ return ['code' => '01','data' => '文件删除失败，请检查命令行是否规范']; }
		return ['code' => '00','data' => '文件删除成功'];
	}

	/**
	 * 检测文件是否存在
	 */
	private function S(){
		# 过滤根目录路径是否有设置
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}
		if (empty($this->command_data[2])) { return ['code' => '01','data' => '需要检查的文件参数不允许为空']; }
		if (substr(strrchr($this->command_data[2], '.'), 1) == ''){ return ['code' => '01','data' => '必须是带文件名的路径参数']; }
		$file = $this->config_data['CD_PATH'].$this->command_data[2];
		if (!file_exists($file)) { return ['code' => '01','data' => '文件不存在']; }
		return ['code' => '00','data' => '文件已存在'];
	}

	/**
	 * 修改文件名
	 */
	public function U(){
		# 过滤根目录路径是否有设置
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}
		if (empty($this->command_data[2])) { return ['code' => '01','data' => '原始文件路径不允许为空']; }
		if (empty($this->command_data[3])) { return ['code' => '01','data' => '更改路径不允许为空']; }
		if ($this->command_data[3] == $this->command_data[2]) { return ['code' => '01','data' => '更改路径不能与原始路径一致']; }
		
		$file_fu = $this->config_data['CD_PATH'].$this->command_data[2];
		$file_zi = $this->config_data['CD_PATH'].$this->command_data[3];
		if (!file_exists($file_fu)) { return ['code' => '01','data' => '原始文件不存在，请修改命令行']; }
		if (file_exists($file_zi))  { return ['code' => '01','data' => '更改文件名已存在，请修改命令行']; }

		$res = @rename($file_fu, $file_zi);

		if(!$res){ return ['code' => '01','data' => '文件修改失败，请检查命令行是否规范']; }
		return ['code' => '00','data' => '文件修改成功'];	
	}

	/**
	 * 复制文件名
	 */
	public function C(){
		# 过滤根目录路径是否有设置
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}
		if (empty($this->command_data[2])) { return ['code' => '01','data' => '原始文件路径不允许为空']; }
		if (empty($this->command_data[3])) { return ['code' => '01','data' => '目标路径不允许为空']; }
		if ($this->command_data[3] == $this->command_data[2]) { return ['code' => '01','data' => '目标路径不能与原始路径一致']; }
		
		$file_fu = $this->config_data['CD_PATH'].$this->command_data[2];
		$file_zi = $this->config_data['CD_PATH'].$this->command_data[3];
		if (!file_exists($file_fu)) { return ['code' => '01','data' => '原始文件不存在，请修改命令行']; }
		if (file_exists($file_zi))  { return ['code' => '01','data' => '目标文件名已存在，请修改命令行']; }

		$res = @copy($file_fu, $file_zi);

		if(!$res){ return ['code' => '01','data' => '文件复制失败，请检查命令行是否规范']; }
		return ['code' => '00','data' => '文件复制成功'];	
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
	
	/**
	 * 过滤登录权限
	 */
	private function userVif(){
		if($_SESSION['cmd_user'] != 'admin'){
			return ['code'=>'01', 'data'=>'只有admin账号有权利注册账号'];
		}
		return false;
	}
}

				