<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 实体化类
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.2
 + Initial-Time : 2017-5-3 11:43
 + Last-time    : 2017-5-8 17:25 + 小黄牛
 + Desc         : MySql操作的命令行
 +              : my -g     打开Mysql连接
 +              : my -x sql 执行原生SQL
 +              : my -b 表名或分卷大小,必须是整数[可选] 分卷大小M,必须是整数[可选]
 +              : my -i sql文件名，不带文件夹[必填]
 +              : my -z sql文件名，不带文件夹[必填]  是否需要下载,不为空即可[可选]
 +----------------------------------------------------------------------
*/

# 引入命令行基类
require_once('CmdInterface.php');

class my implements CmdInterface{
    private $command_data; // 命令行参数 数组
	private $config_data;  // 命令行 配置参数
	private $config_path;  // 命令行 配置路径
	
	private $PDO;          // PDO实例

    public function  __construct($txt){
        $this->command_data = $txt;
		$this->config_path  = 'config/config.php';
		$this->config_data  = require_once($this->config_path);
    }

    public function Go(){
		# 分支
        switch ($this->command_data[1]){
			case '-g' : 
				$res = $this->G();
			break;
			case '-x' : 
				$res = $this->X();
			break;
			case '-b' : 
				$res = $this->B();
			break;
			case '-i' : 
				$res = $this->I();
			break;
			case '-l' : 
				$res = $this->L();
			break;
			case '-z' : 
				$res = $this->Z();
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
	 * 打开数据库链接
	 */
    public function G(){
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
	 * 数据库操作类型分流
	 */
	public function X(){
		# 过滤MySql配置
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}
		if (empty($this->command_data[2])) { return ['code'=>'01', 'data'=>'SQL语句不能为空']; }

		# 验证数据库连接
		$vif  = $this->G();
		$code = !empty($vif['code']) ? $vif['code'] : '';
		if($code != '00') { return ['code'=>'01', 'data'=>'数据库链接失败，可以使用【my -g】命令进行数据库链接测试']; }

		$sql = $this->command_data[2];

		# select操作
		$select = stripos($sql, 'select');
		if( $select=== 0 ){
			return $this->S();
		}
		# 新增操作
		$insert = stripos($sql, 'insert'); 
		if( $insert === 0){
			return $this->A();
		}
		# 修改操作
		$update = stripos($sql, 'update'); 
		if( $update === 0 ){
			return $this->U();
		}
		# 删除操作
		$delete = stripos($sql, 'delete'); 
		if( $delete === 0 ){
			return $this->D();
		}

		return ['code'=>'01', 'data'=>'命令行暂只开放增删改查，四类常用的SQL语句'];
	}

	/**
	 * 新增
	 */
	public function A(){
		$data[] = '执行MySql新增操作：'.$this->command_data[2];

		$pdo = $this->PDO;
		if($pdo->exec($this->command_data[2])){
			$data[] = '执行成功';
			$id = $pdo->lastInsertId();
			if($id){
				$data[] = "返回主键值为：{$id}";
			}
			
			return ['code'=>'00', 'data'=>$data];
		}else{
			$data[] = '执行失败';
			return ['code'=>'01', 'data'=>$data];
		}
	}

	/**
	 * 删除
	 */
	public function D(){
		$data[] = '执行MySql删除操作：'.$this->command_data[2];

		$pdo = $this->PDO;
		if($pdo->exec($this->command_data[2])){
			$data[] = '执行成功';
			return ['code'=>'00', 'data'=>$data];
		}else{
			$data[] = '执行失败';
			return ['code'=>'01', 'data'=>$data];
		}
	}

	/**
	 * 修改
	 */
	public function U(){
		$data[] = '执行MySql修改操作：'.$this->command_data[2];

		$pdo = $this->PDO;
		if($pdo->exec($this->command_data[2])){
			$data[] = '执行成功';
			return ['code'=>'00', 'data'=>$data];
		}else{
			$data[] = '执行失败';
			return ['code'=>'01', 'data'=>$data];
		}
	}

	/**
	 * 查询
	 */
	public function S(){
		$data[] = '执行MySql查询操作：'.$this->command_data[2];

		$pdo = $this->PDO;
		$res = $pdo->query($this->command_data[2]);
		if(!$res){
			$data[] = '执行失败';
			return ['code'=>'01', 'data'=>$data];
		}

		$res->setFetchMode(PDO::FETCH_ASSOC); //列名索引方式
		$row = $res->fetchAll();
		$num = count($row);
		if($num > 0){
			$data[] = '执行成功';
			$data[] = "影响行数：{$num}";
			$array = $this->my_sel($row, $data);
			return ['code'=>'00', 'data'=>$array];
		}

		$data[] = '执行成功';
		$data[] = '影响行数：0';
		return ['code'=>'00', 'data'=>$data];
	}

	/**
	 * 解析查询内容组成Table
	 * @param array  : $array  查询结果集
	 * @param array  : $data   返回文本
	 */
	private function my_sel($array, $data){
		# 获得TR头
		$head = array_keys($array[0]);
		$length = 80 / count($head);
		$html = '';
		foreach ($head as $val){
			$html .= "<div style='width:{$length}%;float:left'>&nbsp;{$val}</div>"; 
		}
		$data [] = $html;

		# 获得TR内容
		foreach ($array as $k){
			$html = '';
			foreach ($head as $v){
				$html .= "<div style='width:{$length}%;float:left'>&nbsp;".$k[$v]."</div>";
			}
			$data [] = $html;
		}
		return $data;
	}	

	/**
	 * 数据库备份
	 */
	private function B(){
		# 过滤MySql配置
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}
		require_once('function/DBback.class.php');
		
		# 整库备份
		if (empty($this->command_data[2])) {
			$obj = new DBback($this->config_data);
			$obj->backup();
			return ['code'=>'00', 'data'=>$obj->_showMsg];
		}else{
		# 单表或整库指定分卷大小
			$str = $this->command_data[2];
			# 依旧是整库，但指定了分卷大小
			if(is_numeric($str)){
				$obj = new DBback($this->config_data);
				$obj->backup('', $str);
				return ['code'=>'00', 'data'=>$obj->_showMsg];
			}else{
				$size = !empty($this->command_data[3]) ? $this->command_data[3] : 2; // 默认2M
				$obj = new DBback($this->config_data);
				$obj->backup($str, $size);
				return ['code'=>'00', 'data'=>$obj->_showMsg];
			}
		}
	}

	/**
	 * 数据库备份恢复
	 */
	private function I(){
		# 过滤操作权限
		$vif = $this->userVif();
		if( $vif != false ){ return $vif;}

		# 过滤MySql配置
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}
		
		# 整库备份
		if (empty($this->command_data[2])) {
			return ['code'=>'01', 'data'=>'还原文件不能为空'];
		}

		require_once('function/DBimport.class.php');

		$url = $this->command_data[2];
		$obj = new DBimport($this->config_data);
		$obj->restore($url);
		return ['code'=>'00', 'data'=>$obj->_showMsg];	
	}

	/**
	 * 备份下载
	 */
	private function Z(){
		if (empty($this->command_data[2])) {return ['code'=>'01', 'data'=>'下载备份的文件不能为空'];}
		$url = 'config/mysql_back/'. $this->command_data[2];
		if (!file_exists( $url )) { return ['code' => '01','data' => '下载备份的文件不存在']; }
		require_once('function/DBzip.class.php');
		$obj = new DBzip();
		$obj->SetFile();

		# 检测是否包含分卷，将类似2017_all_v1.sql从_v分开,有则说明有分卷
        $volume = explode ( "_v", $url );
        $volume_path = $volume [0];
        
        // 存在分卷，则获取当前是第几分卷，循环执行余下分卷
        $volume_id = explode ( ".sq", $volume[1] );
        // 当前分卷为$volume_id
        $volume_id = intval ( $volume_id[0] );

        while ( $volume_id ) {
            $tmpfile = $volume_path . "_v" . $volume_id . ".sql";
            # 存在其他分卷，继续加入压缩包
            if (file_exists ( $tmpfile )) {
                $obj->SaveZip($tmpfile);
            }else{
				$obj->createfile();
				if(empty($this->command_data[3])){
					return ['code'=>'00', 'data'=> $obj->_log];
				}
				$obj->_log[] = '已输出下载地址，请注意浏览器是否阻止弹窗输出 ....';
				return ['code'=>'00', 'data'=> $obj->_log, 'dow'=> 'cmd/'.$obj->zip_path];
			}
            $volume_id ++;
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

				