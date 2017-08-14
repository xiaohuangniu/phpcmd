<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 数据库备份下载
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.3
 + Initial-Time : 2017-5-10 17:58
 + Last-time    : 2017-5-10 17:58 + 小黄牛
 +----------------------------------------------------------------------
*/

class DBzip {
    public  $zip_path;     // 导出的压缩包存放地址
    private $fp;           //压缩包写入权限
    private $file_count        = 0;			//目录深度
    private $datastr_len       = 0; 		//压缩包大小
    private $dirstr_len        = 0;			//目录大小
    private $dirstr            = '';		//目录信息
    public  $_log              = [];        // 操作日志

    public function __construct() {
		$this->zip_path     = 'config/mysql_zip/'.time().'.zip';
    }

    /**
	 * 向压缩包添加文件
	 * $Pack     ：需要打包文件 
	*/
	public function SaveZip($Pakc){
        $res = $this->AddFile($Pakc);
        if($res === false){
            $this->_log[] = '添加压缩包文件失败 - '.$Pakc;
        }else{
            $this->_log[] = '添加压缩包文件成功 - '.$Pakc;
        }
	}

    /**
	 * 初始化文件,建立文件目录,以及生产空压缩包，只对文件压缩时使用
	 * @return object 返回文件的写入权限
	*/
	public function SetFile() {
        $this->_log[] = '新建空压缩包成功 - '.$this->zip_path;
        # 创建压缩包，并且返回写入权限
		if ($this->fp = fopen($this->zip_path, "w")) {
            return true;
        }
        $this->_log[] = '新建空压缩包失败';
        return false;
    }

    /**
	 * 向压缩包内添加一个文件
	 * $name : 文件路径
	*/
     private function AddFile($name){
	 	//读取文件内容
	 	if(file_exists($name)){
			$fp = fopen($name,"r");
			$data = '';
			$buffer = 1024;//每次读取 1024 字节
			while(!feof($fp)){//循环读取，直至读取完整个文件
		    	$data .= fread($fp,$buffer);
		    } 
		}else{
			return false;
		}
        $dtime    = dechex($this->unix2DosTime());
        $hexdtime = '\x' . $dtime[6] . $dtime[7] . '\x' . $dtime[4] . $dtime[5] . '\x' . $dtime[2] . $dtime[3] . '\x' . $dtime[0] . $dtime[1];
        eval('$hexdtime = "' . $hexdtime . '";');
        
        $unc_len = strlen($data);
        $crc     = crc32($data);
        $zdata   = gzcompress($data);
        $c_len   = strlen($zdata);
        $zdata   = substr(substr($zdata, 0, strlen($zdata) - 4), 2);
        
        //新添文件内容格式化:
        $datastr = "\x50\x4b\x03\x04";
        $datastr .= "\x14\x00"; // ver needed to extract
        $datastr .= "\x00\x00"; // gen purpose bit flag
        $datastr .= "\x08\x00"; // compression method
        $datastr .= $hexdtime; // last mod time and date
        $datastr .= pack('V', $crc); // crc32
        $datastr .= pack('V', $c_len); // compressed filesize
        $datastr .= pack('V', $unc_len); // uncompressed filesize
        $datastr .= pack('v', strlen($name)); // length of filename
        $datastr .= pack('v', 0); // extra field length
        $datastr .= $name;
        $datastr .= $zdata;
        $datastr .= pack('V', $crc); // crc32
        $datastr .= pack('V', $c_len); // compressed filesize
        $datastr .= pack('V', $unc_len); // uncompressed filesize
        fwrite($this->fp, $datastr); //写入新的文件内容
        $my_datastr_len = strlen($datastr);
        unset($datastr);//销毁变量
        
        //新添文件目录信息
        $dirstr = "\x50\x4b\x01\x02";
        $dirstr .= "\x00\x00"; // version made by
        $dirstr .= "\x14\x00"; // version needed to extract
        $dirstr .= "\x00\x00"; // gen purpose bit flag
        $dirstr .= "\x08\x00"; // compression method
        $dirstr .= $hexdtime; // last mod time & date
        $dirstr .= pack('V', $crc); // crc32
        $dirstr .= pack('V', $c_len); // compressed filesize
        $dirstr .= pack('V', $unc_len); // uncompressed filesize
        $dirstr .= pack('v', strlen($name)); // length of filename
        $dirstr .= pack('v', 0); // extra field length
        $dirstr .= pack('v', 0); // file comment length
        $dirstr .= pack('v', 0); // disk number start
        $dirstr .= pack('v', 0); // internal file attributes
        $dirstr .= pack('V', 32); // external file attributes - 'archive' bit set
        $dirstr .= pack('V', $this->datastr_len); // relative offset of local header
        $dirstr .= $name;
        $this->dirstr .= $dirstr; //目录信息
        $this->file_count++;
        $this->dirstr_len += strlen($dirstr);
        $this->datastr_len += $my_datastr_len;
	 	unset($dirstr);//销毁变量
		return true;
    }

    /**
	 * 返回文件的修改时间格式
    */
    private function unix2DosTime($unixtime = 0){
        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);
        if ($timearray['year'] < 1980) {
            $timearray['year']    = 1980;
            $timearray['mon']     = 1;
            $timearray['mday']    = 1;
            $timearray['hours']   = 0;
            $timearray['minutes'] = 0;
            $timearray['seconds'] = 0;
        }
        return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) | ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
    }

    /**
     * 释放压缩包资源
     */
	public function createfile(){
        # 压缩包结束信息,包括文件总数,目录信息读取指针位置等信息
        $endstr = "\x50\x4b\x05\x06\x00\x00\x00\x00" . pack('v', $this->file_count) . pack('v', $this->file_count) . pack('V', $this->dirstr_len) . pack('V', $this->datastr_len) . "\x00\x00";
        fwrite($this->fp, $this->dirstr . $endstr);
        fclose($this->fp);
        $this->_log[] = '已释放压缩包资源 ....';
    }
}