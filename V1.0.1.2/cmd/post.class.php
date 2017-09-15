<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 处理CD命令提交的保存修改
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.1.2
 + Initial-Time : 2017-8-14 10:04
 + Last-time    : 2017-9-15 16:09 + 小黄牛
 + Desc         : 修改保存缓存、还原备份、删除备份功能
 +----------------------------------------------------------------------
*/

class Post{
    private $command_data; // 命令行参数 数组
    private $user_cache;   // 管理员GIT缓存目录

    public function  __construct(){
        isset($_SESSION) || session_start();
        if (!isset($_SESSION['cmd_user'])) {
			echo json_encode( array('code'=>'02', 'data'=>'未登录命令行工具,请先登录' ) );exit;
		}
        $this->user_cache = 'config/user_cache/' . $_SESSION['cmd_user'] . '/';

        if(!is_dir($this->user_cache)){
            mkdir($this->user_cache,0777,true); 
        }

        if($_POST['type'] == 'click'){
            $this->click($_POST['url']);
        }else if($_POST['type'] == 'getCache'){
            if($_POST['mode'] == 1) {
                $this->getCache($_POST['file'], $_POST['url']); // 还原备份
            }else{
                $this->delCache($_POST['file']); // 删除备份
            }
        }else{
            $this->upd($_POST['content']);
        }
    }
    
    /**
     * 还原备份文件
     * @param string $file 备份文件地址
     * @param string $url  原文件地址
     */
    private function getCache($file, $url){
        $url = iconv('utf-8//IGNORE','gb2312', $url);
        # 文件检测
        if(!file_exists($file)){  echo json_encode( ['code' => '01','data' => '备份文件不存在！'] ); exit; }
        if(!file_exists($url)){  echo json_encode( ['code' => '01','data' => '目标文件不存在！'] ); exit; }
        # 权限检测
        if(!is_writable($file)){ echo json_encode( ['code' => '01','data' => '备份文件没有读写权限！'] );exit; }
        if(!is_writable($url)){ echo json_encode( ['code' => '01','data' => '目标文件没有读写权限！'] );exit; }
        $file_content = file_get_contents($file); // 获取缓存内容
        file_put_contents($url, $file_content); // 将缓存更新为当前版本
        echo json_encode( ['code' => '00','data' => '备份还原成功'] );exit;
    }

    /**
     * 删除备份文件
     * @param string $file 备份文件地址
     */
    private function delCache($file){
        # 文件检测
        if(!file_exists($file)){  echo json_encode( ['code' => '01','data' => '备份文件不存在！'] ); exit; }
        if(unlink($file)){
            echo json_encode( ['code' => '03','data' => '备份文件删除成功'] );exit;
        }
        echo json_encode( ['code' => '01','data' => '备份文件删除失败'] );exit;
    }


    /**
     * 保存提交的文件内容
     * @param string $content 提交的内容 
    */
    private function upd($content){

        /******************************* 缓存生成相关操作 ********************************/
        $md5 = $this->user_cache . md5($_SESSION['cmd_post_file']) . '/';
        if (!is_dir($md5)) {
            mkdir($md5,0777,true); 
        }
        $array = scandir($md5);
        $file = $md5 . date('YmdHis',time()) . '.cmdcache';

        # 写入缓存
        $content = file_get_contents($_SESSION['cmd_post_file']);
        file_put_contents($file, $content);

        /******************************* 缓存合并相关操作 ********************************/
        
       
        /******************************* 合并后保存相关操作 ******************************/
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
