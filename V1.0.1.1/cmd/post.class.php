<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 处理CD命令提交的保存修改
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.4
 + Initial-Time : 2017-8-14 10:04
 + Last-time    : 2017-8-14 10:04 + 小黄牛
 + Desc         : 
 +----------------------------------------------------------------------
*/

class Post{
    private $command_data; // 命令行参数 数组

    public function  __construct(){
        isset($_SESSION) || session_start();
        if (!isset($_SESSION['cmd_user'])) {
			echo json_encode( array('code'=>'02', 'data'=>'未登录命令行工具,请先登录' ) );exit;
		}

        if($_POST['type'] == 'click'){
            $this->click($_POST['url']);
        }else{
            $this->upd($_POST['content']);
        }
    }

    /**
     * 保存提交的文件内容
     * @param string $content 提交的内容 
    */
    private function upd($content){
        $res = file_put_contents($_SESSION['cmd_post_file'], $content);
        if(!$res) {
            echo json_encode( ['code'=>'01', 'data'=>'内容更新失败！'] );exit;
        }
        echo json_encode( ['code'=>'00', 'data'=>'内容更新成功！'] );exit;
    }

    /**
     * 获取文件内容
     * @param  string $url     文件路径
     * @return string $content 提交的内容 
    */
    private function click($url){
        $file = iconv('utf-8//IGNORE','gb2312',$url);
        # 打开文件
        if(!file_exists($file)){
            echo json_encode( ['code' => '01','data' => $url. ' :文件不存在！']); exit;
        }
        if(!is_writable($file)){
            echo json_encode( ['code' => '01','data' => $url . ' :文件没有读写权限！']); exit;
        }
        $_SESSION['cmd_post_file'] = $file;
        $content = file_get_contents($file);
        $suffix  = strtolower(substr(strrchr($file, '.'), 1));

        echo json_encode( ['code' => '05','msg' => $suffix,'data' => $content]); exit;
    }
}
$str = new Post();
