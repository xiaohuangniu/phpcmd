<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 实体化类
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.1
 + Initial-Time : 2017-5-3 11:43
 + Last-time    : 2017-5-3 11:43 + 小黄牛
 + Desc         : 工具操作账号 的命令行
 +              : user reg 账号[必填] 密码[必填]                            注册账号
 +              : user del 账号[必填]                                      删除账号
 +              : user upd 账号[必填] 密码[必填]                            修改账号
 +              : user exit                                                退出             
 +              : user login|name 账号[必填] 密码[必填]                     登录
 +----------------------------------------------------------------------
*/

# 引入命令行基类
require_once('CmdInterface.php');

class user implements CmdInterface{
    private $command_data; // 命令行参数 数组
	private $config_data;  // 命令行 配置参数
	private $config_path;  // 命令行 配置路径
	private $user_data;    // 所有用户
	private $user_path;    // 用户 配置路径

    public function  __construct($txt){
        $this->command_data = $txt;
		$this->config_path  = 'config/config.php';
		$this->config_data  = require_once($this->config_path);
		$this->user_path    = 'config/user/';
    }

    public function Go(){
		# 分支
        switch ($this->command_data[1]){
			case 'reg' : 
				$res = $this->Reg();
			break;
			case 'del' : 
				$res = $this->Del();
			break;
			case 'upd' : 
				$res = $this->Upd();
			break;
			case 'login|name' : 
				$res = $this->Login();
			break;
			case 'exit' : 
				$res = $this->Ext();
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
	 * 注册账号
	 */
	public function Reg(){
		# 过滤工具包安全性
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}

		if (empty($this->command_data[2])) { return ['code' => '01','data' => '账号不能为空']; }
		if (empty($this->command_data[3])) { return ['code' => '01','data' => '密码不能为空']; }

		$file = $this->user_path . $this->command_data[2] . '.juncmd';
		if (!file_exists($file)) { return ['code' => '01','data' => '账号不存在']; }

		$pwd = md5($this->command_data[3]);
		$res = file_put_contents($file, $pwd);
		if($res){
			return ['code'=>'00', 'data'=>'添加账号成功'];
		}
		return ['code'=>'01', 'data'=>'添加账号失败，请添加QQ:1731223728，向作者反馈BUG'];
	}

	/**
	 * 删除账号
	 */
	public function Del(){
		# 过滤工具包安全性
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}

		if (empty($this->command_data[2])) { return ['code' => '01','data' => '账号不能为空']; }
		if (strtolower($this->command_data[2]) == 'admin'){ return ['code' => '01','data' => '你不能删除系统账号']; }

		$file = $this->user_path . $this->command_data[2] . '.juncmd';
		if (!file_exists($file)) { return ['code' => '01','data' => '账号不存在，请修改命令行']; }

		$res = @unlink($file); 
		if(!$res){ return ['code' => '01','data' => '删除账号失败']; }
		if($_SESSION['cmd_user'] == $this->command_data[2]){
			$_SESSION['cmd_user'] = '';
		}
		return ['code' => '00','data' => '删除账号成功'];
	}

	/**
	 * 修改账号
	 */
	public function Upd(){
		# 过滤工具包安全性
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}

		if (empty($this->command_data[2])) { return ['code' => '01','data' => '账号不能为空']; }
		if (empty($this->command_data[3])) { return ['code' => '01','data' => '新密码不能为空']; }

		$file = $this->user_path . $this->command_data[2] . '.juncmd';
		if (!file_exists($file)) { return ['code' => '01','data' => '账号不存在，请修改命令行']; }

		$res = @unlink($file); 
		if(!$res){ return ['code' => '01','data' => '修改账号失败']; }

		$pwd = md5($this->command_data[3]);
		$res = file_put_contents($file, $pwd);
		if($res){
			if($_SESSION['cmd_user'] == $this->command_data[2]){
				$_SESSION['cmd_user'] = '';
			}
			return ['code'=>'00', 'data'=>'修改账号成功'];
		}
		return ['code'=>'01', 'data'=>'修改账号失败，请添加QQ:1731223728，向作者反馈BUG'];
	}


	/**
	 * 登录命令行工具
	 */
	public function Login(){
		# 过滤工具包安全性
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}

		if (empty($this->command_data[2])) { return ['code' => '01','data' => '账号不能为空']; }
		if (empty($this->command_data[3])) { return ['code' => '01','data' => '密码不能为空']; }

		$file = $this->user_path.$this->command_data[2].'.juncmd';
		if (!file_exists($file)) { return ['code' => '01','data' => '账号不存在，请您不要做违法的事情？']; }
		$pwd = file_get_contents($file);//将整个文件内容读入到一个字符串中
		if($pwd != md5($this->command_data[3])){
			return ['code' => '01','data' => '密码不正确'];
		}
		$_SESSION['cmd_user'] = $this->command_data[2];
		return ['code' => '00','data' => '登录成功']; 
	}

	/**
	 * 退出命令行工具
	 */
	public function Ext(){
		# 过滤工具包安全性
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}

		$_SESSION['cmd_user'] = '';
		return ['code' => '00','data' => '已退出命令行工具']; 
	}

	/**
	 * 判断配置路径是否已设置
	 */
	private function Vif(){
		if (empty($this->config_data['CD_PATH'])) {
			return  ['code' => '01','data' => '原生包的默认根目录为【项目根】，您的配置有误，可能并不是官方作者的工具包，为了安全性请谨慎使用'];
		}else if ($this->config_data['CD_PATH'] != '../'){
			return  ['code' => '01','data' => '原生包的默认根目录为【项目根】，您的配置有误，可能并不是官方作者的工具包，为了安全性请谨慎使用'];
		}
		return false;
	}
}

				