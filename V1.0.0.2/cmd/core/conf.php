<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 实体化类
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.2
 + Initial-Time : 2017-5-2 18:43
 + Last-time    : 2017-5-8 17:35 + 小黄牛
 + Desc         : 修改配置文件的命令行
 +              : conf add key val   添加新项到命令行工具配置文件中
 +              : conf upd key val   修改命令行工具中的配置项
 +              : conf del key       删除命令行工具中的配置项
 +              : conf sel key       单项查询，命令行工具中的配置项
 +              : conf -l            全部列举，命令行工具中的配置项
 +----------------------------------------------------------------------
*/

# 引入命令行基类
require_once('CmdInterface.php');

class conf implements CmdInterface{
    private $command_data; // 命令行参数 数组
	private $config_data;  // 命令行 配置参数
	private $config_path;  // 命令行 配置路径
	private $html_top;     // 命令行 配置头文件
	private $html_bottom;  // 命令行 配置尾文件

    public function  __construct($txt){
        $this->command_data = $txt;
		$this->config_path  = 'config/config.php';
		$this->config_data  = require_once($this->config_path);
		$this->html_top     = '<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 配置参数
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.2
 + Desc         : 命令行相关配置参数
 +----------------------------------------------------------------------
*/
return [
';
        $this->html_bottom  = '
];';
    }

    public function Go(){
		# 第四个参数，不管是不是中文，先过滤一遍
		if(!empty($this->command_data[3])){
			$this->command_data[3] = iconv('utf-8', 'gbk', $this->command_data[3]);
		}
		# 分支
        switch ($this->command_data[1]){
			case 'add' : 
				$res = $this->Add();
			break;  
			case 'upd' : 
				$res = $this->Upd();
			break;
			case 'del' : 
				$res = $this->Del();
			break;
			case 'sel' : 
				$res = $this->Sel();
			break;
			case '-l'  : 
				$res = $this->L();
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
	 * 添加新配置项
	*/
	private function Add(){
		# 过滤操作权限
		$vif = $this->userVif();
		if( $vif != false ){ return $vif;}

		if (empty($this->command_data[2]) || empty($this->command_data[3])) { return ['code'=>'01', 'data'=>'参数格式不正确']; }
		if (array_key_exists($this->command_data[2], $this->config_data))   { return ['code'=>'01', 'data'=>'键名已存在，请修改命令行']; } 
		$key = $this->command_data[2];
		$this->config_data[$key] = $this->command_data[3];
		/*
		 * 内容组装
		*/
		$html = $this->html_top; 
		foreach ($this->config_data as $key=>$val){
			$html .= "    '{$key}' => " . "'{$val}',\r\n";
		}
		$html .= $this->html_bottom;

		$res = file_put_contents($this->config_path, $html);
		if($res){return ['code'=>'00', 'data'=>'新增配置成功'];}
		return ['code'=>'01', 'data'=>'新增配置失败，请检查文件是否有错'];
	}

	/**
	 * 修改配置项
	*/
	private function Upd(){
		# 过滤操作权限
		$vif = $this->userVif();
		if( $vif != false ){ return $vif;}

		if (empty($this->command_data[2]) || empty($this->command_data[3])) { return ['code'=>'01', 'data'=>'参数格式不正确']; }
		if (!array_key_exists($this->command_data[2], $this->config_data))  { return ['code'=>'01', 'data'=>'键名不存在，请修改命令行']; } 
		$key = $this->command_data[2];
		$this->config_data[$key] = $this->command_data[3];
		/*
		 * 内容组装
		*/
		$html = $this->html_top; 
		foreach ($this->config_data as $key=>$val){
			$html .= "    '{$key}' => " . "'{$val}',\r\n";
		}
		$html .= $this->html_bottom; 
		
		$res = file_put_contents($this->config_path, $html);
		if($res){return ['code'=>'00', 'data'=>'修改配置成功'];}
		return ['code'=>'01', 'data'=>'修改配置失败，请检查文件是否有错'];
	}

	/**
	 * 删除配置项
	*/
	private function Del(){
		# 过滤操作权限
		$vif = $this->userVif();
		if( $vif != false ){ return $vif;}

		if (empty($this->command_data[2])) { return ['code'=>'01', 'data'=>'参数格式不正确']; }
		if (
			$this->command_data[2] == 'DB_TYPE' || 
			$this->command_data[2] == 'DB_HOST' || 
			$this->command_data[2] == 'DB_NAME' || 
			$this->command_data[2] == 'DB_USER' || 
			$this->command_data[2] == 'DB_PWD' || 
			$this->command_data[2] == 'DB_PORT' || 
			$this->command_data[2] == 'DB_CHARSET' || 
			$this->command_data[2] == 'CD_PATH'
		) { return ['code'=>'01', 'data'=>'禁止删除系统参数']; }


		if (!array_key_exists($this->command_data[2], $this->config_data))  { return ['code'=>'01', 'data'=>'键名不存在，请修改命令行']; } 
		$key = $this->command_data[2];
		unset($this->config_data[$key]);
		/*
		 * 内容组装
		*/
		$html = $this->html_top; 
		foreach ($this->config_data as $key=>$val){
			$html .= "    '{$key}' => " . "{$val},\r\n";
		}
		$html .= $this->html_bottom; 
		
		$res = file_put_contents($this->config_path, $html);
		if($res){return ['code'=>'00', 'data'=>'删除配置成功'];}
		return ['code'=>'01', 'data'=>'删除配置失败，请检查文件是否有错'];
	}

	/**
	 * 查看单个配置项
	*/
	private function Sel(){
		# 过滤操作权限
		$vif = $this->userVif();
		if( $vif != false ){ return $vif;}

		if (empty($this->command_data[2])) { return ['code'=>'01', 'data'=>'参数格式不正确']; }
		if (!array_key_exists($this->command_data[2], $this->config_data))  { return ['code'=>'01', 'data'=>'键名不存在，请修改命令行']; } 
		$key = $this->command_data[2];
		$val = $this->config_data[$key];
		return ['code'=>'00', 'data'=> '查询结果：'. $key .' - '.$val];
	}

	/**
	 * 查看全部配置项
	*/
	private function L(){
		# 过滤操作权限
		$vif = $this->userVif();
		if( $vif != false ){ return $vif;}

		foreach ($this->config_data as $key=>$val){
			$data[] = '查询结果：'. $key .' - '.$val;
		}
		return ['code'=>'00', 'data'=> $data];
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

				