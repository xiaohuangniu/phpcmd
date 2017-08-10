<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP—CMD 命令行工具 - 实体化类
 + Author       : 小黄牛(1731223728@qq.com) -- QQ群：368405253
 + Version      : V1.0.0.1
 + Initial-Time : 2017-5-2 18:43
 + Last-time    : 2017-5-2 18:43 + 小黄牛
 + Desc         : 操作PHP.ini的命令行
 +              : conf add key val
 +              : conf upd key val
 +              : conf del key
 +              : conf sel key
 +              : conf -l
 +----------------------------------------------------------------------
*/

# 引入命令行基类
require_once('CmdInterface.php');

class php implements CmdInterface{
    private $command_data; // 命令行参数 数组

    public function  __construct($txt){
        $this->command_data = $txt;
    }

    public function Go(){
		# 第四个参数，不管是不是中文，先过滤一遍
		if(!empty($this->command_data[3])){
			$this->command_data[3] = iconv('utf-8', 'gbk', $this->command_data[3]);
		}
		# 分支
        switch ($this->command_data[1]){
			case 'add' : 
				$res = $this->Add();
			break;  
			default  :
				$res = [
					'code' => '01',
					'data' => '暂无该操作类型',
				];
		}
		return $res;
    }

    
}

				