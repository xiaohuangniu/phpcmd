<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 数据库导出
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.3
 + Initial-Time : 2017-5-10 11:08
 + Last-time    : 2017-5-10 11:08 + 小黄牛
 +----------------------------------------------------------------------
*/

class DBback {
    private $DB;                  // PDO实例
    private $SQL_PATH;            // 数据库备份文件夹
    private $ds         = "\r\n"; // 换行符
    public  $sqlEnd     = ';';    // 每条sql语句的结尾符
    public  $_showMsg   = [];     // 操作日志
    private $config_data = [];    // CMD配置

    /**
     * 初始化参数
     * @param array : $config CMD的配置
    */
    public function __construct($config='') {
        $this->config_data = $config;
        $this->SQL_PATH    = 'config/mysql_back/';
        $dbn = $this->config_data['DB_TYPE'].':host='.$this->config_data['DB_HOST'].';port='.$this->config_data['DB_PORT'].';dbname='.$this->config_data['DB_NAME'].';charset='.$this->config_data['DB_CHARSET'];
        $dbh = new PDO($dbn, $this->config_data['DB_USER'], $this->config_data['DB_PWD']);
        $this->DB = $dbh;
        $this->DB->query('set names '.$this->config_data['DB_CHARSET'].';');
    }

    /******************************************************************** 数据库备份 start ****************************************************************************/

    /**
     * 数据库备份
     * 参数：分卷大小(可选,默认2000，即2M)
     *
     * @param string : $tablename 单表备份(可选)
     * @param int    : $size      分卷大小(可选,默认2，即2M)
    */
    public function backup($tablename = '', $size='') {
        $size = !empty($size) ? $size : 2;
        $sql  = '';

        // 单表备份
        if (!empty ( $tablename )) {
            # 检测表存不存在

            # 插入dump信息
            $this->_retrieve();
            $this->_showMsg[] = "正在备份表 - {$tablename} ....";
            # 插入表结构信息
            $sql = $this->_insert_table_structure ( $tablename );
            # 插入数据
            $pdo = $this->DB;
            $res = $pdo->query( "select * from " . $tablename );
            $res->setFetchMode(PDO::FETCH_ASSOC); //列名索引方式
            $data = $res->fetchAll();
            # 文件名前面部分
            $filename = date ( 'YmdHis' ) . "_" . $tablename;
            # 第几分卷
            $p = 1;
            
            # 循环每条记录
            foreach ($data as $record) {
                # 单条记录
                $sql .= $this->_insert_record ( $tablename, $record );
                # 如果大于分卷大小，则写入文件
                if (strlen ( $sql ) >= $size * 1024) {
                    $file = $filename . "_v" . $p . ".sql";
                    if ($this->_write_file ( $sql, $file)) {
                        $this->_showMsg[] = "表-<b>" . $tablename . "</b>-卷-<b>" . $p . "</b>-数据备份完成,备份文件 " . $this->SQL_PATH . $file;
                    } else {
                        $this->_showMsg[] = "备份表 -<b>" . $tablename . "</b>- 失败";
                        return false;
                    }
                    # 下一个分卷
                    $p ++;
                    # 重置$sql变量为空，重新计算该变量大小
                    $sql = "";
                }
                
            }
            # 及时清除数据
            unset($data,$record);
            # sql大小不够分卷大小
            if ($sql != "") {
                $filename .= "_v" . $p . ".sql";
                if ($this->_write_file ( $sql, $filename)) {
                    $this->_showMsg[] =  "表-<b>" . $tablename . "</b>-卷-<b>" . $p . "</b>-数据备份完成,备份文件 " . $this->SQL_PATH . $filename;
                } else {
                    $this->_showMsg[] = "备份卷-<b>" . $p . "</b>-失败";
                    return false;
                }
            }
            $this->_showMsg[] = "恭喜您! 备份成功";
        
        // 整库备份
        }else{
            # 插入dump信息
            $this->_retrieve();
            $this->_showMsg[] = "正在备份 ....";
            # 备份全部表
            # 插入数据
            $pdo = $this->DB;
            $res = $pdo->query("SHOW TABLES");
            $res->setFetchMode(PDO::FETCH_NUM); //列名索引方式
            $tables = $res->fetchALL(); 
            if ($tables) {
                $this->_showMsg[] = "读取数据库结构成功！";
            } else {
                $this->_showMsg[] = "读取数据库结构失败！";
                return false;
            }
            // 文件名前面部分
            $filename = date ( 'YmdHis' ) . "_all";
            # 第几分卷
            $p = 1;
            $sql = '';

            foreach ($tables as $table){
                # 获取表名
                $tablename = $table[0];
                # 插入表结构信息
                $sql .= $this->_insert_table_structure ( $tablename );

                # 插入数据
                $pdo = $this->DB;
                $res = $pdo->query( "select * from " . $tablename );
                $res->setFetchMode(PDO::FETCH_ASSOC); //列名索引方式
                $data = $res->fetchAll();

                # 循环每条记录
                foreach ($data as $record) {
                    # 单条记录
                    $sql .= $this->_insert_record ( $tablename, $record );
                    # 如果大于分卷大小，则写入文件
                    if (strlen ( $sql ) >= $size * 1024) {
                        $file = $filename . "_v" . $p . ".sql";
                        if ($this->_write_file ( $sql, $file)) {
                            $this->_showMsg[] = "表-<b>" . $tablename . "</b>-卷-<b>" . $p . "</b>-数据备份完成,备份文件 " . $this->SQL_PATH . $file;
                        } else {
                            $this->_showMsg[] = "备份表 -<b>" . $tablename . "</b>- 失败";
                            return false;
                        }
                        # 下一个分卷
                        $p ++;
                        # 重置$sql变量为空，重新计算该变量大小
                        $sql = "";
                    }
                    
                }
            }

            # 及时清除数据
            unset($data,$record);
            # sql大小不够分卷大小
            if ($sql != "") {
                $filename .= "_v" . $p . ".sql";
                if ($this->_write_file ( $sql, $filename)) {
                    $this->_showMsg[] =  "表-<b>" . $tablename . "</b>-卷-<b>" . $p . "</b>-数据备份完成,备份文件 " . $this->SQL_PATH . $filename;
                } else {
                    $this->_showMsg[] = "备份卷-<b>" . $p . "</b>-失败";
                    return false;
                }
            }
            $this->_showMsg[] = "恭喜您! 备份成功";
        }
    }

     /**
     * 写入文件
     *
     * @param string $sql
     * @param string $filename
     * @return boolean
     */
    private function _write_file($sql, $filename) {
        $dir = $this->SQL_PATH;
        $re = true;
        if (! $fp = fopen ( $dir . $filename, "w+" )) {
            $re = false;
            $this->_showMsg[] = "打开sql文件失败！";
        }
        if (! fwrite ( $fp, $sql )) {
            $re = false;
            $this->_showMsg[] = "写入sql文件失败，请文件是否可写";
        }
        if (! fclose ( $fp )) {
            $re = false;
            $this->_showMsg[] = "关闭sql文件失败！";
        }
        return $re;
    }


    /**
     * 插入单条记录
     *
     * @param string $table
     * @param array $record
     * @return string
     */
    private function _insert_record($table, $record) {
		# 获得TR头
		$head = array_keys($record);
		# 获得TR内容

        $insert = "INSERT INTO `" . $table . "` VALUES(";
        foreach ($head as $v){
            $insert .= '"'.$record[$v].'",';
        }
        $insert = rtrim($insert, ',') .');'.$this->ds;
		return $insert;
	}	


    /**
     * 插入表结构
     *
     * @param unknown_type $table
     * @return string
     */
    private function _insert_table_structure($table) {
        $this->_showMsg[] = "表结构读取中 ....";

        // 获取详细表信息
        $pdo = $this->DB;
        $res = $pdo->query( 'SHOW CREATE TABLE `' . $table . '`' );
		$res->setFetchMode(PDO::FETCH_NUM);
		$row = $res->fetch();
        $this->_showMsg[] = "表结构已读取成功 <b>{$table}</b> ....";

        # 可以加上一句删除表；
        # "DROP TABLE IF EXISTS `" . $table . '`' . $this->sqlEnd . $this->ds. $this->ds
        return $this->ds. "DROP TABLE IF EXISTS `" . $table . '`' . $this->sqlEnd . $this->ds. $this->ds . $row [1] . $this->sqlEnd . $this->ds. $this->ds;
    }


    /**
     * 插入数据库备份基础信息
     */
    private function _retrieve() {
        $this->_showMsg[] = '启动数据库备份程序....';
        $this->_showMsg[] = '启动日期为: ' . date ( 'Y' ) . ' 年  ' . date ( 'm' ) . ' 月 ' . date ( 'd' ) . ' 日 ' . date ( 'H:i' ) .' ....';
        $this->_showMsg[] = '-- MySQL版本: ' . @mysql_get_server_info () . @mysql_get_client_info()  .' ....';
        $this->_showMsg[] = '-- PHP 版本: ' . PHP_VERSION .' ....';
        $this->_showMsg[] = '';
    }

}