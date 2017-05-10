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

class DBimport {
    private $DB;                  // PDO实例
    private $SQL_PATH;            // 数据库备份文件夹
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

    /******************************************************************** 数据库导入 start ****************************************************************************/
    /**
     * 导入备份数据
     * 说明：分卷文件格式20120516211738_all_v1.sql
     * 参数：文件路径(必填)
     *
     * @param string $sqlfile
     */
    function restore($sqlfile) {
        $sqlfile = $this->SQL_PATH . $sqlfile;
        # 检测文件是否存在
        if (! file_exists ( $sqlfile )) {
            $this->_showMsg[] = "sql文件不存在！请检查";
            return false;
        }
        $this->lock();

        # 获取数据库存储位置
        $sqlpath = pathinfo ( $sqlfile );
        $this->sqldir = $sqlpath ['dirname'];
        
        # 检测是否包含分卷，将类似2017_all_v1.sql从_v分开,有则说明有分卷
        $volume = explode ( "_v", $sqlfile );
        $volume_path = $volume [0];
		$this->_showMsg[] = "数据库恢复中,请勿关闭本页面或者进行页面跳转,导致数据库结构损坏或者丢失!";
        
        // 存在分卷，则获取当前是第几分卷，循环执行余下分卷
        $volume_id = explode ( ".sq", $volume [1] );
        // 当前分卷为$volume_id
        $volume_id = intval ( $volume_id [0] );

        while ( $volume_id ) {
            $tmpfile = $volume_path . "_v" . $volume_id . ".sql";
            // 存在其他分卷，继续执行
            if (file_exists ( $tmpfile )) {
                
                // 执行导入方法
                $this->_showMsg[] = "正在导入分卷 $volume_id ：<span style='color:#f00;'>" . $tmpfile . '</span>';
                if ($this->_import ( $tmpfile )) {
                } else {
                    $this_showMsg[] = "导入分卷：<span style='color:#f00;'>" . $tmpfile . '</span>失败！可能是数据库结构已损坏！请尝试从分卷1开始导入';
                    return false;
                }
            } else {
                $this_showMsg[] = "此分卷备份全部导入成功！";
                return false;
            }
            $volume_id ++;
        }

        # 解锁数据库
        $this->unlock();
    }



    /**
     * 将sql导入到数据库（普通导入）
     *
     * @param string $sqlfile
     * @return boolean
    */
    private function _import($sqlfile) {
        # sql文件包含的sql语句数组
        $sqls = array ();
        $f = fopen ( $sqlfile, "rb" );
        # 创建表缓冲变量
        $create_table = '';
        while ( ! feof ( $f ) ) {
            # 读取每一行sql
            $line = fgets ( $f );
            // 这一步为了将创建表合成完整的sql语句
            // 如果结尾没有包含';'(即为一个完整的sql语句，这里是插入语句)，并且不包含'ENGINE='(即创建表的最后一句)
            if (! preg_match ( '/;/', $line ) || preg_match ( '/ENGINE=/', $line )) {
                // 将本次sql语句与创建表sql连接存起来
                $create_table .= $line;
                // 如果包含了创建表的最后一句
                if (preg_match ( '/ENGINE=/', $create_table)) {
                    //执行sql语句创建表
                    $this->_insert_into($create_table);
                    // 清空当前，准备下一个表的创建
                    $create_table = '';
                }
                // 跳过本次
                continue;
            }
            //执行sql语句
            $this->_insert_into($line);
        }
		if($this->_insert_into($line)==false)
		{
			$this->_showMsg[] = "数据库恢复成功";
		}
        fclose ( $f );
        return true;
    }

    # 插入单条sql语句
    private function _insert_into($sql){
        $sql = trim ( $sql );
        $pdo = $this->DB;
        $pdo->query($sql);
    }

    /******************************************************************** 数据库导入 stop ****************************************************************************/

    /**
     * 锁定数据库，以免备份或导入时出错
    */
    private function lock($op = "WRITE") {
        $pdo = $this->DB;
        $res = $pdo->query( "lock tables " . $this->config_data['DB_NAME'] . " " . $op );
        if ($res) {
            return true;
        }
        return false;
    }

    // 解锁
    private function unlock() {
        $pdo = $this->DB;
        $res = $pdo->query( "unlock tables" );
        if ($res) {
            return true;
        }
        return false;
    }

}