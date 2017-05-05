<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 实体化类
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.1
 + Initial-Time : 2017-5-3 11:43
 + Last-time    : 2017-5-3 11:43 + 小黄牛
 + Desc         : MySql操作的命令行
 +              : my -g     打开Mysql连接
 +              : my -x sql 执行原生SQL
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
}

				