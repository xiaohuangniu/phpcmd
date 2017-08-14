<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 处理命令行
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.4
 + Initial-Time : 2017-5-2 17:27
 + Last-time    : 2017-5-12 14:05 + 小黄牛
 + Desc         : 用于分发命令行，读取命令行相关配置参数
 +----------------------------------------------------------------------
*/
# 设置中国时区 
date_default_timezone_set('PRC');
# 引入公共函数库
require_once 'function/functions.php';

class Cmd{
    private $command_data; // 命令行参数 数组

    public function  __construct(){
        if (PHP_VERSION < '5.4') { 
            echo json_encode( array('code'=>'03', 'data'=>'命令行工具兼容的PHP版本最低为:5.4.0') );
            exit;
        }
        isset($_SESSION) || session_start();
        $this->command_data = $this->Parameter($_POST['cmd']);
        if ( empty($_SESSION['cmd_user']) && empty($this->command_data[1]) ) {
            echo json_encode( array('code'=>'02', 'data'=>'未登录命令行工具,请先登录' ) );
        }else if (empty($_SESSION['cmd_user']) && $this->command_data[1] != 'login|name') {
            echo json_encode( array('code'=>'02', 'data'=>'未登录命令行工具,请先登录' ) );
        }else{
            if (!empty($_SESSION['cmd_user'])) {
                Admin_Log($_SESSION['cmd_user'], $_POST['cmd']);
            }
            $this->Go();
        }
    }

    /**
     * Title  : 分解命令行参数
     * Author : 小黄牛
     * @param  string : $txt    AJAX提交过来的命令行
     * @return array  : 
    */
    private function Parameter($txt){
        $array = explode(' ', $txt);
        # 数据库的SQL命令行要特殊处理
        if ($array[0] == 'my' && $array[1] == '-x'){
            $A = $array[0];
            $B = $array[1];
            $path = $A.' '.$B.' ';
            $C = str_replace($path , '', $txt);
            $array    = array();
            $array[0] = $A;
            $array[1] = $B;
            $C = str_replace($A.' '.$B , '', ltrim($C, ' '));

            if(!empty($C)){
                $array[2] = $C;
                $data     = explode(' -', $array[2]);
                if(!empty($data[1])){
                    $array[3] = $data[1];
                    $array[2] = htmlspecialchars_decode( str_replace(' -'.$array[3] , '', $array[2]) );
                }
            }
            
            # 先获取到 1 2 的命令行
            # 然后用 1.' '.2.' '; 去向原始的命令行中进行替换操作，获得第3个参数
            # 然后用 1.' '.2; 去向第3个参数过滤，以兼容回滚模式
            # 再用' -'的方式去分割第3个参数，得到的下标1，即为回滚标记  
            # 若有第4个参数，则用' -'.第4个参数的方式，向第3个参数中替换，更新第3个参数
        }
        return $array;
    }
    /**
     * Title  : 根据不同的命令行参数，引入不同的处理类
     * Author : 小黄牛
     * @return 
     */
    private function Go(){
        #echo json_encode(['code'=>00,'data'=>$this->command_data]);return false;
        $class = $this->command_data[0];

        if (!file_exists('core/' . $class .'.php')) {
            $this->Eco('01', "暂无该命令行扩展 - {$class}");
        }

        require_once 'core/' . $class .'.php';
        $obj = new $class($this->command_data);
        $res = $obj -> Go();
        echo json_encode($res);
    }

    /**
     * 处理返回值
     * @param  int          : $status  状态码
     * @param  array|string : $data    返回值
     */
    private function Eco($status, $data){
        $array = array(
            'code' => $status,
            'data' => $data
        );
        echo json_encode($array);
        exit;
    }
}
$str = new Cmd();
