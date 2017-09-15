<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 实体化类
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.4
 + Initial-Time : 2017-5-13 13:28
 + Last-time    : 2017-5-13 13:28 + 小黄牛
 + Desc         : MySql操作的命令行
 +              : back get 回滚标识[必填]
 +              : back del 回滚标识[必填]
 +              : back -l
 +----------------------------------------------------------------------
*/

# 引入命令行基类
require_once('CmdInterface.php');

class back implements CmdInterface{
    private $command_data; // 命令行参数 数组
	private $config_data;  // 命令行 配置参数
	private $config_path;  // 命令行 配置路径
	private $back_path;    // 操作回滚，保存目录
	private $PDO;          // PDO实例

    public function  __construct($txt){
        $this->command_data = $txt;
		$this->config_path  = 'config/config.php';
		$this->back_path    = 'config/mysql_back2/';
		$this->config_data  = require_once($this->config_path);
    }

    public function Go(){
		# 分支
        switch ($this->command_data[1]){
			case 'get' : 
				$res = $this->getBack();
			break;
			case 'del' : 
				$res = $this->delBack();
			break;
			case '-l' : 
				$res = $this->listBack();
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
	 * 删除回滚标记
	 */
	private function delBack(){
		if(empty($this->command_data[2])){
			return ['code' => '01','data' => '请输入回滚标识'];
		}
		
		$back = $this->back_path . $this->command_data[2].'.php';
		if (!file_exists($back)) { return ['code' => '01','data' => '回滚标识不存在']; }

		$res = @unlink($back); 
		if(!$res){ return ['code' => '01','data' => '删除失败']; }
		return ['code' => '00','data' => '删除成功'];
	}

	/**
	 * 列举回滚目录
	 */
	private function listBack(){
		$file = $this->back_path;
		$html  =  '<div style="width:10%;float:left">回滚标识</div>';
		$html .=  '<div style="width:10%;float:left">注册时间</div>';
		$data[] =$html;

		# 开始遍历目录
		$handle = opendir($file. "."); 
		while (false !== ($url = readdir($handle))) {
			if ($url != "." && $url != "..") {
				$add_time = date("Y-m-d H:i:s", filemtime($file. $url));
				$html  =  '<div style="width:10%;float:left">'. rtrim($url,'.php') .'</div>';
				$html .=  '<div style="width:10%;float:left">'. $add_time .'</div>';
				
				$data[] =$html;
			}
		}
		closedir($handle); 
		return  ['code' => '00','data' => $data];
	}

	/**
	 * 回滚处理
	 */
	private function getBack(){
		# 过滤操作权限
		$vif = $this->userVif();
		if( $vif != false ){ return $vif;}

		# 验证数据库连接
		$vif  = $this->G();
		$code = !empty($vif['code']) ? $vif['code'] : '';
		if($code != '00') { return ['code'=>'01', 'data'=>'数据库链接失败，可以使用【my -g】命令进行数据库链接测试']; }

		if (empty($this->command_data[2])) {return ['code'=>'01', 'data'=>'回滚标识不能为空'];}
		$back = $this->back_path . $this->command_data[2].'.php';
		if (!file_exists($back)) { return ['code' => '01','data' => '并无对应的回滚缓存记录']; }

		$data[] = '开始回滚释放....';
		
		$sql = require_once($back);
		$pdo = $this->PDO;
		foreach ($sql as $k=>$v){
			# 新增操作
			if($pdo->exec($v)){
				$txt = "第" .($k+1). "条记录，回滚成功：{$v}";
			}else{
				$txt = "第" .($k+1). "条记录，回滚失败：{$v}";
			}

			if($k < 20){
				$data[] = $txt;
			}else if($k == 21){
				$data[] = '......';
			}

		}

		$data[] = '回滚结束....';

		# 是否需要清除缓存
		if (!empty($this->command_data[3])) {
			$data[] = '开始删除 '.$this->command_data[2].' 回滚标记....';
			$res = @unlink($back); 
			if(!$res){ 
				$data[] = '删除失败，原因：缓存文件异常....';
			}else{
				$data[] = '删除成功....';
			}
		}

		return ['code'=>'00', 'data'=>$data];
	}



	/**
	 * 打开数据库链接
	*/
    private function G(){
		# 过滤MySql配置
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}
		$dbn = $this->config_data['DB_TYPE'].':host='.$this->config_data['DB_HOST'].';port='.$this->config_data['DB_PORT'].';dbname='.$this->config_data['DB_NAME'].';charset='.$this->config_data['DB_CHARSET'];

		try {
			$dbh = new PDO($dbn, $this->config_data['DB_USER'], $this->config_data['DB_PWD']);
			$this->PDO = $dbh;
			$this->PDO->query('set names '.$this->config_data['DB_CHARSET'].';');
			return ['code'=>'00', 'data'=>'MySql链接成功'];
		} catch (PDOException $e) {
			return ['code'=>'01', 'data'=>'MySql链接失败 - '.$e->getMessage()];
		}		
	}

	/**
	 * 检测数据库配置是否已设置
	 */
	private function Vif(){
		if (empty($this->config_data['DB_TYPE'])) {
			return  ['code' => '01','data' => '请输入【conf upd DB_TYPE mysql】，设置数据库链接类型，暂只支持MySql数据库'];
		}
		if (empty($this->config_data['DB_HOST'])) {
			return  ['code' => '01','data' => '请输入【conf upd DB_HOST 参数】，设置数据库链接地址，默认为localhost'];
		}
		if (empty($this->config_data['DB_NAME'])) {
			return  ['code' => '01','data' => '请输入【conf upd DB_NAME 参数】，选择对应的数据库'];
		}
		if (empty($this->config_data['DB_USER'])) {
			return  ['code' => '01','data' => '请输入【conf upd DB_USER 参数】，设置MySql账号'];
		}
		if (empty($this->config_data['DB_PWD'])) {
			return  ['code' => '01','data' => '请输入【conf upd DB_PWD 参数】，设置MySql密码'];
		}
		if (empty($this->config_data['DB_PORT'])) {
			return  ['code' => '01','data' => '请输入【conf upd DB_PORT 参数】，设置数据库端口，默认为3306'];
		}
		if (empty($this->config_data['DB_CHARSET'])) {
			return  ['code' => '01','data' => '请输入【conf upd DB_CHARSET 参数】，设置数据库编码，默认为utf8'];
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

				