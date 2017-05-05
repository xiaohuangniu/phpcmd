<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 实体化类
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.1
 + Initial-Time : 2017-5-3 11:43
 + Last-time    : 2017-5-3 11:43 + 小黄牛
 + Desc         : 目录操作的命令行
 +              : mk -l str/[可选]                                   打印指定目录下的全部文件，包括目录名，路径相对于命令行配置目录下的根目录   只有文件名
 +              : mk -ll str/[可选]                                  打印指定目录下的全部文件，包括目录名，路径相对于命令行配置目录下的根目录   详细信息
 +              : mk -a str/[必填] 目录名[可选]||0755[必填] 0755[必填]  新增目录 当第4个参数不存在时，在命令行配置目录下的根目录中创建， 否则根据第三个参数下创建
 +              : mk -s str/[必填] -y[可选] 0755[必填]                检查目录是否存在，-y时循环创建目录
 +              : mk -d str/[必填]                                   删除目录
 +              : mk -u str/[必填]  str/[必填]                       修改目录名
 +----------------------------------------------------------------------
*/

# 引入命令行基类
require_once('CmdInterface.php');

class mk implements CmdInterface{
    private $command_data; // 命令行参数 数组
	private $config_data;  // 命令行 配置参数
	private $config_path;  // 命令行 配置路径
	private $_log;         // 删除目录的日志

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
			case '-l' : 
				$res = $this->L();
			break;
			case '-ll' : 
				$res = $this->LL();
			break;   
			case '-a' : 
				$res = $this->A();
			break;
			case '-s' : 
				$res = $this->S();
			break;
			case '-d' : 
				$res = $this->D();
			break;      
			case '-u' : 
				$res = $this->U();
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
	 * 列举指定路径下的目录和文件 - 只有名称
	 */
	public function L(){
		# 过滤根目录路径是否有设置
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}
		$file = !empty($this->command_data[2]) ? $this->config_data['CD_PATH'].$this->command_data[2] : $this->config_data['CD_PATH'];
		# 过滤目录参数合法性
		$vif = $this->Catalog($file);
		if( $vif != false ){ return $vif;}
		# 检测目录参数是否真实存在
		$vif = $this->Catalog_curl($file);
		if( $vif != false ){ return $vif;}
		
		# 开始遍历目录
		$handle = opendir($file. "."); 
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				$data[] = $file;
			}
		}
		closedir($handle); 
		return  ['code' => '00','data' => $data];
	}

	/**
	 * 列举指定路径下的目录和文件 - 详细参数
	 */
	public function LL(){
		# 过滤根目录路径是否有设置
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}
		$file = !empty($this->command_data[2]) ? $this->config_data['CD_PATH'].$this->command_data[2] : $this->config_data['CD_PATH'];
		# 过滤目录参数合法性
		$vif = $this->Catalog($file);
		if( $vif != false ){ return $vif;}
		# 检测目录参数是否真实存在
		$vif = $this->Catalog_curl($file);
		if( $vif != false ){ return $vif;}
		

		$html  =  '<div style="width:10%;float:left">名称</div>';
		$html .=  '<div style="width:10%;float:left">大小</div>';
		$html .=  '<div style="width:10%;float:left">创建时间</div>';
		$html .=  '<div style="width:10%;float:left">修改时间</div>';
		$html .=  '<div style="width:10%;float:left">权限</div>';
		$data[] =$html;

		# 开始遍历目录
		$handle = opendir($file. "."); 
		while (false !== ($url = readdir($handle))) {
			if ($url != "." && $url != "..") {
				$size  = $this->Size(filesize($file.$url));
				$add_time = date("Y-m-d H:i:s", filectime($file. $url));
				$upd_time = date("Y-m-d H:i:s", filemtime($file. $url));
				$html  =  '<div style="width:10%;float:left">'. $url .'</div>';
				$html .=  '<div style="width:10%;float:left">'. $size .'</div>';
				$html .=  '<div style="width:10%;float:left">'. $add_time .'</div>';
				$html .=  '<div style="width:10%;float:left">'. $upd_time .'</div>';
				$html .=  '<div style="width:10%;float:left">'. $this->Root($file.$url) .'</div>';
				
				$data[] =$html;
			}
		}
		closedir($handle); 
		return  ['code' => '00','data' => $data];
	}

	/**
	 * 创建新目录
	 */
	public function A(){
		# 过滤根目录路径是否有设置
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}
		if (empty($this->command_data[2])) { return ['code' => '01','data' => '需要创建的目录参数不允许为空']; }
		$length = count($this->command_data);
		if ($length == 3) { return ['code' => '01','data' => '目录权限参数不允许为空, 例如:0777']; }

		# 模式分流
		if( $length == 4 ){
			$file = $this->config_data['CD_PATH'].$this->command_data[2];
			$root = $this->command_data[3];
		}else{
			$file = $this->config_data['CD_PATH'].$this->command_data[2].$this->command_data[3];
			$root = $this->command_data[4];
		}

		if (is_dir($file)) { return ['code' => '01','data' => '目录已存在，请修改命令行']; }
		
		$res = mkdir($file, $root, true);
		if(!$res){ return ['code' => '01','data' => '目录创建失败，请检查权限参数，例如：0777']; }
		return ['code' => '00','data' => '目录创建成功'];
	}

	/**
	 * 检查目录是否存在
	 */
	public function S(){
		# 过滤根目录路径是否有设置
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}
		if (empty($this->command_data[2])) { return ['code' => '01','data' => '需要检查的目录参数不允许为空']; }
		$length = count($this->command_data);

		$file = $this->config_data['CD_PATH'].$this->command_data[2];
		# 模式分流
		if( $length == 3 ){// 只检查
			if (is_dir($file)) { return ['code' => '00','data' => '目录已存在']; }
			return ['code' => '00','data' => '目录不存在'];
		}else{
			if ($length == 4) { return ['code' => '01','data' => '创建目录必须要连带目录权限参数']; }
			if ($this->command_data[3] != '-y'){ return ['code' => '01','data' => '暂无该后续操作类型']; }

			if (is_dir($file)) { return ['code' => '00','data' => '目录已存在']; }
			$data[] = '目录不存在';

			$res = mkdir($file, $this->command_data[4], true);
			if(!$res){ 
				$data[] = '目录创建失败，请检查权限参数，例如：0777'; 
			}else{
				$data[] = '目录创建成功';
			}
			
			return ['code' => '00','data' => $data];	
		}

	}

	/**
	 * 删除目录
	 */
	public function D(){
		# 过滤根目录路径是否有设置
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}
		if (empty($this->command_data[2])) { return ['code' => '01','data' => '需要删除的目录参数不允许为空']; }
		$file = $this->config_data['CD_PATH'].$this->command_data[2];
		if (!is_dir($file)) { return ['code' => '01','data' => '目录不存在，请修改命令行']; }
		$this->my_del($file);
		return ['code' => '00','data' => $this->_log];	
	}

	/**
	 * 修改目录名
	 */
	public function U(){
		# 过滤根目录路径是否有设置
		$vif = $this->Vif();
		if( $vif != false ){ return $vif;}
		if (empty($this->command_data[2])) { return ['code' => '01','data' => '原始目录路径不允许为空']; }
		if (empty($this->command_data[3])) { return ['code' => '01','data' => '更改路径不允许为空']; }
		if ($this->command_data[3] == $this->command_data[2]) { return ['code' => '01','data' => '更改路径不能与原始路径一致']; }
		
		$file_fu = $this->config_data['CD_PATH'].$this->command_data[2];
		$file_zi = $this->config_data['CD_PATH'].$this->command_data[3];
		if (!is_dir($file_fu)) { return ['code' => '01','data' => '原始目录不存在，请修改命令行']; }
		if (is_dir($file_zi))  { return ['code' => '01','data' => '更改目录已存在，请修改命令行']; }

		$res = @rename($file_fu, $file_zi);

		if(!$res){ return ['code' => '01','data' => '目录修改失败，请检查命令行是否规范']; }
		return ['code' => '00','data' => '目录修改成功'];	
	}

	/**
	 * 删除目录包括里面的文件
	 * @param string  : $path   完整路径
	 */
	private function my_del($path){
		if(is_dir($path)){
				$file_list = scandir($path);
				foreach ($file_list as $file){
					if ($file != '.' && $file != '..'){
						$this->my_del($path.$file);
					}
				}
				$info = @rmdir($path);  // 这种方法不用判断文件夹是否为空,  因为不管开始时文件夹是否为空,到达这里的时候,都是空的  
				if($info){
					$this->_log[] = $path.' 删除成功';
				}else{
					$this->_log[] = $path.' <a style="color:red">删除失败<a>';
				}
				   
		}else{
			$info = @unlink($path);    // 这两个地方最好还是要用@屏蔽一下warning错误,看着闹心
			if($info){
				$this->_log[] = $path.' 删除成功';
			}else{
				$this->_log[] = $path.' <a style="color:red">删除失败<a>';
			}
		}
	
	}

	/**
	 * 获取目录或文件权限
	 */
	private function Root($file){
		$perms = fileperms($file);
		if (($perms & 0xC000) == 0xC000) {
			// Socket
			$info = 's';
		} elseif (($perms & 0xA000) == 0xA000) {
			// Symbolic Link
			$info = 'l';
		} elseif (($perms & 0x8000) == 0x8000) {
			// Regular
			$info = '-';
		} elseif (($perms & 0x6000) == 0x6000) {
			// Block special
			$info = 'b';
		} elseif (($perms & 0x4000) == 0x4000) {
			// Directory
			$info = 'd';
		} elseif (($perms & 0x2000) == 0x2000) {
			// Character special
			$info = 'c';
		} elseif (($perms & 0x1000) == 0x1000) {
			// FIFO pipe
			$info = 'p';
		} else {
			// Unknown
			$info = 'u';
		}

		// Owner
		$info .= (($perms & 0x0100) ? 'r' : '-');
		$info .= (($perms & 0x0080) ? 'w' : '-');
		$info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));

		// Group
		$info .= (($perms & 0x0020) ? 'r' : '-');
		$info .= (($perms & 0x0010) ? 'w' : '-');
		$info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));

		// World
		$info .= (($perms & 0x0004) ? 'r' : '-');
		$info .= (($perms & 0x0002) ? 'w' : '-');
		$info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));

		return $info;
	}
	
	/**
	 * 字节大小转换
	 */
	private function Size($size){
        $kb = 1024;         // Kilobyte
        $mb = 1024 * $kb;   // Megabyte
        $gb = 1024 * $mb;   // Gigabyte
        $tb = 1024 * $gb;   // Terabyte
       
        if($size < $kb){return $size." B";}
        if($size < $mb){return round($size/$kb,2)." KB";}
        if($size < $gb){return round($size/$mb,2)." MB";}
        if($size < $tb){return round($size/$gb,2)." GB";}
        return round($size/$tb,2)." TB";
    }

	/**
	 * 检查目录链接
	 */
	private function Catalog_curl($file){
		if (!is_dir($file)) {
			return  ['code' => '01','data' => '目录路径参数不正确'];
		}
		return false;
	}

	/**
	 * 检查目录路径合法性
	 */
	private function Catalog($file){
		$path = parse_url($file); 
		$str  = explode('.',$path['path']); 
		$res  = $str[1];
		if (!empty($res)) {
			return  ['code' => '01','data' => '这不是一个目录路径'];
		}
		return false;
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

				